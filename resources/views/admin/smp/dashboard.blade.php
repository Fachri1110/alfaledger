@extends('components.layouts.app')

@section('title', 'Admin SMP Dashboard — ALFA LEDGER')

@section('content')
<div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
    
    {{-- ===== WELCOME BANNER ===== --}}
    <div class="bg-gradient-to-r from-signature to-[#9c18b7] rounded-2xl p-8 text-white shadow-md mb-8">
        <h1 class="text-heading-spp font-bold mb-2 font-inter">Selamat Datang di ALFA LEDGER SMP!</h1>
        <p class="text-white/80 max-w-2xl text-base-spp font-inter">Sistem Pengelolaan Pembayaran Uang SPP Sekolah. Anda masuk sebagai Admin Jenjang SMP. Kelola data pembayaran, tagihan, dan pantau rekapitulasi keuangan khusus tingkat SMP.</p>
    </div>

    {{-- ===== STATS GRID ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 font-inter">
        <!-- Stat Card 1 -->
        <div class="bg-white rounded-xl p-6 border border-shade-white-ii shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-shade-white-ii uppercase tracking-wider">Total Siswa SMP</span>
                <h3 class="text-subheading-spp font-bold text-black mt-2">580</h3>
            </div>
            <span class="text-xs text-green-600 font-medium mt-4">Aktif di jenjang SMP</span>
        </div>
        <!-- Stat Card 2 -->
        <div class="bg-white rounded-xl p-6 border border-shade-white-ii shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-shade-white-ii uppercase tracking-wider">SPP Terbayar (SMP)</span>
                <h3 class="text-subheading-spp font-bold text-signature mt-2">Rp 113.300.000</h3>
            </div>
            <span class="text-xs text-signature font-medium mt-4">83% dari target bulanan</span>
        </div>
        <!-- Stat Card 3 -->
        <div class="bg-white rounded-xl p-6 border border-shade-white-ii shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-shade-white-ii uppercase tracking-wider">Tunggakan SPP (SMP)</span>
                <h3 class="text-subheading-spp font-bold text-danger mt-2">Rp 26.000.000</h3>
            </div>
            <span class="text-xs text-danger font-medium mt-4">Perlu ditindaklanjuti</span>
        </div>
    </div>

    {{-- ===== SECTION INFO ===== --}}
    <div class="bg-white rounded-xl border border-shade-white-ii p-8 shadow-sm font-inter">
        <h2 class="text-subheading-spp font-bold text-black mb-4">Pengumuman & Informasi Terbaru (SMP)</h2>
        <div class="border-l-4 border-signature bg-signature/10 p-4 rounded-r-lg mb-4 text-base-spp text-signature font-medium">
            <strong>Catatan Keuangan SMP:</strong> Pastikan seluruh entri data pembayaran siswa direkonsiliasi sebelum akhir bulan berjalan.
        </div>
        <p class="text-black/60 text-base-spp">Sistem pengelolaan uang SPP saat ini sedang dalam proses konfigurasi fitur pembayaran.</p>
    </div>

</div>
@endsection
