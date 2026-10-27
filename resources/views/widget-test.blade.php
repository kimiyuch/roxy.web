{{-- A stand-in client site for trying the widget locally (route only in local). Needs a site
     whose start URL is this page: php artisan roxy:site you@example.com "Widget test" https://roxy.web.test/widget-test --}}
@php($key = \App\Models\Site::where('name', 'Widget test')->value('key'))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alpenblick · Rooms</title>
    <style>
        body { margin: 0; font: 16px/1.6 Georgia, serif; color: #1c1917; background: #fafaf9; }
        header, main { max-width: 960px; margin: 0 auto; padding: 24px; }
        header { display: flex; justify-content: space-between; align-items: center; }
        nav a { margin-left: 20px; color: #57534e; text-decoration: none; }
        .book { padding: 6px 16px; border-radius: 999px; background: #1c1917; color: #fff !important; }
        .hero { height: 220px; border-radius: 12px; background: linear-gradient(#bae6fd, #fef3c7); }
        .rooms { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .room-card { padding: 16px; border: 1px solid #e7e5e4; border-radius: 10px; background: #fff; }
        .room-card h3 { margin: 0 0 4px; }
        .price { margin-top: 8px; font-weight: bold; }
        p.long { max-width: 60ch; }
        @media (max-width: 640px) { .rooms { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <em style="font-size: 1.5rem">Alpenblick</em>
        <nav><a href="#">Rooms</a><a href="#">Restaurant</a><a href="#">Contact</a><a class="book" href="#">Book now</a></nav>
    </header>
    <main>
        <div class="hero"></div>
        <h1>Rooms with a view</h1>
        <p class="long">Every room looks out over the lake or the garden. Breakfast is included, and the sauna is open until ten.</p>
        <div class="rooms">
            <div class="room-card"><h3>Single</h3>Garden side, 18 m²<div class="price">CHF 140 / night</div></div>
            <div class="room-card"><h3>Double</h3>Balcony, lake view, 26 m²<div class="price">CHF 220 / night</div></div>
            <div class="room-card"><h3>Suite</h3>Corner, two balconies, 48 m²<div class="price">CHF 380 / night</div></div>
        </div>
        @foreach (range(1, 6) as $i)
            <p class="long">More about the hotel, paragraph {{ $i }}, so the page scrolls. The lake is cold in June and perfect in August.</p>
        @endforeach
    </main>

    {{-- Something for Roxy to catch: a console error and a failed request --}}
    <script>
        setTimeout(() => {
            console.error('Map failed to initialise: missing API key');
            fetch('/widget-test/missing.json?token=secret').catch(() => {});
        }, 500);
    </script>
    <script src="{{ url('widget.js') }}" data-site="{{ $key }}" defer></script>
</body>
</html>
