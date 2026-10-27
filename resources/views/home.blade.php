<!DOCTYPE html>
<html lang="en" class="h-full bg-roxy-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roxy: client feedback your coding agent can fix</title>
    <meta name="description" content="A feedback widget for websites. Your client points at it, Claude fixes it.">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    @fonts

    @vite(['resources/css/app.css'])
</head>
<body class="flex h-full items-center justify-center font-sans text-neutral-950">
    <main class="px-6 text-center">
        <img src="{{ asset('images/icon.png') }}" alt="" width="160" height="160" class="mx-auto size-40">
        <h1 class="mt-6 text-4xl font-semibold tracking-tight">Tell Roxy. Claude fixes it.</h1>
        <p class="mt-3 text-lg text-neutral-600">Client feedback your coding agent can read and act on. Coming soon.</p>
        <p class="mt-8"><a href="{{ route('dashboard') }}" class="text-sm font-medium text-roxy-700 hover:text-roxy-900">Sign in →</a></p>
    </main>
</body>
</html>
