@props([
    'active' => 'dashboard',
    'kelasFilter' => null,
])

@php
    $user = auth()->user();

    if ($user?->hasRole('Ketua Yayasan')) {
        $rolePrefix = 'ketuayayasan.sd.';
    } elseif ($user?->hasRole('Staf Yayasan')) {
        $rolePrefix = 'stafyayasan.sd.';
    } else {
        $rolePrefix = 'admin.sd.';
    }

    if ($kelasFilter) {
        $dashRoute  = \Illuminate\Support\Facades\Route::has($rolePrefix . 'dashboard') ? route($rolePrefix . 'dashboard') : route('admin.sd.dashboard');
        $uapRoute   = \Illuminate\Support\Facades\Route::has($rolePrefix . 'kelas.uap') ? route($rolePrefix . 'kelas.uap') : route('admin.sd.kelas.uap');
        $sppRoute   = \Illuminate\Support\Facades\Route::has($rolePrefix . 'kelas.spp') ? route($rolePrefix . 'kelas.spp') : route('admin.sd.kelas.spp');
        $siswaRoute = \Illuminate\Support\Facades\Route::has($rolePrefix . 'kelas.siswa') ? route($rolePrefix . 'kelas.siswa') : route('admin.sd.kelas.siswa');

        $tabs = [
            'dashboard'    => ['label' => 'Dashboard',    'href' => $dashRoute . '?kelas=' . $kelasFilter],
            'uang_pangkal' => ['label' => 'Uang Pangkal', 'href' => $uapRoute . '?kelas=' . $kelasFilter],
            'spp'          => ['label' => 'SPP',          'href' => $sppRoute . '?kelas=' . $kelasFilter],
            'kelola_siswa' => ['label' => 'Kelola Siswa', 'href' => $siswaRoute . '?kelas=' . $kelasFilter],
        ];
    } else {
        $tabs = [
            'dashboard'     => ['label' => 'Dashboard',          'href' => route($rolePrefix . 'dashboard')],
            'rekap_pangkal' => ['label' => 'Rekap Uang Pangkal', 'href' => route($rolePrefix . 'rekap-pangkal')],
            'rekap_spp'     => ['label' => 'Rekap SPP',          'href' => route($rolePrefix . 'rekap-spp')],
        ];

        if ($user?->hasRole('Staf Yayasan')) {
            $tabs['pangkal']    = ['label' => 'Kelola Uang Pangkal', 'href' => route($rolePrefix . 'kelola-pangkal')];
            $tabs['spp_kelola'] = ['label' => 'Kelola SPP',          'href' => route($rolePrefix . 'kelola-spp')];
        }
    }
@endphp

<div class="flex border-b border-shade-white-ii mb-6 overflow-x-auto">
    @foreach ($tabs as $key => $meta)
        @php
            $isActive = ($active === $key);
            $activeClass = $kelasFilter 
                ? 'bg-[#F2F2F2] border-b-2 border-signature text-signature font-bold'
                : 'bg-[#F2F2F2] border-b-2 border-black text-black font-bold';
        @endphp
        <a href="{{ $meta['href'] }}"
           class="px-6 py-3 font-inter text-base-spp transition-all cursor-pointer outline-none whitespace-nowrap {{ $isActive ? $activeClass : 'text-black/60 hover:text-black hover:bg-black/[0.02]' }}">
            {{ $meta['label'] }}
        </a>
    @endforeach
</div>
