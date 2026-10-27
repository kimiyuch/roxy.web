{{-- A snippet to paste somewhere else: wraps instead of scrolling, with a copy button (script in the layout). --}}
<div {{ $attributes->class('relative') }}>
    <pre class="rounded-lg bg-roxy-950 py-4 pr-14 pl-4 font-mono text-xs/5 whitespace-pre-wrap text-roxy-100 [overflow-wrap:anywhere]"><code>{!! trim($slot) !!}</code></pre>
    <button type="button" data-copy aria-label="Copy" class="absolute top-2.5 right-2.5 grid size-8 place-items-center rounded-md text-roxy-300 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-roxy-300">
        <x-heroicon-m-clipboard-document class="size-4 in-data-copied:hidden" />
        <x-heroicon-m-check class="hidden size-4 text-emerald-400 in-data-copied:block" />
    </button>
</div>
