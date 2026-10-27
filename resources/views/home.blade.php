@php
    $repository = config('roxy.repository_url');

    $steps = [
        ['title' => 'Your client points at it', 'body' => 'On the staging site, they press Roxy’s button, click the thing that’s wrong and write a sentence. No account, no screenshot tool, no email with “see attached”.'],
        ['title' => 'Roxy fills in the rest', 'body' => 'A screenshot of the page, the element they clicked, the address, the browser and window size, and any errors in the console. Everything you’d otherwise ask for.'],
        ['title' => 'Claude fixes it', 'body' => 'Your coding agent reads the feedback over MCP, finds the code, makes the change and marks it done, with a short note on what it did.'],
    ];

    $tools = [
        ['name' => 'list_sites', 'body' => 'Your sites, with the repository each one lives in.'],
        ['name' => 'list_feedback', 'body' => 'What’s open, per site, with the page and the element.'],
        ['name' => 'get_feedback', 'body' => 'One entry in full, with the screenshots as images.'],
        ['name' => 'resolve_feedback', 'body' => 'Mark it done, with a note on what changed.'],
        ['name' => 'discard_feedback', 'body' => 'Throw away what isn’t feedback.'],
    ];

    $features = [
        ['icon' => 'cursor-arrow-rays', 'title' => 'Point at the problem', 'body' => 'Clients click the element they mean. Roxy keeps its selector, its text and a bit of its HTML, so nobody has to guess which button.'],
        ['icon' => 'camera', 'title' => 'Screenshots included', 'body' => 'Roxy takes a screenshot of the page as the client sees it. They can add their own images too: pick, drop or paste.'],
        ['icon' => 'command-line', 'title' => 'Console and network', 'body' => 'The last errors and failed requests since the page loaded come along, so “the map stays grey” arrives with its cause.'],
        ['icon' => 'envelope', 'title' => 'No passwords', 'body' => 'You invite reviewers by email. The link signs them in on the site itself, and the browser remembers them.'],
        ['icon' => 'eye-slash', 'title' => 'Invisible to visitors', 'body' => 'Only invited reviewers see the widget. Everyone else gets nothing, not even a button.'],
        ['icon' => 'chat-bubble-left-right', 'title' => 'No double reports', 'body' => 'Reviewers see what’s already open on the page they’re on, so the same thing isn’t reported three times.'],
        ['icon' => 'puzzle-piece', 'title' => 'Any website', 'body' => 'One script tag, on Statamic, Laravel, WordPress or plain HTML. Roxy lives in its own Shadow DOM, so your CSS and its CSS never meet.'],
        ['icon' => 'inbox', 'title' => 'One inbox', 'body' => 'All sites in one place, open and done, with an email when something new comes in. For when you’d rather fix it yourself.'],
    ];

    $origin = url('/');
@endphp

