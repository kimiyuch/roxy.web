@php($repository = config('roxy.repository_url'))

<x-layouts.app title="Legal Notice · Roxy">
    <div class="absolute inset-x-0 top-0 -z-10 h-[44rem] bg-linear-to-b from-roxy-100 to-white"></div>

    <x-site-header />

    <main class="mx-auto max-w-6xl px-6 pt-10 pb-24 sm:pt-16 sm:pb-32 lg:px-8">
        <p class="text-base/7 font-semibold text-roxy-600">Legal notice</p>
        <h1 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
            The small print
        </h1>
        <p class="mt-6 max-w-[48ch] text-lg/8 text-pretty text-neutral-600">
            Roxy collects feedback on websites and passes it on to the people, and the agents, who build them. Here’s what it stores, and what it doesn’t.
        </p>

        <div class="mt-16 divide-y divide-neutral-950/5 border-t border-neutral-950/5">
            <section id="feedback" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">Feedback</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        When a reviewer sends feedback, Roxy stores what they wrote, the screenshots, the address of the page,
                        the element they picked, their browser and window size, and the last errors and failed requests on the
                        page. Addresses of failed requests are stored without their query strings, and screenshots are
                        re-encoded, which removes their metadata.
                    </p>
                    <p>
                        Everything is stored on servers in Switzerland. Only the site’s owner and the agents they connect can
                        read it; reviewers of the same site see the open feedback on the page they’re on. Feedback is kept until
                        the owner discards it or deletes the site.
                    </p>
                </div>
            </section>

            <section id="reviewers" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">Reviewers</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        Reviewers are invited by the site’s owner with their email address and, if given, their name. Roxy uses
                        the address only to send sign-in links. After signing in, the browser keeps a token in the site’s local
                        storage, so the widget knows who’s giving feedback. It works until the site’s owner removes them as a reviewer.
                    </p>
                    <p>
                        For everyone who isn’t invited, the widget does nothing: it doesn’t show, doesn’t record anything and
                        doesn’t set cookies.
                    </p>
                </div>
            </section>

            <section id="warranty" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">No warranty</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        Roxy is in early access and provided “as is”, without any warranty. Changes your agent makes based on
                        feedback are yours to review. To the extent the law allows, we’re not liable for any damage or loss from
                        using it.
                    </p>
                </div>
            </section>

            <section id="privacy" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">This website</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        This website has no ads or analytics and loads nothing from other servers. It sets a session cookie that
                        the website framework needs to work, and our host keeps the usual server logs.
                    </p>
                </div>
            </section>

            <section id="trademarks" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">Trademarks</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        Claude and Claude Code are trademarks of Anthropic, PBC. Cursor is a trademark of Anysphere, Inc. Roxy
                        isn’t affiliated with or endorsed by either of them.
                    </p>
                </div>
            </section>

            <section id="contact" class="grid scroll-mt-8 grid-cols-1 gap-x-12 gap-y-4 py-12 sm:grid-cols-[12rem_1fr]">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-roxy-950">Contact</h2>
                <div class="max-w-[65ch] space-y-4 text-base/7 text-pretty text-neutral-600">
                    <p>
                        Roxy is made by <a href="https://kimiyu.ch" class="font-medium text-roxy-800 hover:text-roxy-600">kimiyu.ch</a>.
                        For questions or problems, please
                        <a href="{{ $repository }}/issues" class="font-medium text-roxy-800 hover:text-roxy-600">open an issue on GitHub</a>.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <x-site-footer />
</x-layouts.app>
