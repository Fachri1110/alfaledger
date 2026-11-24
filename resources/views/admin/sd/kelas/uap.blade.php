@php
    $kelasFilter   = $kelasFilter ?? '1';
    $transaksi     = $transaksi ?? collect();
    $bulanList     = $bulanList ?? ['Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
    $selectedBulan = $selectedBulan ?? 'Jul';
@endphp
@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="uangPangkalKelasPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== SUB-NAV TAB COMPONENT ===== --}}
    <x-sd-nav-tabs active="uang_pangkal" :kelasFilter="$kelasFilter" />

    {{-- ============ CONTENT ============ --}}
    <div class="font-inter">

        {{-- ===== TOOLBAR ===== --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6">

            {{-- Search --}}
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black/40 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="Nama Siswa"
                       class="w-full bg-white border border-shade-white-ii rounded-lg pl-11 pr-4 py-3 text-sm text-black placeholder-black/40 outline-none focus:border-signature transition-colors">
            </div>

            {{-- Urutkan --}}
            <div class="relative" @click.outside="urutkanOpen = false">
                <button type="button"
                        @click="urutkanOpen = !urutkanOpen"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white border border-shade-white-ii rounded-lg px-5 py-3 text-sm font-semibold text-black hover:border-black/30 transition-colors cursor-pointer whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="6" x2="20" y2="6" />
                        <line x1="4" y1="12" x2="14" y2="12" />
                        <line x1="4" y1="18" x2="9" y2="18" />
                    </svg>
                    Urutkan
                </button>
                <div x-show="urutkanOpen" x-transition x-cloak
                     class="absolute z-30 mt-2 w-48 right-0 bg-white border border-shade-white-ii rounded-lg shadow-lg overflow-hidden py-1">
                    <button type="button" @click="urutkan = 'nama_asc'; urutkanOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-black/80 hover:bg-shade-white-i/30 cursor-pointer">Nama (A-Z)</button>
                    <button type="button" @click="urutkan = 'nama_desc'; urutkanOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-black/80 hover:bg-shade-white-i/30 cursor-pointer">Nama (Z-A)</button>
                </div>
            </div>

            {{-- Filter --}}
            <div class="relative" @click.outside="filterOpen = false">
                <button type="button"
                        @click="filterOpen = !filterOpen"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white border border-shade-white-ii rounded-lg px-5 py-3 text-sm font-semibold text-black hover:border-black/30 transition-colors cursor-pointer whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                    </svg>
                    Filter
                </button>
                <div x-show="filterOpen" x-transition x-cloak
                     class="absolute z-30 mt-2 w-48 right-0 bg-white border border-shade-white-ii rounded-lg shadow-lg overflow-hidden py-1">
                    <button type="button" @click="filterStatus = 'semua'; filterOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-black/80 hover:bg-shade-white-i/30 cursor-pointer">Semua Status</button>
                    <button type="button" @click="filterStatus = 'lunas'; filterOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-black/80 hover:bg-shade-white-i/30 cursor-pointer">Lunas</button>
                    <button type="button" @click="filterStatus = 'tunggak'; filterOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-black/80 hover:bg-shade-white-i/30 cursor-pointer">Tunggak</button>
                </div>
            </div>

            {{-- Transaksi (buka modal) --}}
            <button type="button"
                    @click="openModal()"
                    class="flex items-center justify-center gap-2 bg-signature text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 transition-all cursor-pointer whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <line x1="12" y1="8" x2="12" y2="16" />
                    <line x1="8" y1="12" x2="16" y2="12" />
                </svg>
                Transaksi
            </button>

            {{-- Cetak --}}
            <button type="button"
                    @click="cetak()"
                    class="flex items-center justify-center gap-2 bg-black text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 transition-all cursor-pointer whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Cetak
            </button>
        </div>

        {{-- ===== Tabel Transaksi Terbaru ===== --}}
        <div class="rounded-2xl overflow-hidden border border-shade-white-ii shadow-sm">
            <div class="bg-signature px-6 py-4 flex items-center justify-between">
                <p class="text-white font-bold text-base">
                    Transaksi Uang Pangkal — <span x-text="'Bulan ' + selectedBulan"></span>
                </p>
                <span class="text-white/80 text-xs font-semibold" x-text="displayRows.length + ' Siswa'"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white text-sm font-semibold">
                            <th class="px-6 py-3.5 whitespace-nowrap w-80">Nama</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-30">Nominal</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-30">Sisa Pembayaran</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Golongan</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-shade-white-ii bg-white text-sm text-black">
                        <template x-for="trx in displayRows" :key="trx.id">
                            <tr class="hover:bg-black/[0.02] transition-colors">
                                <td class="px-6 py-4 font-medium whitespace-nowrap cursor-pointer hover:text-signature hover:underline underline-offset-2 transition-colors"
                                    @click="openEditModal(trx)"
                                    x-text="trx.nama"></td>
                                <td class="px-8 py-4 font-semibold text-center whitespace-nowrap" x-text="trx.has_trx ? ('Rp ' + formatRupiah(trx.nominal_bulan)) : '-'"></td>
                                <td class="px-6 py-4 font-semibold text-center whitespace-nowrap" x-text="trx.sisa > 0 ? ('Rp ' + formatRupiah(trx.sisa)) : '-'"></td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-block text-white text-xs font-bold rounded-full px-4 py-1.5"
                                          :class="getGolonganColor(trx.golongan)"
                                          x-text="trx.golongan"></span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <template x-if="trx.status && trx.status !== 'Non Aktif'">
                                        <span class="inline-block text-xs font-bold rounded-full px-4 py-1.5"
                                              :class="getStatusColor(trx.status)"
                                              x-text="trx.status"></span>
                                    </template>
                                    <template x-if="!trx.status || trx.status === 'Non Aktif'">
                                        <span class="text-black/40 font-bold px-2 py-1" x-text="trx.status === 'Non Aktif' ? 'Non Aktif' : '-'"></span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <template x-if="displayRows.length === 0">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-black/40">Tidak ada data siswa.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== Filter Bulan ===== --}}
        <div class="flex items-center gap-2 mt-4">
            @foreach ($bulanList as $bulan)
                <button type="button"
                        @click="selectedBulan = '{{ $bulan }}'"
                        :class="selectedBulan === '{{ $bulan }}'
                            ? 'bg-signature border-signature text-white font-bold'
                            : 'bg-white border-shade-white-ii text-black/80 hover:border-black/30 font-semibold'"
                        class="flex-1 min-w-0 text-center rounded-full border py-2.5 text-sm transition-all cursor-pointer">
                    {{ $bulan }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===== MODAL: Pembayaran Uang Pangkal ===== --}}
    <div x-show="showModal"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showModal = false">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showModal = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden font-inter">

            {{-- Header --}}
            <div class="bg-signature px-8 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 flex items-center justify-center border-2 border-white rounded-md shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                    </span>
                    <p class="text-white font-bold text-lg" x-text="editMode ? 'Detail & Edit Pembayaran UAP' : 'Pembayaran Uang Pangkal'"></p>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-8 space-y-6">

                {{-- BARIS 1 (3 KOLOM): NAMA, NISN, GOLONGAN --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    {{-- Nama --}}
                    <div class="relative" @click.outside="showDropdown = false">
                        <label class="block text-sm font-bold text-black mb-2">NAMA</label>
                        <div class="relative">
                            <template x-if="!editMode">
                                <div class="relative">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black/40 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                    </svg>
                                    <input type="text"
                                           x-model="form.nama"
                                           @focus="showDropdown = true"
                                           @input="onSearchInput()"
                                           placeholder="Cari nama siswa"
                                           class="w-full bg-white border border-shade-white-ii rounded-lg pl-11 pr-4 py-3.5 text-sm text-black placeholder-black/40 outline-none focus:border-signature transition-colors">
                                </div>
                            </template>
                            <template x-if="editMode">
                                <input type="text"
                                       x-model="form.nama"
                                       readonly
                                       class="w-full bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3.5 text-sm font-semibold text-black outline-none cursor-not-allowed">
                            </template>
                        </div>

                        {{-- Dropdown Suggestion --}}
                        <div x-show="!editMode && showDropdown && filteredStudents.length > 0"
                             class="absolute z-50 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-shade-white-ii rounded-lg shadow-lg py-1">
                            <template x-for="st in filteredStudents" :key="st.id">
                                <button type="button"
                                        @click="selectStudent(st)"
                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer flex items-center justify-between">
                                    <span x-text="st.nama" class="font-semibold text-black"></span>
                                    <span x-text="st.golongan" class="text-xs bg-signature/10 text-signature px-2 py-0.5 rounded-full font-bold"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- NISN --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">NISN</label>
                        <input type="text"
                               x-model="form.nisn"
                               readonly
                               placeholder="-"
                               class="w-full bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3.5 text-sm text-black outline-none cursor-not-allowed">
                    </div>

                    {{-- Golongan --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">GOLONGAN</label>
                        <input type="text"
                               x-model="form.golongan"
                               readonly
                               placeholder="-"
                               class="w-full bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3.5 text-sm font-semibold text-black outline-none cursor-not-allowed">
                    </div>
                </div>

                {{-- BARIS 2 (2 KOLOM): UANG PANGKAL, SISA TAGIHAN --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Uang Pangkal --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">UANG PANGKAL</label>
                        <div class="flex items-center bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3.5">
                            <span class="text-sm text-black/50 mr-2 shrink-0 font-medium">Rp</span>
                            <input type="text"
                                   :value="form.nominalRaw ? formatRupiah(form.nominalRaw) : '0'"
                                   readonly
                                   class="w-full bg-transparent outline-none text-sm font-semibold text-black cursor-not-allowed">
                        </div>
                    </div>

                    {{-- Sisa Tagihan --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">SISA TAGIHAN</label>
                        <div class="flex items-center bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3.5">
                            <span class="text-sm text-black/50 mr-2 shrink-0 font-medium">Rp</span>
                            <input type="text"
                                   :value="formatRupiah(computedSisaTagihan)"
                                   readonly
                                   class="w-full bg-transparent outline-none text-sm font-semibold text-black cursor-not-allowed">
                        </div>
                    </div>
                </div>

                {{-- BARIS 3 (2 KOLOM): NOMINAL PEMBAYARAN, TANGGAL PEMBAYARAN --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Nominal Pembayaran (nominal yang sudah dibayarkan) --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">
                            NOMINAL PEMBAYARAN
                        </label>
                        <div class="flex items-center bg-white border rounded-lg px-4 py-3.5 transition-colors"
                             :class="isNominalExceedsTotal ? 'border-red-400 focus-within:border-red-500' : 'border-shade-white-ii focus-within:border-signature'">
                            <span class="text-sm text-black/50 mr-2 shrink-0 font-medium">Rp</span>
                            <input type="number"
                                   x-model="form.nominalPembayaran"
                                   placeholder="Masukkan nominal"
                                   min="0"
                                   :max="form.nominalRaw"
                                   class="w-full bg-transparent outline-none text-sm font-semibold text-black">
                        </div>
                        <p x-show="isNominalExceedsTotal" class="text-xs text-red-500 mt-1 font-semibold">
                            ⚠ Nominal pembayaran tidak boleh melebihi <span x-text="editMode ? 'Uang Pangkal' : 'Sisa Tagihan'"></span> (Rp <span x-text="formatRupiah(editMode ? form.nominalRaw : form.currentSisa)"></span>)
                        </p>
                    </div>

                    {{-- Tanggal Pembayaran --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">TANGGAL PEMBAYARAN</label>
                        <input type="date"
                               x-model="form.tanggalPembayaran"
                               class="w-full bg-white border border-shade-white-ii rounded-lg px-4 py-3.5 text-sm text-black outline-none focus:border-signature transition-colors cursor-pointer">
                    </div>
                </div>

                <div x-show="modalError" class="text-red-600 text-sm font-medium" x-text="modalError"></div>
                <div x-show="modalSuccess" class="text-emerald-600 text-sm font-semibold" x-text="modalSuccess"></div>
            </div>

            {{-- Footer --}}
            <div class="bg-[#F2F2F2] px-8 py-5 flex items-center justify-end gap-4">
                <span class="text-sm font-medium text-black/60" x-text="editMode ? 'Simpan perubahan?' : 'Transaksi?'"></span>
                <button type="button"
                        @click="showModal = false"
                        class="flex items-center gap-2 bg-white border border-shade-white-ii text-black font-semibold rounded-lg px-6 py-2.5 text-sm hover:bg-black/[0.02] transition-all cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    Batal
                </button>
                <button type="button"
                        @click="submitPembayaran()"
                        :disabled="!isFormValid || submitting"
                        :class="isFormValid && !submitting ? 'bg-signature hover:opacity-90 cursor-pointer' : 'bg-signature/40 cursor-not-allowed'"
                        class="flex items-center gap-2 text-white font-semibold rounded-lg px-6 py-2.5 text-sm transition-all">
                    <svg x-show="editMode" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <svg x-show="!editMode" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                    <span x-text="submitting ? 'Memproses…' : (editMode ? 'Simpan' : 'Bayar')"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        if (Alpine.store('sd')) {
            Alpine.store('sd').activeTab = 'kelas';
        }
    });

    function uangPangkalKelasPage() {
        return {
            search: '',
            urutkan: null,
            urutkanOpen: false,
            filterStatus: 'semua',
            filterOpen: false,
            selectedBulan: @json($selectedBulan),
            showModal: false,
            editMode: false,
            submitting: false,
            modalError: '',
            modalSuccess: '',
            students: @json($transaksi),
            filteredStudents: [],
            showDropdown: false,

            get displayRows() {
                if (!Array.isArray(this.students)) return [];

                let list = this.students.map(st => {
                    const hist = (st.monthly_history && st.monthly_history[this.selectedBulan])
                        ? st.monthly_history[this.selectedBulan]
                        : null;

                    const hasTrx = hist ? hist.has_trx : (Array.isArray(st.trx_months) && st.trx_months.includes(this.selectedBulan));
                    const nominalBulan = hist ? hist.nominal_bulan : ((st.monthly_payments && st.monthly_payments[this.selectedBulan]) || 0);
                    const sisaBulan = hist ? hist.sisa : st.sisa;
                    const statusBulan = hist ? hist.status : st.status;

                    return {
                        ...st,
                        overall_sisa: st.sisa,
                        overall_status: st.status,
                        has_trx: hasTrx,
                        nominal_bulan: nominalBulan,
                        sisa: sisaBulan,
                        status: statusBulan,
                    };
                });

                if (this.search.trim()) {
                    const q = this.search.toLowerCase();
                    list = list.filter(st => st.nama.toLowerCase().includes(q));
                }

                if (this.filterStatus !== 'semua') {
                    const fs = this.filterStatus.toLowerCase();
                    list = list.filter(st => st.status.toLowerCase() === fs);
                }

                list.sort((a, b) => {
                    if (a.has_trx !== b.has_trx) {
                        return a.has_trx ? -1 : 1;
                    }
                    if (this.urutkan === 'nama_asc') {
                        return a.nama.localeCompare(b.nama);
                    } else if (this.urutkan === 'nama_desc') {
                        return b.nama.localeCompare(a.nama);
                    }
                    return a.nama.localeCompare(b.nama);
                });

                return list;
            },

            formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            },

            getGolonganColor(gol) {
                const colors = {
                    'REG': 'bg-blue-600',
                    'ANP': 'bg-signature',
                    'APK': 'bg-purple-600',
                    'HALF': 'bg-amber-500',
                    'FULL': 'bg-black'
                };
                return colors[gol] || 'bg-black/60';
            },

            getStatusColor(status) {
                const colors = {
                    'Lunas': 'bg-emerald-600 text-white',
                    'Tunggak': 'bg-red-600 text-white',
                    'Non Aktif': 'bg-white text-black border border-shade-white-ii'
                };
                return colors[status] || 'bg-black/60 text-white';
            },

            form: {
                siswaId: null,
                nama: '',
                nisn: '',
                golongan: '',
                nominalRaw: 0,
                currentSisa: 0,
                initialTerbayar: 0,
                nominalPembayaran: '',
                tanggalPembayaran: new Date().toISOString().split('T')[0],
            },

            get computedSisaTagihan() {
                if (!this.form.siswaId) return 0;
                const nominalRaw = parseFloat(this.form.nominalRaw) || 0;
                const bayar = parseFloat(this.form.nominalPembayaran) || 0;

                if (this.editMode) {
                    // Mode edit: Sisa Tagihan = Total UAP - Nominal Pembayaran Baru
                    return Math.max(0, Math.round(nominalRaw - bayar));
                } else {
                    // Mode transaksi baru: Sisa Tagihan = Sisa Saat Ini - Pembayaran Baru
                    const currentSisa = parseFloat(this.form.currentSisa) || 0;
                    return Math.max(0, Math.round(currentSisa - bayar));
                }
            },

            get isNominalExceedsTotal() {
                if (!this.form.siswaId) return false;
                const bayar = parseFloat(this.form.nominalPembayaran);
                if (isNaN(bayar)) return false;
                if (this.editMode) {
                    return bayar > (parseFloat(this.form.nominalRaw) || 0);
                } else {
                    return bayar > (parseFloat(this.form.currentSisa) || 0);
                }
            },

            get isFormValid() {
                if (!this.form.siswaId) return false;
                const bayar = parseFloat(this.form.nominalPembayaran);
                if (isNaN(bayar) || bayar < 0) return false;
                if (this.editMode) {
                    return bayar <= (parseFloat(this.form.nominalRaw) || 0) && !!this.form.tanggalPembayaran;
                }
                return bayar > 0 && bayar <= (parseFloat(this.form.currentSisa) || 0) && !!this.form.tanggalPembayaran;
            },

            openModal() {
                const today = new Date().toISOString().split('T')[0];
                this.editMode = false;
                this.form = {
                    siswaId: null,
                    nama: '',
                    nisn: '',
                    golongan: '',
                    nominalRaw: 0,
                    currentSisa: 0,
                    initialTerbayar: 0,
                    nominalPembayaran: '',
                    tanggalPembayaran: today,
                };
                this.filteredStudents = this.students;
                this.showDropdown = false;
                this.modalError = '';
                this.modalSuccess = '';
                this.submitting = false;
                this.showModal = true;
            },

            openEditModal(st) {
                const today = new Date().toISOString().split('T')[0];
                this.editMode = true;
                const nominalRaw = parseFloat(st.nominal) || 0;
                const terbayar = parseFloat(st.terbayar) || 0;
                const sisa = parseFloat(st.overall_sisa ?? st.sisa) || 0;

                this.form = {
                    siswaId: st.id,
                    nama: st.nama,
                    nisn: st.nisn,
                    golongan: st.golongan,
                    nominalRaw: nominalRaw,
                    currentSisa: sisa,
                    initialTerbayar: terbayar,
                    nominalPembayaran: terbayar,
                    tanggalPembayaran: st.last_payment_date || today,
                };
                this.filteredStudents = this.students;
                this.showDropdown = false;
                this.modalError = '';
                this.modalSuccess = '';
                this.submitting = false;
                this.showModal = true;
            },

            onSearchInput() {
                this.showDropdown = true;
                if (!this.form.nama.trim()) {
                    this.filteredStudents = this.students;
                    this.form.siswaId = null;
                    this.form.nisn = '';
                    this.form.golongan = '';
                    this.form.nominalRaw = 0;
                    this.form.currentSisa = 0;
                    this.form.initialTerbayar = 0;
                    this.form.nominalPembayaran = '';
                } else {
                    const q = this.form.nama.toLowerCase();
                    this.filteredStudents = this.students.filter(s => s.nama.toLowerCase().includes(q));
                }
            },

            selectStudent(st) {
                this.form.siswaId = st.id;
                this.form.nama = st.nama;
                this.form.nisn = st.nisn;
                this.form.golongan = st.golongan;
                this.form.nominalRaw = parseFloat(st.nominal) || 0;
                this.form.currentSisa = parseFloat(st.overall_sisa ?? st.sisa) || 0;
                this.form.initialTerbayar = parseFloat(st.terbayar) || 0;
                this.form.nominalPembayaran = (st.overall_sisa ?? st.sisa) > 0 ? (st.overall_sisa ?? st.sisa) : '';
                this.showDropdown = false;
            },

            submitPembayaran() {
                if (!this.isFormValid || this.submitting) return;
                this.submitting = true;
                this.modalError = '';
                this.modalSuccess = '';

                // Edit mode: kirim nominal_pembayaran ke endpoint koreksi
                // Normal mode: kirim nominal_pembayaran ke endpoint bayar
                const url = this.editMode
                    ? '{{ route('admin.sd.kelas.uap.koreksi') }}'
                    : '{{ route('admin.sd.kelas.uap.bayar') }}';

                const payload = this.editMode
                    ? { siswa_id: this.form.siswaId, nominal_pembayaran: parseFloat(this.form.nominalPembayaran), tanggal: this.form.tanggalPembayaran }
                    : { siswa_id: this.form.siswaId, nominal_pembayaran: parseFloat(this.form.nominalPembayaran), tanggal_pembayaran: this.form.tanggalPembayaran };

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.modalSuccess = data.message;
                        setTimeout(() => { this.showModal = false; location.reload(); }, 1200);
                    } else {
                        this.modalError = data.message;
                    }
                    this.submitting = false;
                })
                .catch(() => {
                    this.modalError = 'Terjadi kesalahan.';
                    this.submitting = false;
                });
            },

            cetak() {
                window.print();
            },
        };
    }
</script>
@endsection