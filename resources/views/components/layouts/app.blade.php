<!DOCTYPE html>
<html lang="en" class="bg-white antialiased motion-safe:scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Roxy: client feedback your coding agent can fix' }}</title>
    <meta name="description" content="Roxy is a feedback widget for websites. Your client points at the problem, Roxy adds a screenshot and the page details, and your coding agent reads it over MCP, fixes it and marks it done.">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Roxy">
    <meta property="og:title" content="Roxy: client feedback your coding agent can fix">
    <meta property="og:description" content="Your client points at the problem. Claude reads it over MCP, with the screenshot, the element and the console, fixes it and marks it done.">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="The Roxy app icon next to the words: Tell Roxy. Claude fixes it.">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Structured data: what Roxy is, and who makes it (kimiyu.ch lists all the apps the same way) --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Roxy',
            'url' => url('/'),
            'image' => asset('images/og.png'),
            'description' => 'A feedback widget for websites. Clients point at what’s wrong; Roxy adds a screenshot, the element, the page and the console errors, and coding agents read it over MCP, fix it and mark it done.',
            'applicationCategory' => 'DeveloperApplication',
            'operatingSystem' => 'Web',
            'publisher' => ['@type' => 'Organization', 'name' => 'kimiyu studio', 'url' => 'https://kimiyu.ch/'],
            'sameAs' => [config('roxy.repository_url')],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @fonts

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans text-neutral-950">
    <div class="isolate">
        {{ $slot }}
    </div>

    {{-- Copy buttons on code blocks. The fallback is for http (Herd), where there's no Clipboard API. --}}
    <script>
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-copy]');
            if (! button) return;
            const text = button.parentElement.querySelector('code').innerText;
            try {
                await navigator.clipboard.writeText(text);
            } catch {
                const field = Object.assign(document.createElement('textarea'), { value: text });
                document.body.append(field);
                field.select();
                document.execCommand('copy');
                field.remove();
            }
            button.dataset.copied = '';
            setTimeout(() => delete button.dataset.copied, 1500);
        });
    </script>
</body>
</html>