<x-layouts.app>
    <div class="absolute inset-x-0 top-0 -z-10 h-[44rem] bg-linear-to-b from-roxy-100 to-white"></div>

    <x-site-header />

    <main>
        {{-- Hero --}}
        <section class="relative pt-10 pb-24 sm:pt-16 sm:pb-32">
            <div class="mx-auto max-w-6xl px-6 text-center lg:px-8">
                <img src="{{ asset('images/roxy.webp') }}" width="384" height="384" fetchpriority="high" alt="The Roxy app icon: a galah peeking over a browser window with a feedback pin" class="mx-auto size-32 sm:size-40">
                <h1 class="mx-auto mt-8 font-display text-5xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-7xl">
                    <span class="block">Tell Roxy.</span>
                    <span class="block">Claude fixes it.</span>
                </h1>
                <p class="mx-auto mt-6 max-w-[50ch] text-lg/8 text-pretty text-neutral-600 sm:text-xl/8">
                    Roxy is a feedback button for the websites you build. Your client points at what’s wrong and writes a sentence. Roxy adds a screenshot and everything about the page, and your coding agent reads it, fixes it and marks it done.
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-4">
                    <a href="#how" class="inline-flex items-center gap-x-2 rounded-full bg-roxy-600 px-5 py-3 text-base/6 font-semibold text-white shadow-sm hover:bg-roxy-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-roxy-600">
                        See how it works
                    </a>
                    <a href="{{ $repository }}" class="inline-flex items-center gap-x-1.5 text-base/6 font-semibold text-roxy-800 hover:text-roxy-600">
                        View on GitHub
                        <x-heroicon-m-arrow-right class="size-5 shrink-0 fill-current" />
                    </a>
                </div>
                <p class="mt-6 text-base/7 text-neutral-500 sm:text-sm/6">Early access · Any website · Claude Code, Cursor and other MCP clients</p>

                <x-site-window class="mx-auto mt-16 max-w-5xl text-left sm:mt-20" />
            </div>
        </section>

        {{-- How it works --}}
        <section id="how" class="scroll-mt-8 pb-24 sm:pb-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <p class="text-base/7 font-semibold text-roxy-600">How it works</p>
                <h2 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
                    From “something’s off” to fixed
                </h2>
                <p class="mt-6 max-w-[52ch] text-lg/8 text-pretty text-neutral-600">
                    Client feedback usually arrives as an email with a blurry phone photo, and half the work is finding out what they mean. Roxy sends it with everything a developer, or an agent, needs to act on it.
                </p>

                <ol role="list" class="mt-16 grid grid-cols-1 gap-x-12 gap-y-12 lg:grid-cols-3">
                    @foreach ($steps as $step)
                        <li>
                            <div class="font-display text-base/7 font-semibold text-roxy-600 tabular-nums">0{{ $loop->iteration }}</div>
                            <h3 class="mt-2 text-lg/7 font-semibold text-roxy-950">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-base/7 text-pretty text-neutral-600">{{ $step['body'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- For agents --}}
        <section id="agents" class="scroll-mt-8 bg-roxy-950 py-24 sm:py-32">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-x-16 gap-y-16 px-6 lg:grid-cols-[2fr_3fr] lg:px-8">
                <div>
                    <p class="text-base/7 font-semibold text-roxy-300">For agents</p>
                    <h2 class="mt-2 max-w-[20ch] font-display text-4xl font-semibold tracking-tight text-balance text-white sm:text-5xl">
                        Feedback your agent can read
                    </h2>
                    <p class="mt-6 max-w-[48ch] text-lg/8 text-pretty text-roxy-100/70">
                        Roxy is an MCP server. Add it to Claude Code once, then say “fix the open feedback”. Your agent gets the words, the screenshots, the element and the errors, and knows which repository the site lives in.
                    </p>

                    <dl class="mt-10 space-y-4">
                        @foreach ($tools as $tool)
                            <div>
                                <dt class="font-mono text-sm/6 font-medium text-white">{{ $tool['name'] }}</dt>
                                <dd class="text-base/7 text-pretty text-roxy-100/70">{{ $tool['body'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <x-agent-terminal class="self-center" />
            </div>
        </section>

        {{-- Dashboard --}}
        <section class="py-24 sm:py-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <p class="text-base/7 font-semibold text-roxy-600">Dashboard</p>
                <h2 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
                    Every site, one inbox
                </h2>
                <p class="mt-6 max-w-[52ch] text-lg/8 text-pretty text-neutral-600">
                    Rather fix it yourself, or check what your agent did? Everything that comes in waits in the dashboard, sorted by site, open or done, with the screenshot and the details next to it.
                </p>

                <x-dashboard-window class="mt-16 sm:mt-20" />
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="scroll-mt-8 pb-24 sm:pb-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <p class="text-base/7 font-semibold text-roxy-600">Features</p>
                <h2 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
                    Made for client reviews
                </h2>
                <p class="mt-6 max-w-[48ch] text-lg/8 text-pretty text-neutral-600">
                    Your clients aren’t developers and shouldn’t have to be. Roxy asks them for one thing, what’s wrong, and works out the rest.
                </p>

                <dl class="mt-16 grid grid-cols-1 gap-x-12 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($features as $feature)
                        <div>
                            <dt class="flex items-start gap-x-3 text-base/7 font-semibold text-roxy-950">
                                <x-dynamic-component :component="'heroicon-m-'.$feature['icon']" class="size-5 h-lh shrink-0 fill-galah-600" />
                                {{ $feature['title'] }}
                            </dt>
                            <dd class="mt-1 pl-8 text-base/7 text-pretty text-neutral-600">{{ $feature['body'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- Under the hood --}}
        <section class="bg-roxy-50 py-24 sm:py-32">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-x-12 gap-y-16 px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p class="text-base/7 font-semibold text-roxy-600">Under the hood</p>
                    <h2 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
                        Light on the site, at home in Switzerland
                    </h2>
                    <p class="mt-6 max-w-[48ch] text-lg/8 text-pretty text-neutral-600">
                        The widget loads only for invited reviewers and stays out of everyone else’s way. Feedback and screenshots are stored on servers in Switzerland, and screenshots are only ever shown through links that expire after a few minutes.
                    </p>
                </div>
                <dl class="divide-y divide-roxy-950/10 self-center">
                    <div class="flex items-baseline justify-between gap-x-6 pb-6">
                        <dt class="text-base/7 text-neutral-600">Where your data lives</dt>
                        <dd class="font-display text-2xl font-semibold tracking-tight text-roxy-950">Switzerland</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-x-6 py-6">
                        <dt class="text-base/7 text-neutral-600">Tracking on your client’s site</dt>
                        <dd class="font-display text-2xl font-semibold tracking-tight text-roxy-950">None</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-x-6 py-6">
                        <dt class="text-base/7 text-neutral-600">Passwords for reviewers</dt>
                        <dd class="font-display text-2xl font-semibold tracking-tight text-roxy-950">None</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-x-6 py-6">
                        <dt class="text-base/7 text-neutral-600">Works with</dt>
                        <dd class="text-right font-display text-2xl font-semibold tracking-tight text-roxy-950">Any MCP client</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-x-6 pt-6">
                        <dt class="text-base/7 text-neutral-600">Source code</dt>
                        <dd class="font-display text-2xl font-semibold tracking-tight">
                            <a href="{{ $repository }}" class="text-roxy-700 underline decoration-roxy-300 underline-offset-4 hover:decoration-roxy-600">GitHub</a>
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- Setup --}}
        <section id="setup" class="scroll-mt-8 py-24 sm:py-32">
            <div class="mx-auto max-w-6xl px-6 lg:px-8">
                <p class="text-base/7 font-semibold text-roxy-600">Setup</p>
                <h2 class="mt-2 max-w-[30ch] font-display text-4xl font-semibold tracking-tight text-balance text-roxy-950 sm:text-5xl">
                    Let Roxy onto the site
                </h2>
                <p class="mt-6 max-w-[48ch] text-lg/8 text-pretty text-neutral-600">
                    Five minutes per site, and the agent only needs connecting once.
                </p>

                <ol role="list" class="mt-16 grid grid-cols-1 gap-x-12 gap-y-12 sm:grid-cols-2">
                    <li>
                        <div class="font-display text-base/7 font-semibold text-roxy-600 tabular-nums">01</div>
                        <h3 class="mt-2 text-base/7 font-semibold text-roxy-950">Add the site</h3>
                        <p class="mt-1 text-base/7 text-pretty text-neutral-600">
                            Give it a name, the addresses it runs on (staging, production or both) and the GitHub repository, so your agent knows where the code is.
                        </p>
                    </li>
                    <li>
                        <div class="font-display text-base/7 font-semibold text-roxy-600 tabular-nums">02</div>
                        <h3 class="mt-2 text-base/7 font-semibold text-roxy-950">Paste the script tag</h3>
                        <p class="mt-1 text-base/7 text-pretty text-neutral-600">Put it before <code class="font-mono text-sm text-roxy-900">&lt;/body&gt;</code>. Visitors who aren’t invited don’t see a thing.</p>
                        <x-code-block class="mt-4">&lt;script src="{{ $origin }}/widget.js" data-site="7f3k9q…" defer&gt;&lt;/script&gt;</x-code-block>
                    </li>
                    <li>
                        <div class="font-display text-base/7 font-semibold text-roxy-600 tabular-nums">03</div>
                        <h3 class="mt-2 text-base/7 font-semibold text-roxy-950">Invite your client</h3>
                        <p class="mt-1 text-base/7 text-pretty text-neutral-600">
                            Enter their email address. They get a link that opens the site with Roxy ready to go, and their browser remembers them from then on.
                        </p>
                    </li>
                    <li>
                        <div class="font-display text-base/7 font-semibold text-roxy-600 tabular-nums">04</div>
                        <h3 class="mt-2 text-base/7 font-semibold text-roxy-950">Connect your agent</h3>
                        <p class="mt-1 text-base/7 text-pretty text-neutral-600">Create a token in the dashboard and add Roxy to Claude Code. One token covers all your sites.</p>
                        <x-code-block class="mt-4">claude mcp add --transport http roxy {{ $origin }}/mcp --header "Authorization: Bearer …"</x-code-block>
                    </li>
                </ol>
            </div>
        </section>
    </main>

    <x-site-footer />
</x-layouts.app>
