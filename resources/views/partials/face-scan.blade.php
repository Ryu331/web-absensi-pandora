{{-- Verifikasi wajah via kamera (wajib untuk absen masuk) --}}
<div id="faceScanSection" class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-4 space-y-3">
    <div>
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">📷 Verifikasi Wajah</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Wajib scan wajah terlebih dahulu. Posisikan wajah di dalam bingkai hingga terdeteksi, lalu ambil foto.
        </p>
    </div>

    <div class="relative mx-auto max-w-xs aspect-[3/4] rounded-xl overflow-hidden bg-black">
        <video id="faceVideo" class="w-full h-full object-cover scale-x-[-1]" playsinline muted autoplay></video>
        <canvas id="faceCanvas" class="hidden"></canvas>
        <div id="faceOverlay"
             class="absolute inset-0 pointer-events-none border-2 border-dashed border-white/50 rounded-xl transition-colors duration-300"></div>
        <img id="facePreview" src="" alt="Preview foto wajah"
             class="hidden absolute inset-0 w-full h-full object-cover scale-x-[-1]">
    </div>

    <p id="faceStatus" class="text-xs text-center text-gray-400">Memuat deteksi wajah...</p>
    @error('foto_wajah')
        <p class="text-xs text-center text-red-500">{{ $message }}</p>
    @enderror

    <input type="file" name="foto_wajah" id="foto_wajah" accept="image/jpeg,image/png" class="hidden" required>

    <div class="flex flex-wrap gap-2 justify-center">
        <button type="button" id="btnMulaiKamera"
                class="px-3 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm">
            Mulai Kamera
        </button>
        <button type="button" id="btnAmbilFoto" disabled
                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
            Ambil Foto Wajah
        </button>
        <button type="button" id="btnUlangiFoto" disabled
                class="hidden px-3 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">
            Ulangi Scan
        </button>
    </div>
</div>
