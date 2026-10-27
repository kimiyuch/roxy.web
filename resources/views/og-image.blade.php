{{-- Source for public/images/og.png (1200×630). Regenerate with scripts/og-image.sh. --}}
<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="m-0">
    <div class="relative isolate flex h-[630px] w-[1200px] items-center gap-x-16 overflow-hidden bg-linear-to-br from-roxy-100 via-white to-galah-100 px-24 font-sans">
        <img src="{{ asset('images/roxy.png') }}" alt="" class="size-[22rem] shrink-0">
        <div>
            <div class="font-display text-[7rem] font-semibold tracking-tight text-roxy-950">Roxy</div>
            <p class="mt-2 max-w-[20ch] font-display text-5xl font-medium tracking-tight text-balance text-roxy-800">
                Tell Roxy. Claude fixes it.
            </p>
            <p class="mt-8 text-2xl text-neutral-600">Client feedback your coding agent can fix.</p>
            <p class="mt-2 text-2xl font-medium text-galah-600">roxy.kimiyu.ch</p>
        </div>
    </div>
</body>
</html>
