@extends('components.layouts.app')

@section('content')

{{-- Halaman Login — ALFA LEDGER --}}
<div class="flex flex-col justify-center items-center min-h-screen p-4">

    <div class="w-full max-w-[420px] flex flex-col items-center">

        {{-- ===== LOGO ===== --}}
        <div class="mb-4 flex justify-center">
            <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="h-20 w-auto object-contain">
        </div>

        {{-- ===== SUBTITLE ===== --}}
        <p class="mb-6 text-center font-medium text-heading-spp text-black">
            Selamat datang...
        </p>

        {{-- ===== CARD LOGIN ===== --}}
        <div class="w-full bg-white border border-shade-white-ii rounded-[12px] p-8 shadow-[0_4px_24px_rgba(120,0,139,0.07)]">

            {{-- Error: validasi --}}
            @if ($errors->any())
                <div class="mb-2 px-4 py-3 rounded-lg text-sm font-medium bg-danger/[.07] border border-danger/25 text-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Error: session --}}
            @if (session('error'))
                <div class="mb-2 px-4 py-3 rounded-lg text-sm font-medium
                            bg-danger/[.07] border border-danger/25 text-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Form Login --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="email" class="block mb-2 text-sm font-normal text-black">
                        Username
                    </label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="{{ old('username') }}"
                        placeholder="username@sekolah.sch.id"
                        required
                        autofocus
                        class="w-full px-4 py-3 rounded-[8px] outline-none transition-all font-inter text-base-spp text-black bg-white border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block mb-2 text-sm font-normal text-black">
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="••••••••••••••••"
                        required
                        class="w-full px-4 py-3 rounded-[8px] outline-none transition-all font-inter text-base-spp text-black bg-white border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10"
                    >
                </div>

                {{-- Tombol Masuk --}}
                <button
                    type="submit"
                    id="btn-login"
                    class="w-full flex items-center justify-center gap-2 py-3 rounded-[8px] font-inter font-bold text-base-spp text-white bg-signature hover:opacity-[0.88] hover:-translate-y-px transition-all cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                    <span>Masuk</span>
                </button>

            </form>
        </div>{{-- /card --}}

        {{-- Hint --}}
        <p class="mt-6 text-center text-base-spp font-normal text-black">
            Kesulitan masuk? Hubungi pihak yayasan anda
        </p>

    </div>
</div>

@endsection
