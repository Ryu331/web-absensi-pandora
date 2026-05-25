<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absen;
use App\Models\AbsenRequest;
use App\Services\ReverseGeocodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AbsenController extends Controller
{
    /**
     * Tampilkan riwayat absensi user yang sedang login.
     */
    public function index()
    {
        $absens = Absen::where('user_id', Auth::id())
            ->orderByDesc('tanggal')
            ->paginate(15);

        return view('user.absens.index', compact('absens'));
    }

    /**
     * Tampilkan form input absensi baru.
     */
    public function create()
    {
        $todayAbsen = Absen::where('user_id', Auth::id())
            ->whereDate('tanggal', Carbon::today(config('app.timezone')))
            ->with('absenRequests')
            ->first();

        $pendingMasuk = $todayAbsen?->absenRequests
            ->where('status', 'pending')
            ->where('request_type', 'masuk')
            ->first();

        $pendingKeluar = $todayAbsen?->absenRequests
            ->where('status', 'pending')
            ->where('request_type', 'keluar')
            ->first();

        $action = 'masuk';

        if ($todayAbsen && $todayAbsen->jam_keluar) {
            $action = 'completed';
        } elseif ($pendingMasuk) {
            $action = 'pending_masuk';
        } elseif ($pendingKeluar) {
            $action = 'pending_keluar';
        } elseif ($todayAbsen && $todayAbsen->status === 'dikonfirmasi' && Auth::user()->status === 'bekerja') {
            $action = 'keluar';
        }

        return view('user.absens.create', compact('todayAbsen', 'action', 'pendingMasuk', 'pendingKeluar'));
    }

    /**
     * Reverse geocode: koordinat → alamat teks + link Google Maps.
     */
    public function reverseGeocode(Request $request, ReverseGeocodeService $geocoder)
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json(
            $geocoder->resolve(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            )
        );
    }

    /**
     * Simpan data absensi baru atau permohonan pulang cepat.
     */
    public function store(Request $request)
    {
        $requestType = $request->input('request_type');

        $validated = $request->validate([
            'request_type' => ['required', 'in:masuk,keluar'],
            'lokasi'       => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'foto_wajah'   => [
                $requestType === 'masuk' ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
            'catatan'      => ['nullable', 'string', 'max:1000'],
        ], [
            'foto_wajah.required' => 'Foto wajah wajib. Scan wajah terlebih dahulu sebelum absen masuk.',
        ]);

        if ($requestType === 'masuk') {
            $todayAbsen = Absen::where('user_id', Auth::id())
                ->whereDate('tanggal', Carbon::today(config('app.timezone')))
                ->with('absenRequests')
                ->first();

            if ($todayAbsen && $todayAbsen->jam_keluar) {
                return back()->with('error', 'Absensi hari ini sudah selesai.');
            }

            $pendingMasuk = $todayAbsen?->absenRequests
                ->where('status', 'pending')
                ->where('request_type', 'masuk')
                ->first();

            if ($pendingMasuk) {
                return back()->with('error', 'Permintaan absen masuk Anda sudah menunggu konfirmasi admin.');
            }

            $fotoPath = $request->file('foto_wajah')->store('foto_absen', 'public');

            if ($todayAbsen && $todayAbsen->status === 'tidak_dikonfirmasi') {
                $todayAbsen->update([
                    'jam'        => Carbon::now(config('app.timezone'))->format('H:i:s'),
                    'lokasi'     => $validated['lokasi'] ?? null,
                    'latitude'   => $validated['latitude'] ?? null,
                    'longitude'  => $validated['longitude'] ?? null,
                    'foto_wajah' => $fotoPath,
                    'status'     => 'pending',
                ]);

                $absen = $todayAbsen;
            } else {
                $absen = Absen::create([
                    'user_id'    => Auth::id(),
                    'tanggal'    => Carbon::today(config('app.timezone')),
                    'jam'        => Carbon::now(config('app.timezone'))->format('H:i:s'),
                    'lokasi'     => $validated['lokasi'] ?? null,
                    'latitude'   => $validated['latitude'] ?? null,
                    'longitude'  => $validated['longitude'] ?? null,
                    'foto_wajah' => $fotoPath,
                    'status'     => 'pending',
                ]);
            }

            AbsenRequest::create([
                'absen_id'     => $absen->id,
                'user_id'      => Auth::id(),
                'catatan'      => $validated['catatan'] ?? 'Permohonan absen masuk',
                'status'       => 'pending',
                'request_type' => 'masuk',
            ]);

            return redirect()->route('user.absens.index')
                ->with('success', 'Permintaan absen masuk terkirim. Menunggu konfirmasi admin.');
        }

        if ($requestType === 'keluar') {
            $todayAbsen = Absen::where('user_id', Auth::id())
                ->whereDate('tanggal', Carbon::today(config('app.timezone')))
                ->where('status', 'dikonfirmasi')
                ->first();

            if (!$todayAbsen || $todayAbsen->jam_keluar) {
                return back()->with('error', 'Tidak ada absen masuk yang aktif untuk pulang cepat.');
            }

            $pendingKeluar = AbsenRequest::where('absen_id', $todayAbsen->id)
                ->where('status', 'pending')
                ->where('request_type', 'keluar')
                ->exists();

            if ($pendingKeluar) {
                return back()->with('error', 'Permohonan pulang cepat Anda sudah menunggu konfirmasi admin.');
            }

            AbsenRequest::create([
                'absen_id'     => $todayAbsen->id,
                'user_id'      => Auth::id(),
                'catatan'      => $validated['catatan'] ?? 'Permohonan pulang cepat',
                'status'       => 'pending',
                'request_type' => 'keluar',
            ]);

            return redirect()->route('user.absens.index')
                ->with('success', 'Permohonan pulang cepat berhasil dikirim. Menunggu konfirmasi admin.');
        }

        return back()->with('error', 'Tipe permintaan absensi tidak dikenal.');
    }

    /**
     * Detail satu record absensi.
     */
    public function show(Absen $absen)
    {
        // Pastikan absen milik user yang sedang login
        if ($absen->user_id !== Auth::id()) {
            abort(403);
        }

        $absen->load('absenRequests.validator');
        return view('user.absens.show', compact('absen'));
    }
}
