@extends('components.layouts.app')

@php
    $rolePrefix = 'admin.sd.';
    if (auth()->user()?->hasRole('Ketua Yayasan')) {
        $rolePrefix = 'ketuayayasan.sd.';
    } elseif (auth()->user()?->hasRole('Staf Yayasan')) {
        $rolePrefix = 'stafyayasan.sd.';
    }
@endphp

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="kelolaSppPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- Toast Notification --}}
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-5 right-5 z-[9999] bg-white text-black px-4 py-3 rounded-xl shadow-xl border border-black/10 flex items-center gap-3 min-w-[260px] max-w-sm font-inter overflow-hidden">
        {{-- Close button --}}
        <button @click="dismissToast()" class="shrink-0 text-black/40 hover:text-black transition-colors cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        {{-- Message --}}
        <span x-text="toast.message" class="text-sm font-semibold flex-1"></span>
        {{-- Urungkan button (only shown when hasUndo is true) --}}
        <template x-if="toast.hasUndo">
            <button @click="urungkan()"
                    class="shrink-0 bg-black text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-black/80 transition-colors cursor-pointer whitespace-nowrap">
                Urungkan
            </button>
        </template>
        {{-- Progress bar --}}
        <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-black/5 overflow-hidden rounded-b-xl">
            <div x-show="toast.show" class="h-full bg-black/20 origin-left"
                 style="animation: toastProgress 7s linear forwards;"></div>
        </div>
    </div>
    <style>
        @keyframes toastProgress {
            from { transform: scaleX(1); }
            to   { transform: scaleX(0); }
        }
    </style>

    {{-- ===== TAB NAVIGATION ===== --}}
    <x-sd-nav-tabs active="spp_kelola" />

    {{-- ============ CONTENT ============ --}}
    <div class="font-inter">

        {{-- Dropdown Kelas --}}
        <div class="relative w-full sm:w-64 mb-8" @click.outside="kelasOpen = false">
            <button type="button"
                    @click="kelasOpen = !kelasOpen"
                    class="w-full flex items-center justify-between bg-white border border-shade-white-ii rounded-lg px-4 py-3 text-sm font-bold text-black hover:border-signature transition-colors cursor-pointer">
                <span x-text="selectedKelas"></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/50 transition-transform shrink-0"
                     :class="kelasOpen ? 'rotate-180' : ''"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </button>
            <div x-show="kelasOpen" x-transition x-cloak
                 class="absolute z-30 mt-2 w-full bg-white border border-shade-white-ii rounded-lg shadow-lg overflow-hidden py-1">
                @foreach ($kelasList as $kelas)
                    <button type="button"
                            @click="changeKelas('{{ $kelas }}')"
                            :class="selectedKelas === '{{ $kelas }}' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                            class="w-full text-left px-4 py-2.5 text-sm transition-colors cursor-pointer">
                        {{ $kelas }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- 2 Kolom: Reguler & Anak Pegawai --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
            <template x-for="item in items" :key="item.id">
                <div>
                    <p class="text-sm font-semibold text-black mb-2" x-text="item.label"></p>
                    <div class="flex items-center bg-white border border-shade-white-ii rounded-lg px-4 py-3 focus-within:border-signature transition-colors">
                        <span class="text-sm text-black/50 mr-2 shrink-0">Rp</span>
                        <input type="text"
                               x-model="item.displayAmount"
                               @input="onAmountInput(item)"
                               @blur="formatAmount(item)"
                               inputmode="numeric"
                               class="w-full bg-transparent outline-none text-sm text-black font-medium">
                    </div>
                </div>
            </template>
        </div>

        {{-- Simpan --}}
        <div class="flex items-center justify-end gap-4 mt-10">
            <span class="text-sm font-medium text-black/60" x-show="dirty">Simpan perubahan?</span>
            <button type="button"
                    @click="save()"
                    :disabled="!dirty || loading"
                    :class="dirty && !loading ? 'bg-signature hover:opacity-90 cursor-pointer' : 'bg-signature/40 cursor-not-allowed'"
                    class="flex items-center gap-2 text-white font-semibold rounded-lg px-6 py-2.5 text-sm shadow-sm transition-all">
                <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Menyimpan...' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sd')) {
            Alpine.store('sd').activeTab = 'spp';
        }
    });

    function kelolaSppPage() {
        return {
            kelasOpen: false,
            selectedKelas: @json($selectedKelas ?? 'Kelas 1'),
            dirty: false,
            loading: false,
            toast: { show: false, message: '', hasUndo: false, reloadTimer: null },
            items: @json($items),

            init() {
                this.items = this.items.map(function(item) {
                    item.displayAmount = item.display_amount || new Intl.NumberFormat('id-ID').format(item.amount || 0);
                    return item;
                });
            },

            changeKelas(kelas) {
                this.selectedKelas = kelas;
                this.kelasOpen = false;
                this.fetchData();
            },

            fetchData() {
                let self = this;
                let url = `{{ route($rolePrefix . 'kelola-spp') }}?kelas=${encodeURIComponent(self.selectedKelas)}`;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            self.items = data.items.map(item => {
                                item.displayAmount = item.display_amount || new Intl.NumberFormat('id-ID').format(item.amount || 0);
                                return item;
                            });
                            self.dirty = false;
                        }
                    });
            },

            onAmountInput(item) {
                const numeric = item.displayAmount.replace(/[^0-9]/g, '');
                item.amount = numeric === '' ? 0 : parseInt(numeric, 10);
                this.dirty = true;
            },

            formatAmount(item) {
                item.displayAmount = new Intl.NumberFormat('id-ID').format(item.amount || 0);
            },

            showToast(msg, reloadFn = null) {
                if (this.toast.reloadTimer) clearTimeout(this.toast.reloadTimer);
                this.toast.message = msg;
                this.toast.hasUndo = !!reloadFn;
                this.toast.show = true;
                this.toast.reloadTimer = setTimeout(() => {
                    this.toast.show = false;
                    if (reloadFn) reloadFn();
                }, 7000);
            },

            dismissToast() {
                if (this.toast.reloadTimer) clearTimeout(this.toast.reloadTimer);
                this.toast.show = false;
            },

            urungkan() {
                // Batalkan reload timer — halaman TIDAK reload
                this.dismissToast();
            },

            save() {
                if (!this.dirty || this.loading) return;
                this.loading = true;
                let self = this;
                fetch("{{ route($rolePrefix . 'kelola-spp.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ kelas: self.selectedKelas, items: self.items })
                })
                .then(res => res.json())
                .then(data => {
                    self.loading = false;
                    if (data.success) {
                        self.dirty = false;
                        self.showToast(data.message || 'Data SPP berhasil disimpan!');
                    }
                })
                .catch(() => {
                    self.loading = false;
                    alert('Gagal menyimpan. Silakan coba lagi.');
                });
            },
        };
    }
</script>
@endsection