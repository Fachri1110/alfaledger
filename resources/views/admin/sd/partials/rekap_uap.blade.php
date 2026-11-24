@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="rekapPangkalPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== TAB NAVIGATION ===== --}}
    <x-sd-nav-tabs active="rekap_pangkal" />

    {{-- ============ CONTENT ============ --}}
    <div class="font-inter">

        {{-- Filter kelas + tombol Cetak --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">

            {{-- Dropdown Kelas --}}
            <div class="relative w-full sm:w-64" @click.outside="kelasOpen = false">
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

            {{-- Tombol Cetak --}}
            <button type="button"
                    @click="cetak()"
                    class="flex items-center gap-2 bg-black text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Cetak
            </button>
        </div>

        {{-- Loading indicator --}}
        <div x-show="loading" x-cloak class="flex justify-center items-center py-12">
            <svg class="animate-spin h-8 w-8 text-signature" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Tabel Rekap --}}
        <div x-show="!loading" class="rounded-2xl overflow-hidden border border-shade-white-ii shadow-sm">

            {{-- Judul --}}
            <div class="bg-signature px-6 py-4">
                <p class="text-white font-bold text-base">
                    Rekap Uang Pangkal — <span x-text="selectedKelas"></span>
                </p>
            </div>

            {{-- Header kolom --}}
            <div class="grid grid-cols-6 bg-black px-6 py-3.5">
                <p class="text-white font-bold text-sm col-span-1">Item</p>
                <p class="text-white font-bold text-sm text-center">REG</p>
                <p class="text-white font-bold text-sm text-center">ANP</p>
                <p class="text-white font-bold text-sm text-center">APK</p>
                <p class="text-white font-bold text-sm text-center">FULL</p>
                <p class="text-white font-bold text-sm text-center">HALF</p>
            </div>

            {{-- Body --}}
            <div class="divide-y divide-shade-white-ii bg-white">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-6 px-6 py-4 hover:bg-black/[0.02] transition-colors">
                        <p class="text-sm text-black font-semibold col-span-1" x-text="item.label"></p>
                        <p class="text-sm text-black text-center" x-text="fmt(item.reg)"></p>
                        <p class="text-sm text-black text-center" x-text="fmt(item.anp)"></p>
                        <p class="text-sm text-black text-center" x-text="fmt(item.apk)"></p>
                        <p class="text-sm text-black text-center" x-text="fmt(item.full)"></p>
                        <p class="text-sm text-black text-center" x-text="fmt(item.half)"></p>
                    </div>
                </template>
            </div>

            {{-- Total per Kategori --}}
            <div class="grid grid-cols-6 bg-signature px-6 py-3.5">
                <p class="text-white font-bold text-sm col-span-1">Total</p>
                <p class="text-white font-bold text-sm text-right" x-text="fmt(totals.reg)"></p>
                <p class="text-white font-bold text-sm text-right" x-text="fmt(totals.anp)"></p>
                <p class="text-white font-bold text-sm text-right" x-text="fmt(totals.apk)"></p>
                <p class="text-white font-bold text-sm text-right" x-text="fmt(totals.full)"></p>
                <p class="text-white font-bold text-sm text-right" x-text="fmt(totals.half)"></p>
            </div>

            {{-- Total Keseluruhan --}}
            <div class="grid grid-cols-6 bg-black px-6 py-3.5">
                <p class="text-white font-bold text-sm col-span-1">Total Keseluruhan</p>
                <p class="text-white font-bold text-sm text-right col-span-5" x-text="fmt(totalKeseluruhan)"></p>
            </div>
        </div>

        {{-- Filter Bulan (penuh lebar) --}}
        <div class="flex items-center gap-2 mt-6 print:hidden">
                @foreach ($bulanList as $bulan)
                    <button type="button"
                            @click="changeBulan('{{ $bulan }}')"
                            :class="selectedBulan === '{{ $bulan }}'
                            ? 'bg-signature border-signature text-white font-bold'
                            : 'bg-white border-shade-white-ii text-black/80 hover:border-black/30 font-semibold'"
                        class="flex-1 min-w-0 text-center rounded-full border py-2.5 text-sm transition-all cursor-pointer">
                        {{ $bulan }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sd')) {
            Alpine.store('sd').activeTab = 'rekap_pangkal';
        }
    });

    function rekapPangkalPage() {
        return {
            kelasOpen: false,
            loading: false,
            selectedKelas: @json($selectedKelas),
            selectedBulan: @json($selectedBulan),
            items: @json($items),
            totals: @json($totals),

            get totalKeseluruhan() {
                return (Number(this.totals.reg) || 0)
                    + (Number(this.totals.anp) || 0)
                    + (Number(this.totals.apk) || 0)
                    + (Number(this.totals.full) || 0)
                    + (Number(this.totals.half) || 0);
            },

            fmt(value) {
                if (!value || value === 0) return '-';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            },

            changeKelas(kelas) {
                this.selectedKelas = kelas;
                this.kelasOpen = false;
                this.fetchData();
            },

            changeBulan(bulan) {
                this.selectedBulan = bulan;
                this.fetchData();
            },

            fetchData() {
                this.loading = true;
                let self = this;
                fetch(`{{ route(auth()->user()?->hasRole('Ketua Yayasan') ? 'ketuayayasan.sd.rekap-pangkal' : (auth()->user()?->hasRole('Staf Yayasan') ? 'stafyayasan.sd.rekap-pangkal' : 'admin.sd.rekap-pangkal')) }}?kelas=${encodeURIComponent(self.selectedKelas)}&bulan=${encodeURIComponent(self.selectedBulan)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.items  = data.items;
                        self.totals = data.totals;
                    }
                    self.loading = false;
                })
                .catch(() => { self.loading = false; });
            },

            cetak() {
                window.print();
            },
        };
    }
</script>
@endsection