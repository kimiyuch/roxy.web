@php
    // A made-up inbox: three sites, four open entries, the first one selected.
    $sites = [
        ['name' => 'Alpenblick', 'open' => 3, 'dot' => 'bg-galah-400'],
        ['name' => 'Bäckerei Huber', 'open' => 1, 'dot' => 'bg-amber-400'],
        ['name' => 'Croc', 'open' => 0, 'dot' => 'bg-emerald-500'],
    ];
    $entries = [
        ['body' => 'The suite still shows last year’s price. It should be CHF 410 from January.', 'site' => 'Alpenblick', 'path' => '/rooms', 'who' => 'Anna Keller', 'when' => '2 min', 'element' => 'div.room-card', 'errors' => 0, 'selected' => true],
        ['body' => 'Book now is cut off on my phone, I can only see “Boo”.', 'site' => 'Alpenblick', 'path' => '/rooms', 'who' => 'Marco Bianchi', 'when' => '1 h', 'element' => 'a.btn-book', 'errors' => 0, 'selected' => false],
        ['body' => 'The map on the contact page stays grey.', 'site' => 'Alpenblick', 'path' => '/contact', 'who' => 'Anna Keller', 'when' => '3 h', 'element' => 'div#map', 'errors' => 2, 'selected' => false],
        ['body' => 'Can the opening hours be bigger? Our customers keep calling.', 'site' => 'Bäckerei Huber', 'path' => '/', 'who' => 'Ruth Huber', 'when' => 'Yesterday', 'element' => null, 'errors' => 0, 'selected' => false],
    ];
@endphp

