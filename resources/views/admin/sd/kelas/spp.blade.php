@php
    $kelas         = $kelas ?? 'Kelas 1';
    $kelasFilter   = $kelasFilter ?? '1';
    $transaksi     = $transaksi ?? collect();
    $tahunAjaranId = $tahunAjaranId ?? null;
    $bulanList     = $bulanList ?? ['Jul','Ags','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
    $selectedBulan = $selectedBulan ?? 'Jul';
    $bulanLengkap  = $bulanLengkap  ?? ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
    $bulanNomor    = ['Juli'=>7,'Agustus'=>8,'September'=>9,'Oktober'=>10,'November'=>11,'Desember'=>12,'Januari'=>1,'Februari'=>2,'Maret'=>3,'April'=>4,'Mei'=>5,'Juni'=>6];
@endphp
@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="sppKelasPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- Sub-nav --}}
    <x-sd-nav-tabs active="spp" :kelasFilter="$kelasFilter" />

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6 font-inter">
        <div class="relative flex-1">
            <svg class="w-5 h-5 text-black/40 absolute left-4 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" placeholder="Nama Siswa"
                   class="w-full border border-shade-white-ii rounded-lg pl-11 pr-4 py-3 text-sm outline-none focus:border-signature transition-colors">
        </div>

        {{-- Urutkan --}}
        <div class="relative" @click.outside="sortOpen = false">
            <button @click="sortOpen = !sortOpen"
                    class="flex items-center gap-2 bg-white border border-shade-white-ii rounded-lg px-5 py-3 text-sm font-semibold hover:border-black/30 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="14" y2="12"/><line x1="4" y1="18" x2="9" y2="18"/></svg>
                Urutkan
            </button>
            <div x-show="sortOpen" x-transition x-cloak class="absolute z-30 mt-2 w-48 left-0 bg-white border border-shade-white-ii rounded-lg shadow-lg py-1">
                <button @click="sortTable('nama','asc');sortOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Nama (A-Z)</button>
                <button @click="sortTable('nama','desc');sortOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Nama (Z-A)</button>
                <button @click="sortTable('sisa','desc');sortOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Sisa Terbesar</button>
                <button @click="sortTable('sisa','asc');sortOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Sisa Terkecil</button>
            </div>
        </div>

        {{-- Filter status --}}
        <div class="relative" @click.outside="filterOpen = false">
            <button @click="filterOpen = !filterOpen"
                    class="flex items-center gap-2 bg-white border border-shade-white-ii rounded-lg px-5 py-3 text-sm font-semibold hover:border-black/30 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            <div x-show="filterOpen" x-transition x-cloak class="absolute z-30 mt-2 w-48 right-0 bg-white border border-shade-white-ii rounded-lg shadow-lg py-1">
                <button @click="filterStatus='semua';filterOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Semua Status</button>
                <button @click="filterStatus='lunas';filterOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Lunas</button>
                <button @click="filterStatus='tunggak';filterOpen=false" class="w-full text-left px-4 py-2.5 text-sm hover:bg-shade-white-i/30 cursor-pointer">Tunggak</button>
            </div>
        </div>

        <button @click="openModal()" class="flex items-center gap-2 bg-signature text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Transaksi
        </button>
        <button @click="window.print()" class="flex items-center gap-2 bg-black text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </button>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="rounded-2xl overflow-hidden border border-shade-white-ii shadow-sm font-inter">
        <div class="bg-signature px-6 py-4 flex items-center justify-between">
            <p class="text-white font-bold">
                Transaksi SPP — <span x-text="'Bulan ' + selectedBulan"></span>
            </p>
            <span class="text-white/80 text-xs font-semibold" x-text="displayRows.length + ' Siswa'"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="spp-table">
                <thead><tr class="bg-black text-white text-sm font-semibold">
                    <th class="px-6 py-3.5 whitespace-nowrap w-80">Nama</th>
                    <th class="px-8 py-3.5 text-center whitespace-nowrap w-30">Total Dibayar</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-30">Sisa Pembayaran</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Sisa Bulan</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Golongan</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-shade-white-ii bg-white text-sm text-black">
                    <template x-for="row in displayRows" :key="row.id">
                        <tr class="hover:bg-black/[0.02]">
                            <td class="px-6 py-4 font-medium whitespace-nowrap cursor-pointer hover:text-signature hover:underline underline-offset-2 transition-colors"
                                @click="openEditModal(row)"
                                x-text="row.nama"></td>
                            <td class="px-8 py-4 font-semibold text-center whitespace-nowrap" x-text="row.has_trx ? ('Rp ' + formatRupiah(row.total_terbayar)) : '-'"></td>
                            <td class="px-6 py-4 font-semibold text-center whitespace-nowrap" x-text="row.has_trx ? ('Rp ' + formatRupiah(row.sisa)) : '-'"></td>
                            <td class="px-6 py-4 font-medium text-center whitespace-nowrap" x-text="row.sisa_bulan || '-'"></td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-block text-white text-xs font-bold rounded-full px-4 py-1.5"
                                      :class="getGolonganColor(row.golongan)"
                                      x-text="row.golongan"></span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <template x-if="row.display_status && row.display_status !== '-'">
                                    <span class="inline-block text-xs font-bold rounded-full px-4 py-1.5"
                                          :class="getStatusColor(row.display_status)"
                                          x-text="row.display_status"></span>
                                </template>
                                <template x-if="!row.display_status || row.display_status === '-'">
                                    <span class="text-black/40 font-bold px-2 py-1">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <template x-if="displayRows.length === 0">
                        <tr><td colspan="6" class="px-6 py-8 text-center text-black/40">Tidak ada data siswa.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Filter Bulan --}}
    <div class="flex items-center gap-2 mt-4">
        @foreach ($bulanList as $bulan)
            <button @click="selectedBulan='{{ $bulan }}'"
                    :class="selectedBulan==='{{ $bulan }}' ? 'bg-signature border-signature text-white font-bold' : 'bg-white border-shade-white-ii text-black/80 hover:border-black/30 font-semibold'"
                    class="flex-1 min-w-0 text-center rounded-full border py-2.5 text-sm transition-all cursor-pointer">{{ $bulan }}</button>
        @endforeach
    </div>

    {{-- MODAL: Pembayaran SPP --}}
    <div x-show="showModal" x-cloak x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showModal=false">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showModal=false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden font-inter">

            <div class="bg-signature px-8 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="bg-white text-signature w-6 h-6 rounded flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </span>
                <p class="text-white font-bold text-lg" x-text="editMode ? 'Edit Pembayaran SPP' : 'Pembayaran SPP'"></p>
                </div>
            </div>

            <div class="p-8 space-y-6">
                {{-- Info siswa --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-4">
                    <div class="relative" @click.outside="showDropdown = false">
                        <label class="block text-sm font-bold mb-1">Nama</label>
                        <div class="relative">
                            <svg class="w-5 h-5 text-black/40 absolute left-4 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text"
                                   x-model="form.nama"
                                   @focus="!editMode && (showDropdown = true)"
                                   @input="!editMode && onSearchInput()"
                                   :readonly="editMode"
                                   placeholder="Nama siswa"
                                   :class="editMode ? 'bg-gray-50 cursor-not-allowed' : 'bg-white'"
                                   class="w-full border border-shade-white-ii rounded-lg pl-11 pr-4 py-3 text-sm outline-none focus:border-signature transition-colors">
                        </div>

                        {{-- Dropdown Suggestion --}}
                        <div x-show="showDropdown && filteredStudents.length > 0"
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
                    <div>
                        <label class="block text-sm font-bold mb-1">NISN</label>
                        <input type="text" x-model="form.nisn" readonly class="w-full bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3 text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Golongan</label>
                        <input type="text" x-model="form.golongan" readonly class="w-full bg-gray-50 border border-shade-white-ii rounded-lg px-4 py-3 text-sm outline-none">
                    </div>
                </div>

                {{-- Checklist Bulan --}}
                <div>
                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                        @foreach ($bulanLengkap as $bln)
                        @php $nomorBulan = $bulanNomor[$bln] ?? 0; @endphp
                        <button type="button"
                                x-bind:disabled="!form.siswaId || (form.billableMonths && !form.billableMonths.includes({{ $nomorBulan }})) || (!editMode && form.paidMonths && form.paidMonths.includes({{ $nomorBulan }}))"
                                @click="toggleBulan({{ $nomorBulan }})"
                                class="flex items-center justify-between border rounded-lg px-4 py-3 text-sm transition-all select-none"
                                :class="!form.siswaId || (form.billableMonths && !form.billableMonths.includes({{ $nomorBulan }}))
                                        ? 'border-gray-100 bg-gray-50/50 text-black/20 cursor-not-allowed opacity-50'
                                        : (!editMode && form.paidMonths && form.paidMonths.includes({{ $nomorBulan }})
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 font-semibold cursor-not-allowed'
                                            : (form.bulan.includes({{ $nomorBulan }}) ? 'border-signature bg-signature/5 font-semibold text-signature cursor-pointer' : 'border-shade-white-ii bg-white text-black/80 hover:border-signature/40 cursor-pointer'))">
                            <span>{{ $bln }}</span>
                            
                            <template x-if="!editMode && form.paidMonths && form.paidMonths.includes({{ $nomorBulan }})">
                                <span class="bg-emerald-600 text-white w-5 h-5 rounded-full flex items-center justify-center shrink-0" title="Sudah Dibayar">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </template>

                            <template x-if="editMode || !form.paidMonths || !form.paidMonths.includes({{ $nomorBulan }})">
                                <span :class="form.bulan.includes({{ $nomorBulan }}) ? 'bg-signature border-signature text-white' : 'bg-gray-50 border-shade-white-ii text-black/10'"
                                      class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0 transition-colors">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </template>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div x-show="modalError" class="text-red-600 text-sm" x-text="modalError"></div>
                <div x-show="modalSuccess" class="text-emerald-600 text-sm font-semibold" x-text="modalSuccess"></div>
            </div>

            <div class="bg-[#F2F2F2] px-8 py-5 flex items-center justify-end gap-4">
                <span x-text="editMode ? 'Simpan Perubahan?' : 'Transaksi?'" class="text-sm text-black/60 font-semibold"></span>
                <button @click="showModal=false" class="flex items-center gap-2 bg-white border border-shade-white-ii text-black font-semibold rounded-lg px-5 py-2.5 text-sm hover:bg-black/[0.02] cursor-pointer">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Batal
                </button>
                <button @click="submitSpp()"
                        :disabled="submitting || (!editMode && form.bulan.length===0)"
                        :class="(editMode || form.bulan.length>0) && !submitting ? 'bg-signature hover:opacity-90 cursor-pointer' : 'bg-signature/40 cursor-not-allowed'"
                        class="flex items-center gap-2 text-white font-semibold rounded-lg px-5 py-2.5 text-sm transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
function sppKelasPage() {
    return {
        search: '',
        filterStatus: 'semua',
        filterOpen: false,
        sortOpen: false,
        sortField: 'nama',
        sortDirection: 'asc',
        selectedBulan: @json($selectedBulan),
        showModal: false,
        editMode: false,
        submitting: false,
        modalError: '',
        modalSuccess: '',
        form: { siswaId: null, nama: '', nisn: '', golongan: '', bulan: [], billableMonths: [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6], paidMonths: [] },

        students: @json($rows),
        filteredStudents: [],

        get displayRows() {
            if (!Array.isArray(this.students)) return [];

            let list = this.students.map(st => {
                const hist = (st.monthly_history && st.monthly_history[this.selectedBulan])
                    ? st.monthly_history[this.selectedBulan]
                    : null;

                const hasTrx = hist ? hist.has_trx : (Array.isArray(st.trx_months) && st.trx_months.includes(this.selectedBulan));
                const totalTerbayar = hist ? hist.total_terbayar : ((st.nominal || 0) * (st.lunas_count || 0));
                const sisa = hist ? hist.sisa : st.sisa;
                const sisaBulan = hist ? hist.sisa_bulan : st.sisa_bulan;
                const displayStatus = hist ? hist.status : (st.status === 'Non Aktif' ? 'Non Aktif' : (st.status === 'Lunas' ? 'Lunas' : 'Tunggak'));

                return {
                    ...st,
                    overall_sisa: st.sisa,
                    overall_status: st.status,
                    has_trx: hasTrx,
                    total_terbayar: totalTerbayar,
                    sisa: sisa,
                    sisa_bulan: sisaBulan,
                    display_status: displayStatus,
                };
            });

            if (this.search.trim()) {
                const q = this.search.toLowerCase();
                list = list.filter(st => st.nama.toLowerCase().includes(q));
            }

            if (this.filterStatus !== 'semua') {
                const fs = this.filterStatus.toLowerCase();
                list = list.filter(st => st.display_status.toLowerCase() === fs);
            }

            list.sort((a, b) => {
                if (a.has_trx !== b.has_trx) {
                    return a.has_trx ? -1 : 1;
                }
                if (this.sortField) {
                    let valA, valB;
                    if (this.sortField === 'sisa') {
                        valA = parseFloat(a.sisa) || 0;
                        valB = parseFloat(b.sisa) || 0;
                    } else {
                        valA = (a.nama || '').toLowerCase();
                        valB = (b.nama || '').toLowerCase();
                    }
                    if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                }
                return (a.nama || '').localeCompare(b.nama || '');
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
            return colors[gol] || 'bg-gray-500';
        },

        getStatusColor(status) {
            if (status === 'Lunas') return 'bg-emerald-600 text-white';
            if (status === 'Tunggak') return 'bg-red-600 text-white';
            if (status === 'Non Aktif') return 'bg-white text-black border border-shade-white-ii';
            return 'bg-gray-400 text-white';
        },

        openModal() {
            this.editMode = false;
            this.form = { siswaId: null, nama: '', nisn: '', golongan: '', bulan: [], billableMonths: [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6], paidMonths: [] };
            this.filteredStudents = this.students;
            this.showDropdown = false;
            this.modalError = '';
            this.modalSuccess = '';
            this.submitting = false;
            this.showModal = true;
        },

        openEditModal(st) {
            this.editMode = true;
            this.form = {
                siswaId: st.id,
                nama: st.nama,
                nisn: st.nisn,
                golongan: st.golongan,
                bulan: [...(st.paid_months || [])],
                billableMonths: st.billable_months || [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6],
                paidMonths: st.paid_months || [],
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
                this.form.billableMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
                this.form.paidMonths = [];
                this.form.bulan = [];
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
            this.form.billableMonths = st.billable_months || [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            this.form.paidMonths = st.paid_months || [];
            this.form.bulan = [];
            this.showDropdown = false;
        },

        toggleBulan(nomor) {
            if (!this.editMode && this.form.paidMonths && this.form.paidMonths.includes(nomor)) return;
            if (this.form.billableMonths && !this.form.billableMonths.includes(nomor)) return;
            const idx = this.form.bulan.indexOf(nomor);
            if (idx === -1) this.form.bulan.push(nomor);
            else this.form.bulan.splice(idx, 1);
        },

        sortTable(field, direction) {
            this.sortField = field;
            this.sortDirection = direction;
        },

        submitSpp() {
            if (!this.editMode && !this.form.bulan.length) return;
            this.submitting = true;
            this.modalError = '';
            this.modalSuccess = '';

            const url = this.editMode
                ? '{{ route('admin.sd.kelas.spp.koreksi') }}'
                : '{{ route('admin.sd.kelas.spp.bayar') }}';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    siswa_id: this.form.siswaId,
                    bulan: this.form.bulan,
                    tahun_ajaran_id: {{ $tahunAjaranId ?? 'null' }}
                })
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
            .catch(() => { this.modalError = 'Terjadi kesalahan.'; this.submitting = false; });
        },
    };
}
</script>
@endsection