<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d1a30">
    <meta name="csp-nonce" content="{{ $cspNonce }}">

    {{-- Terapkan tema sebelum paint (cegah flash) --}}
    <script nonce="{{ $cspNonce }}">
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @php($seo = config('company.seo', []))
    @php($seoTitle = $seo['title'] ?? 'Konsultan Halal & Legalitas Usaha di Malang | PT Zam Zam Khan')
    @php($seoDesc = $seo['description'] ?? 'PT Zam Zam Khan — konsultan halal dan legalitas usaha di Malang. Pendampingan sertifikat halal, NIB, akta pendirian, NPWP, BPOM, HAKI, dan desain label kemasan.')
    @php($seoKeywords = $seo['keywords'] ?? 'konsultan halal Malang, jasa sertifikat halal Malang, konsultan legalitas usaha Malang, jasa BPOM Malang, jasa NIB Malang, jasa HAKI Malang, jasa desain label kemasan Malang')
    @php($seoImage = $seo['og_image'] ?? asset('images/logo-zzk.png'))
    @php($seoCanonical = $seo['canonical'] ?? url('/'))

    <title>@yield('title', $seoTitle)</title>
    <meta name="description" content="@yield('description', $seoDesc)">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="@yield('canonical', $seoCanonical)">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('ogType', 'website')">
    <meta property="og:site_name" content="{{ config('company.name') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="@yield('ogTitle', $seo['og_title'] ?? $seoTitle)">
    <meta property="og:description" content="@yield('ogDescription', $seo['og_description'] ?? $seoDesc)">
    <meta property="og:image" content="@yield('ogImage', $seoImage)">
    <meta property="og:url" content="@yield('ogUrl', url('/'))">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('ogTitle', $seo['og_title'] ?? $seoTitle)">
    <meta name="twitter:description" content="@yield('ogDescription', $seo['og_description'] ?? $seoDesc)">
    <meta name="twitter:image" content="@yield('ogImage', $seoImage)">

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    @hasSection('jsonld')
        @yield('jsonld')
    @else
        @include('partials.seo-jsonld')
    @endif
    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans @yield('bodyClass')">
    <a href="#main-content" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold text-navy-900 shadow-lg focus:fixed focus:left-4 focus:top-4 focus:not-sr-only">
        Lewati ke konten utama
    </a>
    @include('partials.navbar')

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    @include('partials.footer')
    @if (filled(config('company.whatsapp_number')))
        @include('partials.whatsapp-lead-form')
        @include('partials.whatsapp-float')
    @endif
</body>
</html>