{{-- A mock of the Roxy dashboard: sites on the left, the open feedback in the middle, one entry on the right. --}}
<div {{ $attributes->class('overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-roxy-950/10') }} aria-hidden="true">
    <div class="flex text-xs/5 sm:text-sm/5">
        {{-- Sidebar --}}
        <div class="w-52 shrink-0 border-r border-roxy-950/5 bg-roxy-50 px-3 py-4 max-md:hidden">
            <div class="flex items-center gap-x-2 px-2">
                <img src="{{ asset('images/roxy.webp') }}" alt="" class="size-7">
                <span class="font-display text-base font-semibold text-roxy-950">Roxy</span>
            </div>

            <div class="mt-6 space-y-0.5">
                <div class="flex items-center gap-x-2 rounded-md bg-white px-2 py-1.5 font-medium text-roxy-950 shadow-xs ring-1 ring-roxy-950/5">
                    <x-heroicon-m-inbox class="size-4 shrink-0 fill-roxy-500" />
                    Feedback
                    <span class="ml-auto text-xs text-roxy-600 tabular-nums">4</span>
                </div>
                <div class="flex items-center gap-x-2 px-2 py-1.5 text-neutral-600">
                    <x-heroicon-m-globe-alt class="size-4 shrink-0 fill-neutral-400" />
                    Sites
                </div>
                <div class="flex items-center gap-x-2 px-2 py-1.5 text-neutral-600">
                    <x-heroicon-m-user-group class="size-4 shrink-0 fill-neutral-400" />
                    Reviewers
                </div>
                <div class="flex items-center gap-x-2 px-2 py-1.5 text-neutral-600">
                    <x-heroicon-m-key class="size-4 shrink-0 fill-neutral-400" />
                    Agent tokens
                </div>
            </div>

            <div class="mt-6 px-2 text-xs/5 font-medium text-neutral-500">Sites</div>
            <div class="mt-1 space-y-0.5">
                @foreach ($sites as $site)
                    <div class="flex items-center gap-x-2.5 px-2 py-1.5 text-neutral-700">
                        <span class="size-2 shrink-0 rounded-full {{ $site['dot'] }}"></span>
                        {{ $site['name'] }}
                        <span class="ml-auto text-xs text-neutral-400 tabular-nums">{{ $site['open'] ?: '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- List --}}
        <div class="min-w-0 flex-1 lg:max-w-sm lg:border-r lg:border-roxy-950/5">
            <div class="flex items-center justify-between gap-x-3 border-b border-roxy-950/5 px-4 py-3">
                <span class="font-display text-base font-semibold text-roxy-950">Feedback</span>
                <div class="flex rounded-md bg-roxy-50 p-0.5 text-xs/5 font-medium ring-1 ring-roxy-950/5">
                    <span class="rounded bg-white px-2 py-0.5 text-roxy-950 shadow-xs">Open 4</span>
                    <span class="px-2 py-0.5 text-neutral-500">Done 27</span>
                </div>
            </div>

            <div class="divide-y divide-roxy-950/5">
                @foreach ($entries as $entry)
                    <div @class([
                        'relative px-4 py-3',
                        'bg-roxy-50/70' => $entry['selected'],
                    ])>
                        @if ($entry['selected'])
                            <div class="absolute inset-y-0 left-0 w-0.5 bg-roxy-500"></div>
                        @endif
                        <div class="flex items-center gap-x-2 text-xs/5 text-neutral-500">
                            <span class="font-medium text-neutral-700">{{ $entry['who'] }}</span>
                            <span>·</span>
                            <span class="truncate">{{ $entry['site'] }} {{ $entry['path'] }}</span>
                            <span class="ml-auto shrink-0">{{ $entry['when'] }}</span>
                        </div>
                        <p class="mt-1 line-clamp-2 text-neutral-900">{{ $entry['body'] }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5 text-xs/5">
                            <span class="inline-flex items-center gap-x-1 rounded px-1.5 text-neutral-600 ring-1 ring-neutral-950/10">
                                <x-heroicon-m-camera class="size-3.5 fill-neutral-400" /> 1
                            </span>
                            @if ($entry['element'])
                                <span class="rounded px-1.5 font-mono text-neutral-600 ring-1 ring-neutral-950/10">{{ $entry['element'] }}</span>
                            @endif
                            @if ($entry['errors'])
                                <span class="rounded bg-red-50 px-1.5 font-medium text-red-700 ring-1 ring-red-600/15">{{ $entry['errors'] }} errors</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Detail --}}
        <div class="min-w-0 flex-1 p-5 max-lg:hidden">
            <div class="flex items-center gap-x-2 text-xs/5 text-neutral-500">
                <span class="rounded-full bg-galah-100 px-2 font-medium text-galah-600">Open</span>
                Anna Keller · 2 minutes ago
            </div>
            <p class="mt-3 text-base/6 text-pretty text-neutral-950">The suite still shows last year’s price. It should be CHF 410 from January.</p>

            {{-- The screenshot, with the picked element --}}
            <div class="relative mt-4 aspect-16/9 overflow-hidden rounded-lg bg-linear-to-b from-sky-200 via-stone-50 to-stone-100 ring-1 ring-neutral-950/10">
                <div class="absolute inset-x-[6%] top-[8%] h-[3%] rounded-full bg-stone-300/70"></div>
                <div class="absolute top-[18%] left-[6%] h-[30%] w-[88%] rounded bg-sky-100/80"></div>
                <div class="absolute bottom-[12%] left-[6%] grid h-[24%] w-[88%] grid-cols-3 gap-[3%]">
                    <div class="rounded bg-white ring-1 ring-stone-900/10"></div>
                    <div class="rounded bg-white ring-1 ring-stone-900/10"></div>
                    <div class="rounded bg-white ring-1 ring-stone-900/10 outline-2 outline-offset-2 outline-pin-500 outline-dashed"></div>
                </div>
            </div>

            <dl class="mt-4 grid grid-cols-[6rem_1fr] gap-x-3 gap-y-1.5 text-xs/5">
                <dt class="text-neutral-500">Page</dt>
                <dd class="truncate text-neutral-800">staging.alpenblick.ch/rooms</dd>
                <dt class="text-neutral-500">Element</dt>
                <dd class="truncate font-mono text-neutral-800">main div.room-card:nth-child(3)</dd>
                <dt class="text-neutral-500">Browser</dt>
                <dd class="truncate text-neutral-800">Safari 19 · 1440 × 900 · 2×</dd>
                <dt class="text-neutral-500">Console</dt>
                <dd class="text-neutral-800">No errors</dd>
            </dl>

            <div class="mt-5 flex items-center gap-x-2">
                <div class="min-w-0 flex-1 rounded-md px-3 py-1.5 text-neutral-400 ring-1 ring-neutral-950/10">Note for Anna (optional)</div>
                <span class="inline-flex shrink-0 items-center gap-x-1.5 rounded-md bg-roxy-600 px-3 py-1.5 font-semibold text-white shadow-sm">
                    <x-heroicon-m-check class="size-4 fill-white" />
                    Done
                </span>
            </div>
        </div>
    </div>
</div>
