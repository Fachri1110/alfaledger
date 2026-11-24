@php
    $kelas           = $kelas ?? 'Kelas 1';
    $kelasFilter     = $kelasFilter ?? '1';
    $siswa           = $siswa ?? collect();
    $golonganOptions = $golonganOptions ?? ['REG','ANP','APK'];
    $sppRates        = $sppRates ?? [];
    $uapRates        = $uapRates ?? [];

    $golonganColor = ['REG'=>'bg-blue-600','ANP'=>'bg-signature','APK'=>'bg-purple-600','HALF'=>'bg-amber-500','FULL'=>'bg-black'];
    $statusColor   = ['Lunas'=>'bg-emerald-600 text-white','Tunggak'=>'bg-red-600 text-white','Non Aktif'=>'bg-white text-black border border-shade-white-ii'];
@endphp
@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="kelolaSiswaPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- Sub-nav --}}
    <x-sd-nav-tabs active="kelola_siswa" :kelasFilter="$kelasFilter" />

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6 font-inter flex-wrap">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="w-5 h-5 text-black/40 absolute left-4 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" placeholder="Cari nama siswa…"
                   class="w-full border border-shade-white-ii rounded-lg pl-11 pr-4 py-3 text-sm outline-none focus:border-signature transition-colors">
        </div>
        <button @click="openTambahSiswa()"
                class="flex items-center gap-2 bg-signature text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Tambah Siswa
        </button>
        <button @click="showNaikKelas=true"
                class="flex items-center gap-2 bg-emerald-600 text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            Naik Kelas
        </button>
        <button @click="window.print()"
                class="flex items-center gap-2 bg-black text-white font-semibold rounded-lg px-5 py-3 text-sm hover:opacity-90 cursor-pointer whitespace-nowrap">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </button>
    </div>

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

    {{-- Tabel Siswa --}}
    <div class="rounded-2xl overflow-hidden border border-shade-white-ii shadow-sm font-inter">
        <div class="bg-signature px-6 py-4"><p class="text-white font-bold">Daftar Siswa — {{ $kelas }}</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead><tr class="bg-black text-white text-sm font-semibold">
                    <th class="px-6 py-3.5 whitespace-nowrap w-80">Nama</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-72">NISN</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Golongan</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-24">UAP</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">SPP</th>
                    <th class="px-6 py-3.5 text-center whitespace-nowrap w-24">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-shade-white-ii bg-white text-sm text-black">
                    @forelse ($siswa as $idx => $s)
                        @php $sArr = is_array($s) ? $s : $s->toArray(); @endphp
                        <tr class="hover:bg-black/[0.02]" x-show="!search || '{{ addslashes($sArr['nama']) }}'.toLowerCase().includes(search.toLowerCase())">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button"
                                        @click="openDetail(students[{{ $idx }}])"
                                        class="font-semibold text-black hover:text-signature transition-colors cursor-pointer text-left">
                                    {{ $sArr['nama'] }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap text-black/60">{{ $sArr['nisn'] }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-block {{ $golonganColor[$sArr['golongan']] ?? 'bg-black/60' }} text-white text-xs font-bold rounded-full px-4 py-1.5">{{ $sArr['golongan'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-block {{ $statusColor[$sArr['uap']] ?? 'bg-black/60 text-white' }} text-xs font-bold rounded-full px-4 py-1.5">{{ $sArr['uap'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-block {{ $statusColor[$sArr['spp']] ?? 'bg-black/60 text-white' }} text-xs font-bold rounded-full px-4 py-1.5">{{ $sArr['spp'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-block {{ ($sArr['status_siswa'] === 'Aktif' || $sArr['status_siswa'] === 'aktif') ? 'bg-black text-white' : 'bg-white text-black border border-shade-white-ii' }} text-xs font-bold rounded-full px-4 py-1.5">
                                    {{ ($sArr['status_siswa'] === 'Aktif' || $sArr['status_siswa'] === 'aktif') ? 'Aktif' : 'Out' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-black/40">Belum ada siswa di kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: Detail Siswa --}}
    <div x-show="showDetail" x-cloak x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showDetail=false">
        <div x-show="showDetail"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showDetail=false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden font-inter flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="bg-signature px-8 py-4 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    <p class="text-white font-bold text-lg">Detail Siswa</p>
                </div>
                <button @click="showDetail=false" class="text-white hover:opacity-70 cursor-pointer">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto flex-1">
                <div class="p-8 space-y-6">

                    {{-- Baris 1: Nama --}}
                    <div>
                        <label class="block text-sm font-bold text-black mb-2">Nama</label>
                        <div class="relative">
                            <input type="text" x-model="detail.nama"
                                   class="w-full border border-shade-white-ii rounded-lg pl-4 pr-11 py-3 text-sm text-black font-medium outline-none focus:border-signature transition-colors">
                            <svg class="w-4 h-4 text-black/50 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </div>
                    </div>

                    {{-- Baris 2: NISN | Tanggal Masuk | Golongan | Status Siswa --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- NISN --}}
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">NISN</label>
                            <div class="relative">
                                <input type="text" x-model="detail.nisn"
                                       class="w-full border border-shade-white-ii rounded-lg pl-4 pr-11 py-3 text-sm text-black outline-none focus:border-signature transition-colors">
                                <svg class="w-4 h-4 text-black/50 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </div>
                        </div>

                        {{-- Tanggal Masuk --}}
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">Tanggal Masuk</label>
                            <div class="relative">
                                <input type="date" x-model="detail.tanggal_masuk"
                                       @input="onTanggalMasukChange()"
                                       @change="onTanggalMasukChange()"
                                       class="w-full border border-shade-white-ii rounded-lg px-4 py-3 text-sm text-black font-medium outline-none focus:border-signature transition-colors">
                            </div>
                        </div>

                        {{-- Golongan --}}
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">Golongan</label>
                            <div class="relative z-30" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="w-full bg-white px-4 py-3 rounded-lg border border-shade-white-ii focus:border-signature text-sm flex items-center justify-between cursor-pointer hover:border-signature/50 transition-colors">
                                    <span class="text-black font-medium" x-text="detail.golongan || 'Pilih Golongan'"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/50 transition-transform shrink-0" :class="open ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full mt-2 bg-white border border-shade-white-ii rounded-lg shadow-xl z-50 overflow-hidden font-inter py-1">
                                    @foreach (['REG', 'ANP', 'APK', 'FULL', 'HALF'] as $opt)
                                        <button type="button" @click="selectGolongan('{{ $opt }}'); open = false"
                                            :class="detail.golongan === '{{ $opt }}' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                            class="w-full text-left px-4 py-2.5 text-sm transition-colors cursor-pointer">{{ $opt }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Status Siswa --}}
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">Status Siswa</label>
                            <div class="relative z-20" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="w-full bg-white px-4 py-3 rounded-lg border border-shade-white-ii focus:border-signature text-sm flex items-center justify-between cursor-pointer hover:border-signature/50 transition-colors">
                                    <span class="text-black font-medium uppercase" x-text="detail.status_siswa || 'Pilih Status'"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/50 transition-transform shrink-0" :class="open ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full mt-2 bg-white border border-shade-white-ii rounded-lg shadow-xl z-50 overflow-hidden font-inter py-1">
                                    <button type="button" @click="detail.status_siswa = 'Aktif'; open = false"
                                        :class="(detail.status_siswa === 'Aktif' || detail.status_siswa === 'AKTIF') ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                        class="w-full text-left px-4 py-2.5 text-sm transition-colors">AKTIF</button>
                                    <button type="button" @click="detail.status_siswa = 'Non Aktif'; open = false"
                                        :class="(detail.status_siswa === 'Non Aktif' || detail.status_siswa === 'NON AKTIF') ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                        class="w-full text-left px-4 py-2.5 text-sm transition-colors">NON AKTIF</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Baris 3: Nominal Uang Pangkal | Nominal SPP (Terkunci / Tidak dapat diubah) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">Nominal Uang Pangkal</label>
                            <div class="relative">
                                <input type="text" :value="fmtRp(detail.nominal_uap)" disabled readonly
                                       class="w-full bg-shade-white-i border border-shade-white-ii rounded-lg px-4 py-3 text-sm text-black font-semibold outline-none cursor-not-allowed">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-black mb-2">Nominal SPP</label>
                            <div class="relative">
                                <input type="text" :value="fmtRp(detail.nominal_spp)" disabled readonly
                                       class="w-full bg-shade-white-i border border-shade-white-ii rounded-lg px-4 py-3 text-sm text-black font-semibold outline-none cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    {{-- Riwayat (Permanen / Tidak dapat dihapus) --}}
                    <div class="rounded-xl overflow-hidden border border-shade-white-ii">
                        <div class="bg-signature px-6 py-3">
                            <p class="text-white font-bold text-sm">Riwayat</p>
                        </div>
                        <div class="bg-[#fafafa] divide-y divide-shade-white-ii">
                            <template x-for="(r, i) in detail.riwayat" :key="i">
                                <div class="px-6 py-3.5 flex items-center justify-between text-sm text-black font-medium hover:bg-black/[0.02] transition-colors gap-4">
                                    <div class="flex items-center gap-6 sm:gap-8 flex-wrap">
                                        <span class="min-w-[150px] whitespace-nowrap text-black/90 font-medium" x-text="r.tanggal"></span>
                                        <span class="w-14 shrink-0 text-black/70 font-medium" x-text="r.jam"></span>
                                        <span class="w-24 shrink-0 text-black font-semibold" x-text="r.function"></span>
                                        <span class="w-12 shrink-0 text-black/80 font-bold" x-text="r.jenis"></span>
                                        <span class="w-24 shrink-0 text-black font-semibold" x-text="fmtRp(r.nominal)"></span>

                                        <template x-if="r.menjadi">
                                            <div class="flex items-center gap-4 shrink-0">
                                                <span class="text-black/40 font-bold">&gt;</span>
                                                <span class="font-bold text-black" x-text="fmtRp(r.nilai_akhir)"></span>
                                            </div>
                                        </template>

                                        <template x-if="r.sisa !== null && r.sisa !== undefined">
                                            <span class="text-xs text-black/50 bg-shade-white-i px-2.5 py-1 rounded-md shrink-0" x-text="'Sisa: Rp ' + fmtRp(r.sisa)"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!detail.riwayat || detail.riwayat.length === 0">
                                <div class="py-8 text-center text-black/40 text-sm">Belum ada riwayat transaksi atau perubahan.</div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-[#F2F2F2] px-8 py-5 flex items-center justify-end gap-3 shrink-0">
                <span class="text-sm text-black font-medium mr-2">Simpan perubahan?</span>
                <button @click="submitEditSiswa()"
                        :disabled="submittingEdit"
                        :class="submittingEdit ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 cursor-pointer'"
                        class="flex items-center gap-2 bg-signature text-white font-semibold rounded-xl px-6 py-2.5 text-sm transition-all">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><polyline points="20 6 9 17 4 12"/></svg>
                    <span x-text="submittingEdit ? 'Menyimpan…' : 'Simpan'"></span>
                </button>
                <button @click="showHapusConfirm = true"
                        :disabled="submittingHapus"
                        :class="submittingHapus ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 cursor-pointer'"
                        title="Hapus Siswa"
                        class="flex items-center justify-center bg-red-600 text-white font-semibold rounded-xl w-10 h-10 transition-all">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Tambah Siswa --}}
    <div x-show="showTambahSiswa" x-cloak x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showTambahSiswa=false">
        <div x-show="showTambahSiswa"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showTambahSiswa=false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl font-inter relative">

            <div class="bg-signature px-8 py-5 flex items-center gap-3 rounded-t-2xl">
                <span class="bg-white text-signature w-7 h-7 rounded-lg flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                </span>
                <p class="text-white font-bold text-lg">Tambah Siswa</p>
            </div>

            <div class="p-8 space-y-5">
                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-bold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" x-model="formSiswa.nama_siswa" placeholder="Nama Siswa"
                               class="w-full border border-shade-white-ii rounded-lg pl-4 pr-11 py-3 text-sm outline-none focus:border-signature transition-colors">
                        <svg class="w-4 h-4 text-black/40 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                    </div>
                </div>

                {{-- NISN, Tanggal Masuk, Golongan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-bold mb-2">NISN</label>
                        <div class="relative">
                            <input type="text" x-model="formSiswa.nisn" placeholder="NISN Siswa"
                                   class="w-full border border-shade-white-ii rounded-lg pl-4 pr-11 py-3 text-sm outline-none focus:border-signature transition-colors">
                            <svg class="w-4 h-4 text-black/40 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Tanggal Masuk</label>
                        <input type="date" x-model="formSiswa.tanggal_masuk"
                               class="w-full border border-shade-white-ii rounded-lg px-4 py-3 text-sm outline-none focus:border-signature transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-black">Golongan</label>
                        <div class="relative z-30" x-data="{ open: false }" @click.away="open = false">
                            <button type="button" @click="open = !open"
                                class="w-full bg-white px-4 py-3 rounded-lg border border-shade-white-ii focus:border-signature text-sm flex items-center justify-between cursor-pointer hover:border-signature/50 transition-colors">
                                <span class="text-black font-medium" x-text="formSiswa.golongan || 'Pilih Golongan'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/50 transition-transform shrink-0" :class="open ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 top-full mt-2 bg-white border border-shade-white-ii rounded-lg shadow-xl z-50 overflow-hidden font-inter py-1">
                                @foreach ($golonganOptions as $opt)
                                    <button type="button" @click="formSiswa.golongan = '{{ $opt }}'; open = false"
                                        :class="formSiswa.golongan === '{{ $opt }}' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                        class="w-full text-left px-4 py-2.5 text-sm transition-colors cursor-pointer">{{ $opt }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="tambahError" x-cloak class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-600 text-sm" x-text="tambahError"></div>
            </div>

            <div class="bg-[#F2F2F2] px-8 py-5 flex items-center justify-end gap-4 rounded-b-2xl">
                <span class="text-sm text-black/60 font-semibold">Tambahkan?</span>
                <button @click="showTambahSiswa=false"
                        class="flex items-center gap-2 bg-white border border-shade-white-ii text-black font-semibold rounded-lg px-5 py-2.5 text-sm hover:bg-black/[0.02] cursor-pointer">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Batal
                </button>
                <button @click="submitTambahSiswa()"
                        :disabled="submittingSiswa"
                        :class="!submittingSiswa ? 'bg-signature hover:opacity-90 cursor-pointer' : 'bg-signature/40 cursor-not-allowed'"
                        class="flex items-center gap-2 text-white font-semibold rounded-lg px-5 py-2.5 text-sm transition-all">
                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14" /></svg>
                    <span x-text="submittingSiswa ? 'Menyimpan…' : 'Tambah Siswa'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- DIALOG: Naik Kelas --}}
    <div x-show="showNaikKelas" x-cloak x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showNaikKelas=false">
        <div x-show="showNaikKelas"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showNaikKelas=false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden font-inter">

            <div class="bg-emerald-600 px-8 py-5 flex items-center gap-3">
                <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                <p class="text-white font-bold text-lg">Konfirmasi Naik Kelas</p>
            </div>

            <div class="p-8 space-y-3 text-sm text-black">
                <p class="font-semibold">Perlu diketahui bahwa:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Siswa dengan status <span class="font-bold">LUNAS</span> (atau kelas yang belum diatur tagihannya oleh admin) akan pindah ke kelas berikutnya</li>
                    <li>Siswa dengan status <span class="font-bold">BELUM LUNAS / TUNGGAK</span> akan dihapus otomatis</li>
                    <li>Siswa dengan status <span class="font-bold">NONAKTIF</span> akan dihapus otomatis</li>
                </ul>
                {{-- Pesan error (kelas atas masih ada siswa) --}}
                <div x-show="naikKelasError"
                     class="mt-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p class="text-red-700 font-semibold text-sm leading-snug" x-text="naikKelasMsg"></p>
                </div>
                {{-- Pesan sukses --}}
                <div x-show="naikKelasMsg && !naikKelasError" class="mt-3 text-emerald-700 font-semibold" x-text="naikKelasMsg"></div>
            </div>

            <div class="bg-[#F2F2F2] px-8 py-5 flex justify-end gap-4">
                <button @click="showNaikKelas=false; naikKelasMsg=''; naikKelasError=false;" class="bg-white border border-shade-white-ii text-black font-semibold rounded-lg px-6 py-2.5 text-sm hover:bg-black/[0.02] cursor-pointer">Batal</button>
                <button @click="submitNaikKelas()"
                        :disabled="submittingNaik"
                        :class="!submittingNaik ? 'bg-emerald-600 hover:opacity-90 cursor-pointer' : 'bg-emerald-600/40 cursor-not-allowed'"
                        class="text-white font-semibold rounded-lg px-6 py-2.5 text-sm transition-all">
                    <span x-text="submittingNaik ? 'Memproses…' : 'Ya, Naik Kelas'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- DIALOG: Konfirmasi Hapus Siswa --}}
    <div x-show="showHapusConfirm" x-cloak x-transition.opacity
         class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="showHapusConfirm=false">
        <div x-show="showHapusConfirm"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showHapusConfirm=false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden font-inter">

            {{-- Header --}}
            <div class="bg-red-600 px-6 py-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
                <p class="text-white font-bold text-base">Hapus siswa</p>
            </div>

            {{-- Body --}}
            <div class="px-6 py-6 space-y-2">
                <p class="text-black font-bold text-sm">Hapus data siswa?</p>
                <p class="text-black/60 text-sm">Data siswa yang terhapus tidak dapat dipulihkan kembali</p>
            </div>

            {{-- Footer --}}
            <div class="bg-[#F2F2F2] px-6 py-4 flex items-center justify-end gap-3">
                <button @click="showHapusConfirm=false"
                        class="flex items-center gap-2 bg-white border border-shade-white-ii text-black font-semibold rounded-xl px-5 py-2.5 text-sm hover:bg-black/[0.03] transition-colors cursor-pointer">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batal
                </button>
                <button @click="showHapusConfirm=false; submitHapusSiswa()"
                        :disabled="submittingHapus"
                        :class="submittingHapus ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 cursor-pointer'"
                        class="flex items-center gap-2 bg-red-600 text-white font-semibold rounded-xl px-5 py-2.5 text-sm transition-all">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    <span x-text="submittingHapus ? 'Menghapus…' : 'Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function kelolaSiswaPage() {
    return {
        students: @json($siswa),
        sppRates: @json($sppRates ?? []),
        uapRates: @json($uapRates ?? []),
        search: '',
        toast: { show: false, message: '', hasUndo: false, reloadTimer: null },

        // Undo state: simpan aksi terakhir beserta data sebelumnya agar bisa dibatalkan
        undoPending: null, // { action: 'tambah'|'edit'|'hapus', siswaId, snapshot }

        showDetail: false,
        detail: {
            id: null,
            nama: '',
            nisn: '',
            golongan: 'REG',
            golongan_asal: 'REG',
            tanggal_masuk: '',
            status_siswa: 'Aktif',
            nominal_uap: 0,
            nominal_spp: 0,
            riwayat: []
        },
        // Simpan snapshot data siswa sebelum diedit (untuk keperluan undo)
        detailSnapshot: null,

        hitungGolonganDariTanggal(tanggalMasukStr, golonganAsal = 'REG') {
            if (!tanggalMasukStr) return golonganAsal || 'REG';
            const parts = tanggalMasukStr.split('-');
            if (parts.length < 3) return golonganAsal || 'REG';
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10);
            if (isNaN(year) || isNaN(month)) return golonganAsal || 'REG';

            const tStartYear = (month >= 7) ? year : year - 1;
            const tEndYear   = tStartYear + 1;

            const bulanNomorList = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            let totalBulan = 0;
            const targetVal = (year * 12) + month;

            for (const m of bulanNomorList) {
                const y = (m >= 7) ? tStartYear : tEndYear;
                if (((y * 12) + m) >= targetVal) {
                    totalBulan++;
                }
            }

            if (totalBulan <= 6) {
                return 'HALF';
            } else if (totalBulan < 12) {
                return 'FULL';
            } else {
                return golonganAsal || 'REG';
            }
        },

        onTanggalMasukChange() {
            if (!this.detail.tanggal_masuk) return;
            const baseGol = this.detail.golongan_asal || (['ANP', 'APK'].includes(this.detail.golongan) ? this.detail.golongan : 'REG');
            const newGol = this.hitungGolonganDariTanggal(this.detail.tanggal_masuk, baseGol);
            this.detail.golongan = newGol;

            if (this.sppRates && this.sppRates[newGol] !== undefined) {
                this.detail.nominal_spp = this.sppRates[newGol];
            }
            if (this.uapRates && this.uapRates[newGol] !== undefined && (!this.detail.riwayat || this.detail.riwayat.length === 0)) {
                this.detail.nominal_uap = this.uapRates[newGol];
            }
        },

        selectGolongan(opt) {
            this.detail.golongan = opt;
            if (['REG', 'ANP', 'APK'].includes(opt)) {
                this.detail.golongan_asal = opt;
            }
            if (this.sppRates && this.sppRates[opt] !== undefined) {
                this.detail.nominal_spp = this.sppRates[opt];
            }
            if (this.uapRates && this.uapRates[opt] !== undefined && (!this.detail.riwayat || this.detail.riwayat.length === 0)) {
                this.detail.nominal_uap = this.uapRates[opt];
            }
        },

        openDetail(st) {
            if (!st) return;
            this.detail = JSON.parse(JSON.stringify(st));
            this.detail.golongan_asal = st.golongan_asal || (['ANP', 'APK'].includes(st.golongan) ? st.golongan : 'REG');
            this.detailSnapshot = JSON.parse(JSON.stringify(this.detail)); // simpan snapshot untuk undo
            this.showDetail = true;
        },

        submittingEdit: false,
        submittingHapus: false,
        showHapusConfirm: false,

        submitEditSiswa() {
            if (!this.detail.id) return;
            this.submittingEdit = true;

            // Simpan snapshot sebelum edit untuk keperluan undo
            const snapshot = this.detailSnapshot ? JSON.parse(JSON.stringify(this.detailSnapshot)) : null;

            fetch(`/admin/sd/kelas/siswa/${this.detail.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nama: this.detail.nama,
                    nisn: this.detail.nisn,
                    tanggal_masuk: this.detail.tanggal_masuk,
                    golongan: this.detail.golongan,
                    golongan_asal: this.detail.golongan_asal,
                    status_siswa: this.detail.status_siswa
                })
            })
            .then(r => r.json())
            .then(data => {
                this.submittingEdit = false;
                if (data.success) {
                    this.showDetail = false;
                    // Set undo state: simpan data lama untuk dikembalikan jika urungkan
                    this.undoPending = {
                        action: 'edit',
                        siswaId: this.detail.id,
                        snapshot: snapshot // data sebelum edit
                    };
                    this.showToast(data.message || 'Data siswa berhasil diperbarui!', () => location.reload(), true);
                } else {
                    this.showToast(data.message || 'Gagal memperbarui data siswa.');
                }
            })
            .catch(() => {
                this.submittingEdit = false;
                this.showToast('Terjadi kesalahan saat menyimpan data.');
            });
        },

        submitHapusSiswa() {
            if (!this.detail.id) return;

            this.submittingHapus = true;

            // Simpan snapshot seluruh data siswa sebelum dihapus untuk keperluan undo
            const siswaSnapshot = JSON.parse(JSON.stringify(this.detail));

            fetch(`/admin/sd/kelas/siswa/${this.detail.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                this.submittingHapus = false;
                if (data.success) {
                    this.showDetail = false;
                    this.showHapusConfirm = false;
                    // Set undo state: simpan data siswa yang dihapus
                    this.undoPending = {
                        action: 'hapus',
                        siswaSnapshot: siswaSnapshot
                    };
                    this.showToast(data.message || 'Siswa berhasil dihapus!', () => location.reload(), true);
                } else {
                    this.showToast(data.message || 'Gagal menghapus siswa.');
                }
            })
            .catch(() => {
                this.submittingHapus = false;
                this.showToast('Terjadi kesalahan saat menghapus siswa.');
            });
        },

        batalPembayaran(r) {
            if (!r || !r.pembayaran_ids || r.pembayaran_ids.length === 0) return;
            if (!confirm(`Apakah Anda yakin ingin membatalkan/menghapus transaksi ${r.atribut} sebesar Rp ${this.fmtRp(r.nominal)} pada ${r.tanggal}?`)) return;

            fetch('{{ route('admin.sd.kelas.pembayaran.batal') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    pembayaran_ids: r.pembayaran_ids
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showDetail = false;
                    this.showToast(data.message || 'Pembayaran berhasil dibatalkan!', () => location.reload());
                } else {
                    this.showToast(data.message || 'Gagal membatalkan pembayaran.');
                }
            })
            .catch(() => {
                this.showToast('Terjadi kesalahan saat membatalkan pembayaran.');
            });
        },

        fmtRp(val) {
            if (val === null || val === undefined || val === '') return '0';
            const num = Number(val);
            if (isNaN(num)) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatTanggal(dStr) {
            if (!dStr) return '-';
            try {
                const parts = dStr.split('-');
                if (parts.length === 3) {
                    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    const day = parseInt(parts[2], 10);
                    const monthIdx = parseInt(parts[1], 10) - 1;
                    const year = parts[0];
                    return `${day} ${months[monthIdx] || ''} ${year}`;
                }
                return dStr;
            } catch(e) {
                return dStr;
            }
        },

        showToast(msg, reloadFn = null, hasUndo = false) {
            if (this.toast.reloadTimer) clearTimeout(this.toast.reloadTimer);
            this.toast.message = msg;
            this.toast.hasUndo = hasUndo;
            this.toast.reloadFn = reloadFn;
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
            // Batalkan timer reload agar halaman tidak refresh dulu
            this.dismissToast();

            const undo = this.undoPending;
            if (!undo) { location.reload(); return; }
            this.undoPending = null;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            if (undo.action === 'tambah') {
                // Undo tambah siswa → hapus siswa yang baru ditambahkan
                fetch(`/admin/sd/kelas/siswa/${undo.siswaId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Penambahan siswa berhasil diurungkan.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.showToast('Gagal mengurungkan: ' + (data.message || ''));
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(() => { this.showToast('Gagal mengurungkan penambahan siswa.'); setTimeout(() => location.reload(), 1500); });

            } else if (undo.action === 'edit') {
                // Undo edit siswa → kembalikan data ke nilai sebelum edit
                const s = undo.snapshot;
                if (!s || !undo.siswaId) { location.reload(); return; }
                fetch(`/admin/sd/kelas/siswa/${undo.siswaId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        nama:          s.nama,
                        nisn:          s.nisn,
                        tanggal_masuk: s.tanggal_masuk,
                        golongan:      s.golongan,
                        golongan_asal: s.golongan_asal,
                        status_siswa:  s.status_siswa
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Perubahan data siswa berhasil diurungkan.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.showToast('Gagal mengurungkan: ' + (data.message || ''));
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(() => { this.showToast('Gagal mengurungkan perubahan.'); setTimeout(() => location.reload(), 1500); });

            } else if (undo.action === 'hapus') {
                // Undo hapus siswa → buat ulang siswa dengan data yang sama
                const s = undo.siswaSnapshot;
                if (!s) { location.reload(); return; }
                fetch('{{ route('admin.sd.kelas.siswa.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        nama_siswa:    s.nama,
                        nisn:          s.nisn,
                        tanggal_masuk: s.tanggal_masuk,
                        golongan:      s.golongan,
                        kelas:         '{{ $kelasFilter }}'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.showToast('Penghapusan siswa berhasil diurungkan.');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.showToast('Gagal mengurungkan penghapusan: ' + (data.message || ''));
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(() => { this.showToast('Gagal mengurungkan penghapusan.'); setTimeout(() => location.reload(), 1500); });

            } else {
                location.reload();
            }
        },

        showTambahSiswa: false,
        submittingSiswa: false,
        tambahError: '',
        formSiswa: { nama_siswa: '', nisn: '', tanggal_masuk: '', golongan: '{{ $golonganOptions[0] ?? "REG" }}' },

        showNaikKelas: false,
        submittingNaik: false,
        naikKelasMsg: '',
        naikKelasError: false,

        openTambahSiswa() {
            this.formSiswa = { nama_siswa: '', nisn: '', tanggal_masuk: '', golongan: '{{ $golonganOptions[0] ?? "REG" }}' };
            this.tambahError = '';
            this.showTambahSiswa = true;
        },

        submitTambahSiswa() {
            if (!this.formSiswa.nama_siswa || !this.formSiswa.nisn || !this.formSiswa.tanggal_masuk) {
                this.tambahError = 'Semua field wajib diisi.'; return;
            }
            this.submittingSiswa = true;
            this.tambahError = '';

            fetch('{{ route('admin.sd.kelas.siswa.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ...this.formSiswa, kelas: '{{ $kelasFilter }}' })
            })
            .then(r => r.json())
            .then(data => {
                this.submittingSiswa = false;
                if (data.success) {
                    this.showTambahSiswa = false;
                    // Set undo state: simpan ID siswa yang baru dibuat agar bisa dihapus kembali
                    this.undoPending = {
                        action: 'tambah',
                        siswaId: data.siswa_id
                    };
                    this.showToast(data.message || 'Siswa berhasil ditambahkan!', () => location.reload(), true);
                } else {
                    this.tambahError = data.message;
                }
            })
            .catch(() => { this.tambahError = 'Terjadi kesalahan.'; this.submittingSiswa = false; });
        },

        submitNaikKelas() {
            this.submittingNaik = true;
            this.naikKelasMsg = '';
            this.naikKelasError = false;

            fetch('{{ route('admin.sd.kelas.naik-kelas') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ kelas: '{{ $kelasFilter }}' })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.submittingNaik = false;
                this.naikKelasMsg = data.message;
                if (data.success && ok) {
                    this.naikKelasError = false;
                    this.showToast(data.message || 'Proses naik kelas berhasil!');
                    setTimeout(() => {
                        this.showNaikKelas = false;
                        if (data.next_kelas && data.promoted > 0) {
                            window.location.href = `/admin/sd/kelas/siswa?kelas=${data.next_kelas}`;
                        } else {
                            location.reload();
                        }
                    }, 1500);
                } else {
                    this.naikKelasError = true;
                }
            })
            .catch(() => { this.naikKelasMsg = 'Terjadi kesalahan jaringan. Silakan coba lagi.'; this.naikKelasError = true; this.submittingNaik = false; });
        },
    };
}
</script>
@endsection