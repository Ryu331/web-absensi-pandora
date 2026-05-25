@extends('layouts.app')
@section('title', 'Input Absensi')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('user.dashboard') }}" class="text-blue-600 hover:underline text-sm">← Kembali ke Dashboard</a>
        <h2 class="text-2xl font-bold mt-2">Input Absensi Harian</h2>
        <p class="text-gray-500 text-sm">{{ now(config('app.timezone'))->isoFormat('dddd, D MMMM Y') }}</p>
    </div>

    @if($action === 'completed')
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 text-center">
        <div class="text-4xl mb-3">🎉</div>
        <h3 class="text-lg font-bold text-emerald-800">Absensi hari ini selesai</h3>
        <p class="text-emerald-700 mt-2 text-sm">Jam masuk: {{ $todayAbsen->jam }} · Jam pulang: {{ $todayAbsen->jam_keluar }}</p>
        <a href="{{ route('user.absens.index') }}"
           class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
            Lihat Riwayat Absen
        </a>
    </div>
    @elseif($action === 'pending_masuk')
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
        <div class="text-4xl mb-3">⏳</div>
        <h3 class="text-lg font-bold text-yellow-800">Menunggu konfirmasi masuk</h3>
        <p class="text-yellow-700 mt-2 text-sm">Permintaan absen masuk Anda sedang diproses oleh admin.</p>
        <a href="{{ route('user.absens.index') }}"
           class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
            Lihat Riwayat Absen
        </a>
    </div>
    @elseif($action === 'pending_keluar')
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
        <div class="text-4xl mb-3">⏳</div>
        <h3 class="text-lg font-bold text-yellow-800">Menunggu konfirmasi pulang cepat</h3>
        <p class="text-yellow-700 mt-2 text-sm">Permohonan pulang cepat Anda sedang diproses oleh admin.</p>
        <a href="{{ route('user.absens.index') }}"
           class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
            Lihat Riwayat Absen
        </a>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6 text-center">
            <p class="text-sm text-blue-600 font-medium">Waktu Absen</p>
            <p class="text-3xl font-bold text-blue-800 dark:text-blue-300" id="jamSekarang">--:--:--</p>
        </div>

        <form method="POST" action="{{ route('user.absens.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="request_type" value="{{ $action === 'keluar' ? 'keluar' : 'masuk' }}">

            <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-700">
                <p class="text-sm text-slate-500 dark:text-slate-300">Tipe absensi</p>
                <p class="text-lg font-semibold mt-1">{{ $action === 'keluar' ? 'Pulang Cepat' : 'Masuk' }}</p>
                @if($action === 'keluar')
                <p class="text-xs text-slate-400 mt-1">Ajukan permohonan pulang cepat. Admin akan mengkonfirmasi sebelum jam pulang Anda tercatat.</p>
                @else
                <p class="text-xs text-slate-400 mt-1">Ajukan absensi masuk. Scan wajah terlebih dahulu, lalu lengkapi lokasi. Admin akan memverifikasi.</p>
                @endif
            </div>

            @if($action !== 'keluar')
                @include('partials.face-scan')
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    📍 Lokasi
                </label>
                <div class="flex gap-2">
                    <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                           placeholder="Nama lokasi / alamat" readonly>
                    <button type="button" onclick="ambilLokasi()"
                            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm whitespace-nowrap">
                        Ambil GPS
                    </button>
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <p id="lokasiStatus" class="mt-1 text-xs text-gray-400"></p>
                <a id="lokasiMapsLink" href="#" target="_blank" rel="noopener noreferrer"
                   class="mt-1 hidden text-xs text-blue-600 hover:underline dark:text-blue-400">
                    Buka di Google Maps →
                </a>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    📝 Catatan (opsional)
                </label>
                <textarea name="catatan" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm resize-none bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                          placeholder="Tuliskan alasan atau keterangan...">{{ old('catatan') }}</textarea>
            </div>

            <button type="submit" id="btnSubmitAbsen"
                    @if($action !== 'keluar') disabled @endif
                    class="w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                {{ $action === 'keluar' ? 'Ajukan Pulang Cepat' : 'Ajukan Absen Masuk' }}
            </button>
            @if($action !== 'keluar')
            <p id="submitHint" class="text-xs text-center text-amber-600 dark:text-amber-400">
                Scan dan ambil foto wajah terlebih dahulu untuk mengaktifkan tombol absen.
            </p>
            @endif
        </form>
    </div>
    @endif
</div>

@if($action !== 'keluar')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endif

