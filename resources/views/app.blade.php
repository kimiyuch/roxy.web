<!DOCTYPE html>
<html lang="en" class="h-full bg-roxy-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="description" content="Roxy: client feedback your coding agent can fix. Sign in to your feedback inbox.">

    {{-- So a shared link to the login or the dashboard still shows Roxy in chats --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Roxy">
    <meta property="og:title" content="Roxy: client feedback your coding agent can fix">
    <meta property="og:description" content="Sign in to see what your clients told Roxy.">
    <meta property="og:image" content="{{ asset('images/og.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full font-sans text-neutral-950">
    @inertia
</body>
</html>
