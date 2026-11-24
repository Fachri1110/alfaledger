@php
    $kelas         = $kelas ?? 'Kelas 1';
    $kelasFilter   = $kelasFilter ?? '1';
    $stats         = $stats ?? ['uang_pangkal' => 0, 'spp' => 0, 'total_siswa' => 0];
    $transaksi     = $transaksi ?? [];
    $golonganColor = ['REG' => 'bg-blue-600', 'ANP' => 'bg-signature', 'APK' => 'bg-purple-600', 'FULL' => 'bg-black', 'HALF' => 'bg-amber-500'];
    $statusColor   = ['Lunas' => 'bg-emerald-600', 'Tunggak' => 'bg-red-500'];
@endphp
@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div x-data="dbKelasPage()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- Sub-nav --}}
    <x-sd-nav-tabs active="dashboard" :kelasFilter="$kelasFilter" />

    {{-- ===== TAB: DASHBOARD ===== --}}
    <div x-show="activeTab === 'dashboard'" class="space-y-6">
        <div class="bg-cover bg-center rounded-2xl p-8 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 min-h-[160px] overflow-hidden"
             style="background-image: url('{{ asset('images/headline.png') }}');">
            <div class="z-10">
                <span class="text-base font-inter">SD Alfa Omega Ngabang</span>
                <h1 class="text-3xl font-bold font-inter leading-none mt-1">{{ $kelas }}</h1>
            </div>
            <div class="flex flex-wrap gap-4 z-10">
                <div class="bg-white rounded-xl p-4 min-w-[160px]">
                    <span class="text-xs font-semibold text-black/60 uppercase block mb-1">Uang Pangkal</span>
                    <span class="text-xl font-bold text-signature">Rp {{ number_format($stats['uang_pangkal'], 0, ',', '.') }}</span>
                </div>
                <div class="bg-white rounded-xl p-4 min-w-[160px]">
                    <span class="text-xs font-semibold text-black/60 uppercase block mb-1">Pendapatan SPP</span>
                    <span class="text-xl font-bold text-signature">Rp {{ number_format($stats['spp'], 0, ',', '.') }}</span>
                </div>
                <div class="bg-white rounded-xl p-4 min-w-[130px]">
                    <span class="text-xs font-semibold text-black/60 uppercase block mb-1">Total Siswa</span>
                    <span class="text-xl font-bold text-signature">{{ $stats['total_siswa'] }} Orang</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-shade-white-ii overflow-hidden">
            <div class="bg-signature text-white px-6 py-4 font-bold font-inter">Transaksi Terbaru</div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead><tr class="bg-black text-white text-sm font-semibold font-inter">
                        <th class="px-6 py-3.5 whitespace-nowrap w-64">Nama Siswa</th>
                        <th class="px-6 py-3.5 whitespace-nowrap w-32">Kelas</th>
                        <th class="px-6 py-3.5 text-center whitespace-nowrap w-48">Nominal</th>
                        <th class="px-6 py-3.5 text-center whitespace-nowrap w-32">Tanggal</th>
                        <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Tipe</th>
                        <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Golongan</th>
                    </tr></thead>
                    <tbody class="divide-y divide-shade-white-ii font-inter text-sm">
                        @include('admin.sd.partials.transaksi-rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function dbKelasPage() {
    return {
        activeTab: 'dashboard'
    };
}
</script>
@endsection