<script>
    function updateJam() {
        const el = document.getElementById('jamSekarang');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }
    }
    setInterval(updateJam, 1000);
    updateJam();

    function setLokasiStatus(message, type) {
        const statusEl = document.getElementById('lokasiStatus');
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.className = 'mt-1 text-xs ' + (
            type === 'ok' ? 'text-green-600' :
            type === 'err' ? 'text-red-500' : 'text-gray-400'
        );
    }

    function tampilkanLinkMaps(url) {
        const link = document.getElementById('lokasiMapsLink');
        if (!link || !url) return;
        link.href = url;
        link.classList.remove('hidden');
    }

    function formatAlamatDariApi(data) {
        const admin = (data.localityInfo?.administrative || [])
            .filter(function(a) { return a.order >= 7; })
            .sort(function(a, b) { return b.order - a.order; })
            .map(function(a) { return a.name; });

        const unik = [];
        admin.forEach(function(nama) {
            if (nama && !unik.includes(nama)) unik.push(nama);
        });

        if (unik.length) {
            return unik.join(', ');
        }

        return [data.locality, data.city, data.principalSubdivision, data.countryName]
            .filter(Boolean)
            .filter(function(v, i, arr) { return arr.indexOf(v) === i; })
            .join(', ');
    }

    async function ambilAlamatBigDataCloud(lat, lng) {
        const url = 'https://api.bigdatacloud.net/data/reverse-geocode-client'
            + '?latitude=' + encodeURIComponent(lat)
            + '&longitude=' + encodeURIComponent(lng)
            + '&localityLanguage=id';

        const res = await fetch(url);
        if (!res.ok) throw new Error('API alamat gagal');
        const data = await res.json();
        const address = formatAlamatDariApi(data);
        if (!address) throw new Error('Alamat kosong');
        return address;
    }

    async function ambilAlamatServer(lat, lng) {
        const url = @json(route('user.absens.reverse-geocode'))
            + '?latitude=' + encodeURIComponent(lat)
            + '&longitude=' + encodeURIComponent(lng);

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error('Server geocode gagal');
        const data = await res.json();
        return data.address;
    }

    async function isiAlamatDariKoordinat(lat, lng) {
        setLokasiStatus('Mencari nama lokasi / alamat...', 'info');
        const mapsUrl = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        let address = null;

        try {
            address = await ambilAlamatBigDataCloud(lat, lng);
        } catch (e) {
            try {
                address = await ambilAlamatServer(lat, lng);
            } catch (e2) {
                address = null;
            }
        }

        if (address && !/^-?\d+\.\d+,\s*-?\d+\.\d+$/.test(address) && !address.startsWith('Lat:')) {
            document.getElementById('lokasi').value = address.substring(0, 255);
            tampilkanLinkMaps(mapsUrl);
            setLokasiStatus('✅ Alamat lokasi berhasil (mirip share lokasi). Bisa diedit jika perlu.', 'ok');
        } else {
            document.getElementById('lokasi').value = address || ('Lat: ' + lat + ', Lng: ' + lng);
            tampilkanLinkMaps(mapsUrl);
            setLokasiStatus('GPS OK. Alamat otomatis terbatas — edit manual atau buka Google Maps.', 'err');
        }

        document.getElementById('lokasi').removeAttribute('readonly');
    }

    function ambilLokasi() {
        setLokasiStatus('Mengambil lokasi GPS...', 'info');
        document.getElementById('lokasiMapsLink')?.classList.add('hidden');

        if (!navigator.geolocation) {
            setLokasiStatus('Browser tidak mendukung geolokasi.', 'err');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude.toFixed(8);
                const lng = pos.coords.longitude.toFixed(8);

                document.getElementById('latitude').value  = lat;
                document.getElementById('longitude').value = lng;

                isiAlamatDariKoordinat(lat, lng);
            },
            function(err) {
                setLokasiStatus('Gagal ambil GPS: ' + err.message, 'err');
                document.getElementById('lokasi').removeAttribute('readonly');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    @if($action !== 'keluar')
    (function initFaceScanAbsen() {
        const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
        const video = document.getElementById('faceVideo');
        const canvas = document.getElementById('faceCanvas');
        const preview = document.getElementById('facePreview');
        const overlay = document.getElementById('faceOverlay');
        const statusEl = document.getElementById('faceStatus');
        const fileInput = document.getElementById('foto_wajah');
        const btnMulai = document.getElementById('btnMulaiKamera');
        const btnAmbil = document.getElementById('btnAmbilFoto');
        const btnUlangi = document.getElementById('btnUlangiFoto');
        const btnSubmit = document.getElementById('btnSubmitAbsen');
        const submitHint = document.getElementById('submitHint');
        const form = btnSubmit?.closest('form');

        if (!video || !fileInput) return;

        let stream = null;
        let modelsReady = false;
        let faceDetected = false;
        let fotoSudahDiambil = false;
        let detectTimer = null;

        function setFaceStatus(msg, type) {
            statusEl.textContent = msg;
            statusEl.className = 'text-xs text-center ' + (
                type === 'ok' ? 'text-green-600' :
                type === 'err' ? 'text-red-500' : 'text-gray-400'
            );
        }

        function updateOverlay() {
            if (fotoSudahDiambil) return;
            overlay.classList.toggle('border-green-400', faceDetected);
            overlay.classList.toggle('border-white/50', !faceDetected);
            btnAmbil.disabled = !faceDetected || !stream;
        }

        function updateSubmitState() {
            const ok = fotoSudahDiambil && fileInput.files.length > 0;
            btnSubmit.disabled = !ok;
            if (submitHint) {
                submitHint.classList.toggle('hidden', ok);
            }
        }

        function stopCamera() {
            if (detectTimer) {
                clearInterval(detectTimer);
                detectTimer = null;
            }
            if (stream) {
                stream.getTracks().forEach(function(t) { t.stop(); });
                stream = null;
            }
            video.srcObject = null;
        }

        async function loadModels() {
            setFaceStatus('Memuat model deteksi wajah...', 'info');
            try {
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                modelsReady = true;
                setFaceStatus('Model siap. Klik "Mulai Kamera" untuk scan wajah.', 'info');
            } catch (e) {
                setFaceStatus('Gagal memuat deteksi wajah. Periksa koneksi internet lalu refresh halaman.', 'err');
            }
        }

        async function deteksiWajah() {
            if (!modelsReady || !stream || fotoSudahDiambil) return;
            try {
                const deteksi = await faceapi.detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 })
                );
                faceDetected = !!deteksi;
                setFaceStatus(
                    faceDetected
                        ? 'Wajah terdeteksi. Klik "Ambil Foto Wajah".'
                        : 'Posisikan wajah di tengah bingkai...',
                    faceDetected ? 'ok' : 'info'
                );
                updateOverlay();
            } catch (e) {
                faceDetected = false;
                updateOverlay();
            }
        }

        async function mulaiKamera() {
            stopCamera();
            fotoSudahDiambil = false;
            preview.classList.add('hidden');
            video.classList.remove('hidden');
            fileInput.value = '';
            btnUlangi.classList.add('hidden');
            btnAmbil.classList.remove('hidden');
            updateSubmitState();

            if (!modelsReady) {
                setFaceStatus('Model deteksi belum siap.', 'err');
                return;
            }

            setFaceStatus('Meminta akses kamera...', 'info');

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false,
                });
                video.srcObject = stream;
                await video.play();
                setFaceStatus('Mencari wajah di kamera...', 'info');
                detectTimer = setInterval(deteksiWajah, 400);
            } catch (e) {
                setFaceStatus('Tidak bisa akses kamera: izinkan kamera di browser.', 'err');
            }
        }

        function ambilFoto() {
            if (!faceDetected || !stream) return;

            const w = video.videoWidth;
            const h = video.videoHeight;
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.translate(w, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, w, h);

            canvas.toBlob(function(blob) {
                if (!blob) {
                    setFaceStatus('Gagal mengambil foto. Coba lagi.', 'err');
                    return;
                }

                const file = new File([blob], 'foto_wajah_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;

                preview.src = canvas.toDataURL('image/jpeg', 0.9);
                preview.classList.remove('hidden');
                video.classList.add('hidden');
                fotoSudahDiambil = true;
                stopCamera();

                btnAmbil.classList.add('hidden');
                btnUlangi.classList.remove('hidden');
                setFaceStatus('Foto wajah berhasil. Anda bisa melanjutkan absen.', 'ok');
                updateSubmitState();
            }, 'image/jpeg', 0.9);
        }

        function ulangiScan() {
            fotoSudahDiambil = false;
            faceDetected = false;
            fileInput.value = '';
            preview.classList.add('hidden');
            video.classList.remove('hidden');
            btnUlangi.classList.add('hidden');
            btnAmbil.classList.remove('hidden');
            updateSubmitState();
            mulaiKamera();
        }

        btnMulai?.addEventListener('click', mulaiKamera);
        btnAmbil?.addEventListener('click', ambilFoto);
        btnUlangi?.addEventListener('click', ulangiScan);

        form?.addEventListener('submit', function(e) {
            if (!fotoSudahDiambil || !fileInput.files.length) {
                e.preventDefault();
                setFaceStatus('Scan wajah wajib sebelum absen masuk.', 'err');
            }
        });

        loadModels();
        updateSubmitState();
    })();
    @endif
</script>
@endsection
