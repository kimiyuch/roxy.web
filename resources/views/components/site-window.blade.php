@php
    // A made-up client site (a hotel on staging) with the Roxy widget open: the client picked
    // the suite's price card and is about to send. The price cards are the page; the panel is Roxy.
    $rooms = [
        ['name' => 'Single', 'detail' => 'Garden side, 18 m²', 'price' => '140', 'picked' => false],
        ['name' => 'Double', 'detail' => 'Balcony, lake view, 26 m²', 'price' => '220', 'picked' => false],
        ['name' => 'Suite', 'detail' => 'Corner, two balconies, 48 m²', 'price' => '380', 'picked' => true],
    ];
@endphp

{{-- A mock of a client's website in a browser, with the Roxy feedback panel open. --}}
<div {{ $attributes->class('overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-roxy-950/10') }} aria-hidden="true">
    {{-- Browser bar --}}
    <div class="flex items-center gap-x-4 border-b border-neutral-950/5 bg-neutral-100 px-4 py-3">
        <div class="flex gap-x-2">
            <div class="size-3 rounded-full bg-[#ff5f57]"></div>
            <div class="size-3 rounded-full bg-[#febc2e]"></div>
            <div class="size-3 rounded-full bg-[#28c840]"></div>
        </div>
        <div class="mx-auto flex w-full max-w-sm items-center justify-center gap-x-1.5 rounded-md bg-white px-3 py-1 text-xs/5 text-neutral-500 ring-1 ring-neutral-950/5">
            <x-heroicon-m-lock-closed class="size-3.5 shrink-0 fill-neutral-400" />
            staging.alpenblick.ch/rooms
        </div>
        <div class="w-13 max-sm:hidden"></div>
    </div>

    <div class="relative h-[44rem] overflow-hidden text-xs/5 md:h-[34rem] sm:text-sm/5">
        {{-- The client's site --}}
        <div class="px-5 py-4 sm:px-8 sm:py-5">
            <div class="flex items-center justify-between">
                <span class="font-serif text-lg text-stone-900 italic">Alpenblick</span>
                <div class="flex items-center gap-x-5 text-stone-600">
                    <span class="max-sm:hidden">Rooms</span>
                    <span class="max-sm:hidden">Restaurant</span>
                    <span class="max-sm:hidden">Contact</span>
                    <span class="rounded-full bg-stone-900 px-3 py-1 text-white">Book now</span>
                </div>
            </div>

            <div class="mt-5 flex h-36 items-end overflow-hidden rounded-lg bg-linear-to-b from-sky-200 via-sky-100 to-amber-50 max-md:hidden">
                <svg viewBox="0 0 400 80" preserveAspectRatio="none" class="h-2/3 w-full">
                    <path d="M0 80 L0 50 L60 18 L105 46 L160 6 L215 44 L260 22 L320 52 L360 34 L400 48 L400 80 Z" class="fill-stone-400/60" />
                    <path d="M0 80 L0 62 L80 40 L150 64 L230 36 L300 60 L400 44 L400 80 Z" class="fill-stone-500/70" />
                </svg>
            </div>

            {{-- Kept left of the panel, so the picked card stays in view --}}
            <div class="md:mr-[23rem]">
                <h3 class="mt-5 font-serif text-2xl text-stone-900 sm:text-3xl">Rooms with a view</h3>
                <p class="mt-1 max-w-[46ch] text-stone-600 max-md:hidden">Every room looks out over the lake or the garden. Breakfast is included.</p>

                <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-3">
                    @foreach ($rooms as $room)
                        <div @class([
                            'relative rounded-lg bg-stone-50 p-3 ring-1 ring-stone-900/10',
                            'max-lg:hidden' => $room['name'] === 'Single',
                            'outline-2 outline-offset-4 outline-pin-500 outline-dashed' => $room['picked'],
                        ])>
                            @if ($room['picked'])
                                {{-- The pin Roxy puts on the element the client picked --}}
                                <svg viewBox="0 0 24 24" class="absolute -top-8 left-1/2 size-8 -translate-x-1/2 drop-shadow">
                                    <path d="M12 2C7.6 2 4 5.4 4 9.7 4 15 12 22 12 22s8-7 8-12.3C20 5.4 16.4 2 12 2Z" class="fill-pin-600" />
                                    <circle cx="12" cy="9.7" r="3" class="fill-white" />
                                </svg>
                            @endif
                            <div class="font-medium text-stone-900">{{ $room['name'] }}</div>
                            <div class="text-stone-500">{{ $room['detail'] }}</div>
                            <div class="mt-2 text-stone-900">CHF {{ $room['price'] }} <span class="text-stone-500">/ night</span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Roxy's button --}}
        <div class="absolute right-4 bottom-4 grid size-12 place-items-center rounded-full bg-galah-600 shadow-lg ring-4 ring-white md:right-6 md:bottom-6">
            <x-heroicon-m-x-mark class="size-6 fill-white" />
        </div>

        {{-- Roxy's panel --}}
        <div class="absolute inset-x-3 bottom-20 rounded-xl bg-white shadow-2xl ring-1 ring-neutral-950/10 md:inset-x-auto md:right-6 md:bottom-22 md:w-88">
            <div class="flex items-center gap-x-2.5 border-b border-neutral-950/5 px-4 py-3">
                <img src="{{ asset('images/roxy.webp') }}" alt="" class="size-7">
                <div class="min-w-0">
                    <div class="font-semibold text-neutral-950">Feedback on this page</div>
                    <div class="truncate text-xs/4 text-neutral-500">Signed in as Anna Keller</div>
                </div>
            </div>

            <div class="space-y-3 p-4">
                <div class="flex items-center gap-x-2 rounded-md bg-roxy-50 px-2.5 py-1.5 text-xs/5 text-roxy-800">
                    <x-heroicon-m-chat-bubble-left-right class="size-4 shrink-0 fill-roxy-500" />
                    <span class="truncate">1 open here: “Book now is cut off on my phone”</span>
                </div>

                <div class="min-h-20 rounded-md px-3 py-2 text-neutral-800 ring-1 ring-roxy-500 outline-3 outline-roxy-100">
                    The suite still shows last year’s price. It should be CHF 410 from January.<span class="ml-px inline-block h-4 w-px translate-y-0.5 bg-neutral-800 motion-safe:animate-pulse"></span>
                </div>

                <div class="flex gap-x-2">
                    <div class="relative h-14 w-20 shrink-0 overflow-hidden rounded-md bg-linear-to-b from-sky-200 via-stone-50 to-stone-100 ring-1 ring-neutral-950/10">
                        <div class="absolute right-2 bottom-2 h-4 w-5 rounded-xs outline-1 outline-pin-500 outline-dashed"></div>
                    </div>
                    <div class="min-w-0 space-y-1 text-xs/5">
                        <div class="flex items-center gap-x-1.5 text-neutral-700">
                            <x-heroicon-m-camera class="size-4 shrink-0 fill-neutral-400" />
                            Screenshot of the page
                        </div>
                        <div class="flex items-center gap-x-1.5 text-neutral-700">
                            <x-heroicon-m-cursor-arrow-rays class="size-4 shrink-0 fill-pin-500" />
                            <span class="truncate font-mono">div.room-card:nth-child(3)</span>
                        </div>
                        <div class="flex items-center gap-x-1.5 text-neutral-500">
                            <x-heroicon-m-information-circle class="size-4 shrink-0 fill-neutral-400" />
                            Page, browser and errors added
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-x-3 border-t border-neutral-950/5 px-4 py-3">
                <span class="inline-flex items-center gap-x-1.5 font-medium text-neutral-600">
                    <x-heroicon-m-photo class="size-4 shrink-0 fill-neutral-400" />
                    Add image
                </span>
                <span class="rounded-full bg-galah-600 px-4 py-1.5 font-semibold text-white shadow-sm">Send</span>
            </div>
        </div>
    </div>
</div>
