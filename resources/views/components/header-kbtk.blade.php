<div class="w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

        {{-- Logo Kiri --}}
        <a href="#" @click.prevent="$store.kbtk.setView('beranda'); $store.kbtk.activeTab = 'dashboard'" class="shrink-0">
            <img src="{{ asset('images/Logo.png') }}" alt="ALFA LEDGER" class="h-9 w-auto">
        </a>

        {{-- Navigasi Tengah: Beranda · KB · TK A · TK B --}}
        <div class="flex items-center gap-1.5 overflow-x-auto py-1">
            <button
                @click="$store.kbtk.setView('beranda')"
                :class="$store.kbtk.currentView === 'beranda'
                    ? 'text-signature bg-signature/10 border border-signature/30 font-bold shadow-xs'
                    : 'text-black/70 hover:text-signature hover:bg-signature/10 font-medium'"
                class="px-3.5 py-1.5 text-sm font-inter transition-all rounded-lg whitespace-nowrap flex items-center gap-1.5">
                <template x-if="$store.kbtk.currentView === 'beranda'">
                    <span class="w-1.5 h-1.5 rounded-full bg-signature"></span>
                </template>
                <span>Beranda</span>
            </button>

            @foreach (['kb' => 'KB', 'tka' => 'TK A', 'tkb' => 'TK B'] as $key => $label)
                <button
                    @click="$store.kbtk.setView('{{ $key }}')"
                    :class="$store.kbtk.currentView === '{{ $key }}'
                        ? 'text-signature bg-signature/10 border border-signature/30 font-bold shadow-xs'
                        : 'text-black/70 hover:text-signature hover:bg-signature/10 font-medium'"
                    class="px-3.5 py-1.5 text-sm font-inter transition-all rounded-lg whitespace-nowrap flex items-center gap-1.5">
                    <template x-if="$store.kbtk.currentView === '{{ $key }}'">
                        <span class="w-1.5 h-1.5 rounded-full bg-signature"></span>
                    </template>
                    <span>{{ $label }}</span>
                </button>
            @endforeach
        </div>

        {{-- User Info Kanan --}}
        <div class="flex items-center gap-3 shrink-0">
            @if (auth()->user()?->hasRole(['Ketua Yayasan', 'Staf Yayasan']))
                @php
                    $user = auth()->user();
                    $mainDashboardRoute = route('admin.dashboard');
                    if ($user?->hasRole('Ketua Yayasan')) {
                        $mainDashboardRoute = route('ketuayayasan.dashboard');
                    } elseif ($user?->hasRole('Staf Yayasan')) {
                        $mainDashboardRoute = route('stafyayasan.dashboard');
                    }
                @endphp
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

{{-- Alpine Store --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('kbtk', {
            activeTab: 'dashboard',
            currentView: new URLSearchParams(window.location.search).get('kelas') || 'beranda',

            setView(view) {
                if (view === 'beranda') {
                    window.location.href = window.location.pathname;
                } else {
                    window.location.href = window.location.pathname + '?kelas=' + view;
                }
            },

            get activeKelas() {
                return this.currentView;
            },

            kelasLabel(k) {
                return { kb: 'KB', tka: 'TK A', tkb: 'TK B' }[k] ?? k.toUpperCase();
            },

            isBeranda() {
                return this.currentView === 'beranda';
            },

            getCurrentKelasName() {
                if (this.currentView === 'beranda') return 'Semua Kelas';
                return this.kelasLabel(this.currentView);
            }
        });
    });
</script>
