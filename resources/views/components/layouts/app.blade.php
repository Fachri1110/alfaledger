<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ALFA LEDGER')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar using Design Tokens */
        ::-webkit-scrollbar             { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track       { background: #F2F2F2; }
        ::-webkit-scrollbar-thumb       { background: #CCCCCC; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #78008B; }
    </style>
</head>
<body class="bg-gradient-app min-h-screen font-inter text-black bg-no-repeat bg-cover bg-fixed">

    <div class="flex flex-col min-h-screen w-full">

        {{-- ===== HEADER ===== --}}
        {{-- Hanya muncul ketika user sudah login --}}
        @auth
            <header class="sticky top-0 z-50 w-full h-20 bg-white border-b border-shade-white-ii shadow-[0_2px_12px_rgba(120,0,139,0.08)] flex items-center shrink-0">
                @hasSection('header')
                    @yield('header')
                @else
                    @include('components.header-general')
                @endif
            </header>
        @endauth

        {{-- ===== MAIN CONTENT AREA ===== --}}
        <main class="flex-grow w-full flex flex-col">
            @yield('content')
        </main>

    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        inter: ['Inter', 'sans-serif'],
                    },
                    fontSize: {
                        'base-spp':       ['1rem',   { lineHeight: '1.6',  fontWeight: '400' }],
                        'subheading-spp': ['1.5rem', { lineHeight: '1.4',  fontWeight: '400' }],
                        'heading-spp':    ['2rem',   { lineHeight: '1.25', fontWeight: '400' }],
                    },
                    colors: {
                        signature: {
                            DEFAULT: '#78008B',
                            10:      'rgba(120, 0, 139, 0.10)',
                        },
                        'shade-white-i':  '#F2F2F2',
                        'shade-white-ii': '#CCCCCC',
                        danger:           '#CA2508',
                    },
                    backgroundImage: {
                        'gradient-app': 'linear-gradient(135deg, #FFFFFF 0%, rgba(120,0,139,0.10) 100%)',
                    },
                },
            },
        };
    </script>
    @stack('scripts')
</body>
</html>