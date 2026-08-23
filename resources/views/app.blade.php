@php
    $locale = app()->getLocale();
    $direction = $locale === 'ar' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Avarewase Auth') }}</title>

    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#1B2A4A">

    {{-- iOS: Safari ignores the web manifest's own install/standalone hints --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Avarewase">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/ts/app.tsx'])
    @inertiaHead
</head>
<body>
    <div id="app-splash" class="app-splash">
        <svg width="72" height="72" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="48" fill="#1B2A4A" />
            <rect x="44" y="20" width="12" height="60" fill="#3B82F6" />
            <rect x="20" y="44" width="60" height="12" fill="#3B82F6" />
        </svg>
        <div class="app-splash-spinner"></div>
    </div>

    @inertia
</body>
</html>
