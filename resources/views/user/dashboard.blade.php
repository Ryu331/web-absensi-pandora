@extends('layouts.app')
@section('title', 'Dashboard Saya')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Dashboard Saya</h2>
    <p class="text-gray-500">Selamat datang, {{ Auth::user()->name }}</p>
</div>

{{-- STATUS BANNER --}}
<div class="bg-gradient-to-r from-blue-400 to-blue-800 rounded-xl shadow-lg p-6 mb-8 text-white">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm opacity-80 uppercase tracking-wide">Status Absensi</p>
            <p class="text-3xl font-bold mt-1">{{ $currentStatus }}</p>
<p class="text-sm opacity-75 mt-1">{{ now(config('app.timezone'))->isoFormat('dddd, D MMMM Y') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            @if(isset($todayAbsen))
            <div class="bg-white/15 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-white/80">Jam Masuk</p>
                <p class="text-lg font-semibold mt-1">{{ $todayAbsen->jam }}</p>
            </div>
            <div class="bg-white/15 rounded-xl p-4">
                <p class="text-xs uppercase tracking-wide text-white/80">Jam Pulang</p>
                <p class="text-lg font-semibold mt-1">{{ $todayAbsen->jam_keluar ?? '-' }}</p>
            </div>
            @endif
        </div>
    </div>

    @if($dashboardAction)
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:gap-3">
        <a href="{{ route('user.absens.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/25 transition">
            {{ $dashboardAction === 'keluar' ? 'Ajukan Pulang Cepat' : 'Ajukan Absen Masuk' }}
        </a>
        <p class="text-sm text-white/80">{{ $dashboardAction === 'keluar' ? 'Anda bisa mengajukan permohonan pulang cepat saat sudah bekerja.' : 'Silakan ajukan absen masuk jika belum memulai hari kerja.' }}</p>
    </div>
    @endif
</div>

{{-- STATS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Absen Bulan Ini</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['absen_bulan_ini'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Dikonfirmasi</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['absen_dikonfirmasi'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Pending</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending_requests'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
        <p class="text-xs text-gray-500 uppercase">Request Diterima</p>
        <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['dikonfirmasi_requests'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- RIWAYAT ABSENSI --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Riwayat Absensi Saya</h3>
                <a href="{{ route('user.absens.index') }}" class="text-blue-600 hover:underline text-sm">Lihat Semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Tanggal</th>
                            <th class="px-4 py-2 text-left">Jam Masuk</th>
                            <th class="px-4 py-2 text-left">Jam Pulang</th>
                            <th class="px-4 py-2 text-left">Lokasi</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatAbsen as $absen)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3">{{ $absen->tanggal->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $absen->jam }}</td>
                            <td class="px-4 py-3">{{ $absen->jam_keluar ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 max-w-32 truncate">{{ $absen->lokasi ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @php
                                $color = match($absen->status) {
                                    'dikonfirmasi'       => 'bg-green-100 text-green-700',
                                    'pending'            => 'bg-yellow-100 text-yellow-700',
                                    'tidak_dikonfirmasi' => 'bg-red-100 text-red-700',
                                    default              => 'bg-gray-100 text-gray-600',
                                };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $color }}">
                                    {{ ucfirst(str_replace('_',' ',$absen->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada riwayat absensi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="space-y-4">
        {{-- MENU CEPAT --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Menu Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('user.absens.create') }}"
                   class="flex items-center gap-2 w-full p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition font-medium text-sm">
                    ✅ Input Absensi
                </a>
                <a href="{{ route('user.absens.index') }}"
                   class="flex items-center gap-2 w-full p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition font-medium text-sm">
                    📋 Riwayat Absen
                </a>
                <a href="{{ route('user.laporans.create') }}"
                   class="flex items-center gap-2 w-full p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition font-medium text-sm">
                    📝 Kirim Laporan
                </a>
                <a href="{{ route('user.laporans.index') }}"
                   class="flex items-center gap-2 w-full p-3 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition font-medium text-sm">
                    📄 Laporan Saya
                </a>
                <a href="{{ route('cuti.index') }}"
                   class="flex items-center gap-2 w-full p-3 bg-teal-50 text-teal-700 rounded-lg hover:bg-teal-100 transition font-medium text-sm">
                    🏖️ Ajukan Cuti
                </a>
            </div>
        </div>

        {{-- STATUS REQUEST TERBARU --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4">Request Terbaru</h3>
            <div class="space-y-3">
                @forelse($riwayatRequests as $req)
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-medium">{{ $req->absen?->tanggal?->format('d/m/Y') ?? '-' }}</p>
                        @php
                        $color = match($req->status) {
                            'pending'            => 'bg-yellow-100 text-yellow-700',
                            'dikonfirmasi'       => 'bg-green-100 text-green-700',
                            'tidak_dikonfirmasi' => 'bg-red-100 text-red-700',
                            default              => 'bg-gray-100 text-gray-600',
                        };
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                            {{ ucfirst(str_replace('_',' ',$req->status)) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ Str::limit($req->catatan ?? '-', 40) }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-2">Tidak ada request</p>
                @endforelse
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 rounded-lg shadow p-5 border border-blue-200">
            <h4 class="font-bold text-blue-800 mb-1">💡 Tips</h4>
            <p class="text-sm text-blue-700">Jangan lupa absen setiap hari kerja sebelum pukul 09.00!</p>
        </div>
    </div>

</div>
@endsection
