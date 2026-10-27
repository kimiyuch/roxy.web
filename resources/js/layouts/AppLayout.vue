<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRightStartOnRectangleIcon, InboxIcon } from '@heroicons/vue/20/solid';
import { computed } from 'vue';
import { siteDot } from '../utils.js';

const page = usePage();
const user = computed(() => page.props.auth.user);
const sites = computed(() => page.props.sites ?? []);
const open = computed(() => sites.value.reduce((sum, site) => sum + site.open_count, 0));
const currentSite = computed(() => new URLSearchParams(page.url.split('?')[1] ?? '').get('site'));

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex h-dvh flex-col bg-white md:flex-row">
        <!-- Sidebar -->
        <aside class="flex w-60 shrink-0 flex-col border-r border-roxy-950/5 bg-roxy-50 px-3 py-4 max-md:hidden">
            <Link href="/app/feedback" class="flex items-center gap-x-2 px-2">
                <img src="/images/roxy.webp" alt="" class="size-7" />
                <span class="font-display text-base font-semibold text-roxy-950">Roxy</span>
            </Link>

            <nav class="mt-6 space-y-0.5 text-sm/5">
                <Link
                    href="/app/feedback"
                    class="flex items-center gap-x-2 rounded-md px-2 py-1.5"
                    :class="page.url.startsWith('/app/feedback') && !currentSite ? 'bg-white font-medium text-roxy-950 shadow-xs ring-1 ring-roxy-950/5' : 'text-neutral-600 hover:text-roxy-950'"
                >
                    <InboxIcon class="size-4 shrink-0 fill-roxy-500" />
                    Feedback
                    <span v-if="open" class="ml-auto text-xs text-roxy-600 tabular-nums">{{ open }}</span>
                </Link>
            </nav>

            <template v-if="sites.length">
                <div class="mt-6 px-2 text-xs/5 font-medium text-neutral-500">Sites</div>
                <nav class="mt-1 space-y-0.5 text-sm/5">
                    <Link
                        v-for="site in sites"
                        :key="site.id"
                        :href="`/app/feedback?site=${site.id}`"
                        class="flex items-center gap-x-2.5 rounded-md px-2 py-1.5"
                        :class="currentSite === site.id ? 'bg-white font-medium text-roxy-950 shadow-xs ring-1 ring-roxy-950/5' : 'text-neutral-700 hover:text-roxy-950'"
                    >
                        <span class="size-2 shrink-0 rounded-full" :class="siteDot(site.id)" />
                        <span class="truncate">{{ site.name }}</span>
                        <span class="ml-auto text-xs text-neutral-400 tabular-nums">{{ site.open_count || '' }}</span>
                    </Link>
                </nav>
            </template>

            <div class="mt-auto flex items-center gap-x-2 px-2 text-sm/5">
                <span class="min-w-0 flex-1 truncate text-neutral-600">{{ user?.name }}</span>
                <button type="button" title="Sign out" class="rounded-md p-1 text-neutral-400 hover:bg-white hover:text-roxy-950" @click="logout">
                    <ArrowRightStartOnRectangleIcon class="size-4" />
                    <span class="sr-only">Sign out</span>
                </button>
            </div>
        </aside>

        <!-- Top bar on small screens -->
        <header class="flex items-center gap-x-3 border-b border-roxy-950/5 bg-roxy-50 px-4 py-3 md:hidden">
            <Link href="/app/feedback" class="flex items-center gap-x-2">
                <img src="/images/roxy.webp" alt="" class="size-7" />
                <span class="font-display text-base font-semibold text-roxy-950">Roxy</span>
            </Link>
            <button type="button" class="ml-auto text-sm text-neutral-600" @click="logout">Sign out</button>
        </header>

        <main class="min-h-0 min-w-0 flex-1">
            <slot />
        </main>
    </div>
</template>
