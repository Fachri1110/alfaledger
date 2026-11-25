@extends('components.layouts.app')

@section('header')
    @include('components.header-kbtk')
@endsection

@section('content')
<div x-data="{}" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== TAB NAVIGATION ===== --}}
    <div class="flex border-b border-shade-white-ii mb-6">
        @foreach ([
            'dashboard' => 'Dashboard',
            'rekap_pangkal' => 'Rekap Uang Pangkal',
            'rekap_spp' => 'Rekap SPP',
            'pangkal' => 'Kelola Uang Pangkal',
            'spp' => 'Kelola SPP',
        ] as $tab => $label)
            <button
                @click="$store.kbtk.activeTab = '{{ $tab }}'"
                :class="$store.kbtk.activeTab === '{{ $tab }}'
                    ? 'bg-[#F2F2F2] border-b-2 border-signature text-signature font-bold'
                    : 'text-black/60 hover:text-black hover:bg-black/[0.02]'"
                class="px-6 py-3 font-inter text-base-spp transition-all cursor-pointer outline-none whitespace-nowrap">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ===== TAB: DASHBOARD ===== --}}
    <div 
        x-show="$store.kbtk.activeTab === 'dashboard'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="space-y-6">

        {{-- Banner dengan Stats Cards --}}
        <div class="bg-cover bg-center rounded-2xl p-8 text-white shadow-[0_4px_20px_rgba(120,0,139,0.15)] flex items-center justify-between gap-6 min-h-[160px] relative overflow-hidden"
             style="background-image: url('{{ asset('images/headline.png') }}');"
            
            {{-- Text Kiri --}}
            <div class="flex flex-col justify-center z-10">
                <span class="text-base mb-1 font-inter">
                    Tata Usaha KB/TK Alfa Omega Ngabang
                </span>
                <h1 class="text-heading-spp font-bold font-inter leading-none">
                    Halo, {{ auth()->user()->name ?? 'Olivia' }}
                </h1>
            </div>

            {{-- Stats Cards Kanan --}}
            <div class="flex items-center gap-4 z-10">
                {{-- Uang Pangkal --}}
                <div class="bg-white rounded-xl p-4 min-w-[180px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Uang Pangkal</span>
                    <span class="text-xl font-bold text-signature block">Rp 999.999.999</span>
                </div>

                {{-- Pendapatan SPP --}}
                <div class="bg-white rounded-xl p-4 min-w-[180px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Pendapatan SPP</span>
                    <span class="text-xl font-bold text-signature block">Rp 17.550.000</span>
                </div>

                {{-- Total Siswa --}}
                <div class="bg-white rounded-xl p-4 min-w-[160px] shadow-sm">
                    <span class="text-xs font-semibold text-black/60 uppercase tracking-wider block mb-1">Total Siswa</span>
                    <span class="text-xl font-bold text-signature block">320 Orang</span>
                </div>
            </div>
        </div>

        {{-- History Transaksi Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border border-shade-white-ii overflow-hidden">
            <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp font-inter flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                History Transaksi Terbaru
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white font-inter text-sm font-semibold uppercase">
                            <th class="px-6 py-3.5">Nama Siswa</th>
                            <th class="px-6 py-3.5">Kelas</th>
                            <th class="px-6 py-3.5">Jenis</th>
                            <th class="px-6 py-3.5">Jumlah</th>
                            <th class="px-6 py-3.5">Tanggal</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-shade-white-i font-inter text-sm text-black">
                        @include('admin.kbtk.partials.transaksi-rows')
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ===== TAB: REKAP UANG PANGKAL ===== --}}
    <div 
        x-show="$store.kbtk.activeTab === 'rekap_pangkal'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="space-y-6" x-cloak>
        {{--
            TODO: partial 'rekap' saat ini menampilkan rekap gabungan.
            Pisahkan jadi rekap khusus Uang Pangkal di sini bila perlu;
            untuk sementara pakai partial yang sama dengan flag $mode.
        --}}
    </div>

    {{-- ===== TAB: REKAP SPP ===== --}}
    <div 
        x-show="$store.kbtk.activeTab === 'rekap_spp'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="space-y-6" x-cloak>
        {{--
            TODO: pisahkan jadi rekap khusus SPP bila perlu; untuk
            sementara pakai partial yang sama dengan flag $mode.
        --}}    </div>

    {{-- ===== TAB: KELOLA UANG PANGKAL ===== --}}
    <div 
        x-show="$store.kbtk.activeTab === 'pangkal'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="space-y-6" x-cloak>
        @include('admin.kbtk.partials.pangkal')
    </div>

    {{-- ===== TAB: KELOLA SPP ===== --}}
    <div 
        x-show="$store.kbtk.activeTab === 'spp'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="space-y-6" x-cloak>

    </div>

</div>
@endsection