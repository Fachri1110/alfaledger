@extends('components.layouts.app')

@section('header')
    @include('components.header-sd')
@endsection

@section('content')
<div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== TAB NAVIGATION ===== --}}
    <x-sd-nav-tabs active="dashboard" />

    {{-- ===== CONTENT ===== --}}
    <div class="space-y-6">

@php
    $user = auth()->user();
    if ($user?->hasRole('Ketua Yayasan')) {
        $roleLabel = 'Ketua Yayasan';
    } elseif ($user?->hasRole('Staf Yayasan')) {
        $roleLabel = 'Staf Yayasan';
    } elseif ($user?->hasRole('Super Admin')) {
        $roleLabel = 'Super Admin';
    } else {
        $roleLabel = 'Tata Usaha';
    }
@endphp

        {{-- Banner dengan Stats Cards --}}
        <div class="bg-cover bg-center rounded-2xl p-8 text-white shadow-[0_4px_20px_rgba(120,0,139,0.15)] flex items-center justify-between gap-6 min-h-[160px] relative overflow-hidden"
             style="background-image: url('{{ asset('images/headline.png') }}');">

            {{-- Text Kiri --}}
            <div class="flex flex-col justify-center z-10">
                <span class="text-base mb-1 font-inter">
                    {{ $roleLabel }} — SD Alfa Omega Ngabang
                </span>
                <h1 class="text-heading-spp font-bold font-inter leading-none">
                    Halo, {{ $user?->name ?? 'Admin' }}
                </h1>
            </div>

            {{-- Stats Cards Kanan --}}
            <div class="flex flex-wrap items-center gap-4 z-10">
                <div class="bg-white rounded-xl p-4 min-w-[160px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Uang Pangkal</span>
                    <span class="text-xl font-bold text-signature block">
                        Rp {{ number_format($stats['uang_pangkal_terbayar'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <div class="bg-white rounded-xl p-4 min-w-[160px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Pendapatan SPP</span>
                    <span class="text-xl font-bold text-signature block">
                        Rp {{ number_format($stats['spp_lunas'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <div class="bg-white rounded-xl p-4 min-w-[140px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Total Siswa</span>
                    <span class="text-xl font-bold text-signature block">
                        {{ $stats['total_siswa'] ?? 0 }} Orang
                    </span>
                </div>
            </div>
        </div>

        {{-- History Transaksi Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border border-shade-white-ii overflow-hidden">
            <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp font-inter flex items-center gap-3">
                History Transaksi Terbaru
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white font-inter text-sm font-semibold">
                            <th class="px-6 py-3.5 whitespace-nowrap w-64">Nama</th>
                            <th class="px-6 py-3.5 whitespace-nowrap w-32">Kelas</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-48">Nominal</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-24">Tanggal</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Tipe</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap w-12">Golongan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-shade-white-i font-inter text-sm text-black">
                        @include('admin.sd.partials.transaksi-rows')
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
