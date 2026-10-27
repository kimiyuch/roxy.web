{{-- A mock of a Claude Code session working through Roxy feedback over MCP. --}}
<div {{ $attributes->class('overflow-hidden rounded-xl bg-[#11161f] shadow-2xl ring-1 ring-white/10') }} aria-hidden="true">
    <div class="flex items-center gap-x-2 border-b border-white/5 px-4 py-3">
        <div class="size-3 rounded-full bg-white/15"></div>
        <div class="size-3 rounded-full bg-white/15"></div>
        <div class="size-3 rounded-full bg-white/15"></div>
        <span class="ml-3 font-mono text-xs/5 text-white/40">~/Sites/alpenblick.web — claude</span>
    </div>

    <div class="space-y-3 overflow-x-auto p-5 font-mono text-xs/5 text-white/80 *:whitespace-pre sm:text-[0.8125rem]/5">
        <div><span class="text-white/40">&gt;</span> <span class="text-white">Fix the open Roxy feedback for Alpenblick</span></div>

        <div><span class="text-roxy-300">⏺</span> <span class="font-semibold text-white">roxy - list_feedback</span> <span class="text-white/50">(site: "Alpenblick", status: "open")</span>
<span class="text-white/40">  ⎿</span>  3 open: /rooms “The suite still shows last year’s price…”
      /rooms “Book now is cut off on my phone…”
      /contact “The map on the contact page stays grey.”</div>

        <div><span class="text-roxy-300">⏺</span> <span class="font-semibold text-white">roxy - get_feedback</span> <span class="text-white/50">(id: "01jb7r…")</span>
<span class="text-white/40">  ⎿</span>  [Image] 1440×900 · div.room-card:nth-child(3) · no errors</div>

        <div><span class="text-white/60">⏺</span> The prices live in the rooms collection. Updating the suite.</div>

        <div><span class="text-emerald-400">⏺</span> <span class="font-semibold text-white">Update</span><span class="text-white/50">(content/collections/rooms/suite.md)</span>
<span class="text-white/40">  ⎿</span>  <span class="bg-red-500/15 text-red-300">- price: 380</span>
     <span class="bg-emerald-500/15 text-emerald-300">+ price: 410</span></div>

        <div><span class="text-roxy-300">⏺</span> <span class="font-semibold text-white">roxy - resolve_feedback</span> <span class="text-white/50">(note: "The suite is CHF 410 now.")</span>
<span class="text-white/40">  ⎿</span>  Marked as done.</div>

        <div><span class="text-white/60">⏺</span> Next: the Book now button on small screens.<span class="ml-0.5 inline-block h-4 w-2 translate-y-0.5 bg-white/70 motion-safe:animate-pulse"></span></div>
    </div>
</div>
