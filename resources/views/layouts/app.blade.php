<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Web Absensi Pandora'))</title>
    <link rel="icon" type="image/jpg" href="{{ asset('Logo.jpg') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    {{-- NAVBAR --}}
    <nav class="bg-slate-900 border-b border-slate-800 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-4">
                    <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}"
                       class="text-2xl font-bold text-sky-500">Pandora</a>
                    <span class="text-sm text-slate-400 hidden sm:inline">Sistem Absensi</span>
                </div>

                {{-- Nav Links --}}
                <div class="hidden md:flex items-center gap-2">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Kelola User
                        </a>
                        <a href="{{ route('admin.absens.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.absens.*') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Absensi
                        </a>
                        <a href="{{ route('admin.absen-requests.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium relative {{ request()->routeIs('admin.absen-requests.*') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Permintaan
                            @php $pendingCount = \App\Models\AbsenRequest::where('status','pending')->count(); @endphp
                            @if($pendingCount > 0)
                                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.laporans.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.laporans.*') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Laporan
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('user.dashboard') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('user.absens.create') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('user.absens.create') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Input Absen
                        </a>
                        <a href="{{ route('user.absens.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('user.absens.index') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Riwayat
                        </a>
                        <a href="{{ route('user.laporans.index') }}"
                           class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('user.laporans.*') ? 'bg-slate-800 text-sky-200' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                            Laporan
                        </a>
                    @endif
                </div>

                {{-- User Info & Logout --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-300 hidden sm:inline">{{ Auth::user()->name }}</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-100">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-500 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                ❌ {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- PAGE CONTENT --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

</body>
</html>
