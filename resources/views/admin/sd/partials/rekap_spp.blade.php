@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="rekapSppPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== TAB NAVIGATION ===== --}}
    <x-sd-nav-tabs active="rekap_spp" />

    {{-- ============ CONTENT ============ --}}
    <div class="font-inter">

        {{-- Tombol Cetak (atas kanan) --}}
        <div class="flex items-center justify-end mb-6 print:hidden">
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
        <div x-show="loading" x-cloak class="flex justify-center items-center py-12 print:hidden">
            <svg class="animate-spin h-8 w-8 text-signature" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        {{-- Tabel Rekap SPP --}}
        <div x-show="!loading" class="rounded-2xl overflow-hidden border border-shade-white-ii shadow-sm print:rounded-none print:border print:border-gray-300 print:shadow-none">

            {{-- Judul --}}
            <div class="bg-signature px-6 py-4 print:px-4 print:py-3">
                <p class="text-white font-bold text-base print:text-sm">
                    Rekap SPP SD
                    <span class="font-normal text-white/80 ml-2 print:block print:mt-0.5" x-text="'Bulan: ' + selectedBulan"></span>
                </p>
            </div>

            {{-- Header kolom --}}
            <div class="grid grid-cols-4 bg-black px-6 py-3.5 print:px-4 print:py-2.5">
                <p class="text-white font-bold text-sm col-span-1 print:text-xs">Kelas</p>
                <p class="text-white font-bold text-sm text-right print:text-xs">REG</p>
                <p class="text-white font-bold text-sm text-right print:text-xs">ANP</p>
                <p class="text-white font-bold text-sm text-right print:text-xs">APK</p>
            </div>

            {{-- Body --}}
            <div class="divide-y divide-shade-white-ii bg-white">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid grid-cols-4 px-6 py-4 hover:bg-black/[0.02] transition-colors print:px-4 print:py-2.5">
                        <p class="text-sm text-black font-semibold col-span-1 print:text-xs" x-text="row.kelas"></p>
                        <p class="text-sm text-black text-right print:text-xs" x-text="fmt(row.reg)"></p>
                        <p class="text-sm text-black text-right print:text-xs" x-text="fmt(row.anp)"></p>
                        <p class="text-sm text-black text-right print:text-xs" x-text="fmt(row.apk)"></p>
                    </div>
                </template>
            </div>

            {{-- Total --}}
            <div class="grid grid-cols-4 bg-signature px-6 py-3.5 print:px-4 print:py-2.5">
                <p class="text-white font-bold text-sm col-span-1 print:text-xs">Total</p>
                <p class="text-white font-bold text-sm text-right print:text-xs" x-text="fmt(totals.reg)"></p>
                <p class="text-white font-bold text-sm text-right print:text-xs" x-text="fmt(totals.anp)"></p>
                <p class="text-white font-bold text-sm text-right print:text-xs" x-text="fmt(totals.apk)"></p>
            </div>

            {{-- Total Keseluruhan --}}
            <div class="grid grid-cols-4 bg-black px-6 py-3.5 print:px-4 print:py-2.5">
                <p class="text-white font-bold text-sm col-span-1 print:text-xs">Total Keseluruhan</p>
                <p class="text-white font-bold text-sm text-right col-span-3 print:text-xs" x-text="fmt(totalKeseluruhan)"></p>
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
                        class="flex-1 text-center rounded-full border py-2.5 text-sm transition-all cursor-pointer min-w-0">
                    {{ $bulan }}
                </button>
            @endforeach
        </div>
    </div>
</div>

<style>
@media print {
    body > *:not(#app):not(main):not([class*="max-w"]) {
        display: none !important;
    }

    header, nav, footer,
    [class*="header-sd"],
    .print\:hidden {
        display: none !important;
    }

    body {
        margin: 0;
        padding: 0;
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .max-w-7xl {
        max-width: 100% !important;
        width: 100% !important;
        padding: 16px !important;
        margin: 0 !important;
    }

    .flex.border-b {
        display: none !important;
    }

    .rounded-2xl,
    .overflow-hidden {
        width: 100% !important;
        border-radius: 0 !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .bg-signature {
        background-color: #7c3aed !important;
        color: white !important;
    }

    .bg-black {
        background-color: #000000 !important;
        color: white !important;
    }

    @page {
        size: A4 portrait;
        margin: 16mm 12mm;
    }
}
</style>

<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sd')) {
            Alpine.store('sd').activeTab = 'rekap_spp';
        }
    });

    function rekapSppPage() {
        return {
            loading: false,
            selectedBulan: @json($selectedBulan),
            rows: @json($rows),
            totals: @json($totals),

            get totalKeseluruhan() {
                return (Number(this.totals.reg) || 0) + (Number(this.totals.anp) || 0) + (Number(this.totals.apk) || 0);
            },

            fmt(value) {
                if (!value || value === 0) return '-';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            },

            changeBulan(bulan) {
                this.selectedBulan = bulan;
                this.fetchData();
            },

            fetchData() {
                this.loading = true;
                let self = this;
                fetch(`{{ route(auth()->user()?->hasRole('Ketua Yayasan') ? 'ketuayayasan.sd.rekap-spp' : (auth()->user()?->hasRole('Staf Yayasan') ? 'stafyayasan.sd.rekap-spp' : 'admin.sd.rekap-spp')) }}?bulan=${encodeURIComponent(self.selectedBulan)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.rows   = data.rows;
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
