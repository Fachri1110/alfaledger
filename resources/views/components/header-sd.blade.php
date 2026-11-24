@php
    $user = auth()->user();
    $isSuperAdmin = $user?->hasRole(['Ketua Yayasan', 'Staf Yayasan']);

    if ($user?->hasRole('Ketua Yayasan')) {
        $rolePrefix = 'ketuayayasan.sd.';
        $mainDashboardRoute = route('ketuayayasan.dashboard');
    } elseif ($user?->hasRole('Staf Yayasan')) {
        $rolePrefix = 'stafyayasan.sd.';
        $mainDashboardRoute = route('stafyayasan.dashboard');
    } else {
        $rolePrefix = 'admin.sd.';
        $mainDashboardRoute = route('admin.dashboard');
    }

    // Tentukan item aktif berdasarkan query parameter saat ini
    $currentKelas = (string) request()->get('kelas', ''); // '1', '2', ... '6' atau ''
    $isBeranda    = $currentKelas === '';

    $kelasNav = [
        '1' => 'Kelas 1',
        '2' => 'Kelas 2',
        '3' => 'Kelas 3',
        '4' => 'Kelas 4',
        '5' => 'Kelas 5',
        '6' => 'Kelas 6',
    ];

    $dashboardRoute = route($rolePrefix . 'dashboard');

    // Cek route saat ini untuk mempertahankan tab saat berganti kelas
    $currentRouteName = request()->route()?->getName() ?? '';
    $kelasRoutes = ['admin.sd.kelas.uap', 'admin.sd.kelas.spp', 'admin.sd.kelas.siswa'];
    $targetRoute = $dashboardRoute;
    if (in_array($currentRouteName, $kelasRoutes)) {
        $targetRoute = route($currentRouteName);
    }
@endphp

<div class="w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Logo Kiri --}}
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ $dashboardRoute }}" class="shrink-0">
                <img src="{{ asset('images/Logo.png') }}" alt="ALFA LEDGER" class="h-9 w-auto">
            </a>

            {{-- Konteks Kelas: muncul saat berada di halaman kelas tertentu --}}
            @unless($isBeranda)
                <span class="text-black/20 font-light text-lg select-none">/</span>
                <span class="flex items-center gap-1.5 bg-signature/10 border border-signature/20 text-signature text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                    {{ $kelasNav[$currentKelas] ?? 'Kelas' }}
                </span>
            @endunless
        </div>

        {{-- Navigasi Tengah: Beranda · Kelas 1-6 --}}
        @unless (auth()->user()?->hasRole(['Ketua Yayasan', 'Staf Yayasan']))
            <nav class="flex items-center gap-1 overflow-x-auto">
                {{-- Beranda --}}
                <a href="{{ $dashboardRoute }}"
                   class="px-3.5 py-1.5 text-sm font-inter transition-all rounded-lg whitespace-nowrap
                          {{ $isBeranda
                             ? 'text-signature bg-signature/10 border border-signature/30 font-semibold'
                             : 'text-black/60 hover:text-signature hover:bg-signature/10 font-normal' }}">
                    Beranda
                </a>

                {{-- Kelas 1-6 --}}
                @foreach ($kelasNav as $key => $label)
                    <a href="{{ $targetRoute }}?kelas={{ $key }}"
                       class="px-3.5 py-1.5 text-sm font-inter transition-all rounded-lg whitespace-nowrap
                              {{ $currentKelas === $key
                                 ? 'text-signature bg-signature/10 border border-signature/30 font-semibold'
                                 : 'text-black/60 hover:text-signature hover:bg-signature/10 font-normal' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        @endunless

        {{-- User Info Kanan --}}
        <div class="flex items-center gap-3 shrink-0">
            @if (auth()->user()?->hasRole(['Ketua Yayasan', 'Staf Yayasan']))
                <a href="{{ $mainDashboardRoute }}"
                   class="bg-signature text-white hover:opacity-90 px-4 py-2 rounded-full flex items-center gap-1.5 text-sm font-semibold transition-all shadow-sm">
                    <span>Kembali</span>
                </a>
            @endif

            <span class="text-sm font-normal text-black">
                {{ auth()->user()->name ?? 'Olivia' }}
            </span>

            <div class="w-9 h-9 rounded-full bg-signature flex items-center justify-center shrink-0">
                <span class="text-sm font-semibold text-white uppercase">
                    {{ mb_substr(auth()->user()->name ?? 'O', 0, 1) }}
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 text-sm font-normal text-black hover:text-signature transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </div>
</div>
