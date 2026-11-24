<div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">

    {{-- ===== KIRI: Logo ===== --}}
    <div class="flex items-center">
        <a href="{{ route('superadmin.dashboard') }}">
            <img
                src="{{ asset('images/Logo.png') }}"
                alt="ALFA LEDGER"
                class="h-10 w-auto object-contain"
            >
        </a>
    </div>

    {{-- ===== KANAN: Nama · Avatar · Tombol Keluar ===== --}}
    <div class="flex items-center gap-4">

        {{-- Nama Super Admin --}}
        <span class="font-inter text-base-spp font-normal text-black hidden sm:block">
            {{ auth()->user()->name ?? 'Super Admin' }}
        </span>

        {{-- Avatar — lingkaran dengan inisial huruf pertama nama --}}
        <div class="w-9 h-9 rounded-full bg-signature flex items-center justify-center shrink-0">
            <span class="font-inter text-sm font-semibold text-white uppercase">
                {{ mb_substr(auth()->user()->name ?? 'S', 0, 1) }}
            </span>
        </div>

        {{-- Divider --}}
        <div class="w-px h-5 bg-shade-white-ii hidden sm:block"></div>

        {{-- Tombol Keluar --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                id="btn-logout"
                class="flex items-center gap-2 font-inter text-base-spp font-normal text-black
                       hover:text-signature transition-colors cursor-pointer"
            >
                {{-- Ikon logout (arrow right from box) --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.8" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                </svg>
                <span class="hidden sm:inline">Log Out</span>
            </button>
        </form>

    </div>

</div>