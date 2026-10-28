<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRightStartOnRectangleIcon, CheckCircleIcon, GlobeAltIcon, InboxIcon, XMarkIcon } from '@heroicons/vue/20/solid';
import { computed, onUnmounted, ref } from 'vue';
import { siteDot } from '../utils.js';

const page = usePage();
const user = computed(() => page.props.auth.user);
const sites = computed(() => page.props.sites ?? []);
const open = computed(() => sites.value.reduce((sum, site) => sum + site.open_count, 0));
const currentSite = computed(() => new URLSearchParams(page.url.split('?')[1] ?? '').get('site'));

const section = computed(() => (page.url.startsWith('/app/sites') ? 'sites' : 'feedback'));

// "Saved.", "Invitation sent to …": shown for a few seconds after a redirect
const toast = ref(null);
let timer;
function showToast(status) {
    if (!status) return;
    toast.value = status;
    clearTimeout(timer);
    timer = setTimeout(() => (toast.value = null), 4000);
}
showToast(page.props.flash?.status);
onUnmounted(router.on('success', (event) => showToast(event.detail.page.props.flash?.status)));

function logout() {
    router.post('/logout');
}
</script>

<template>
    <!-- Left-aligned on the sidebar's blue; the sidebar sits on the background, the content is white -->
    <div class="flex h-dvh max-w-[100rem] flex-col md:flex-row">
        <!-- Sidebar -->
        <aside class="flex w-60 shrink-0 flex-col bg-roxy-50 px-3 py-4 max-md:hidden">
            <Link href="/app/feedback" class="flex items-center gap-x-2 px-2">
                <img src="/images/roxy.webp" alt="" class="size-7" />
                <span class="font-display text-base font-semibold text-roxy-950">Roxy</span>
            </Link>

            <nav class="mt-6 space-y-0.5 text-sm/5">
                <Link
                    href="/app/feedback"
                    class="flex items-center gap-x-2 rounded-md px-2 py-1.5"
                    :class="section === 'feedback' && !currentSite ? 'bg-white font-medium text-roxy-950 shadow-xs ring-1 ring-roxy-950/5' : 'text-neutral-600 hover:text-roxy-950'"
                >
                    <InboxIcon class="size-4 shrink-0" :class="section === 'feedback' && !currentSite ? 'fill-roxy-500' : 'fill-neutral-400'" />
                    Feedback
                    <span v-if="open" class="ml-auto text-xs text-roxy-600 tabular-nums">{{ open }}</span>
                </Link>
                <Link
                    href="/app/sites"
                    class="flex items-center gap-x-2 rounded-md px-2 py-1.5"
                    :class="section === 'sites' ? 'bg-white font-medium text-roxy-950 shadow-xs ring-1 ring-roxy-950/5' : 'text-neutral-600 hover:text-roxy-950'"
                >
                    <GlobeAltIcon class="size-4 shrink-0" :class="section === 'sites' ? 'fill-roxy-500' : 'fill-neutral-400'" />
                    Sites
                </Link>
            </nav>

            <template v-if="sites.length && section === 'feedback'">
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
            <nav class="ml-auto flex items-center gap-x-4 text-sm text-neutral-600">
                <Link href="/app/feedback" :class="{ 'font-medium text-roxy-950': section === 'feedback' }">Feedback</Link>
                <Link href="/app/sites" :class="{ 'font-medium text-roxy-950': section === 'sites' }">Sites</Link>
                <button type="button" @click="logout">Sign out</button>
            </nav>
        </header>

        <main class="min-h-0 min-w-0 flex-1 bg-white md:border-x md:border-roxy-950/10">
            <slot />
        </main>

        <Transition
            enter-from-class="translate-y-2 opacity-0"
            enter-active-class="transition duration-200 ease-out"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toast"
                role="status"
                class="fixed right-4 bottom-4 z-50 flex max-w-sm items-start gap-x-2.5 rounded-lg bg-roxy-950 px-4 py-3 text-sm/5 text-white shadow-lg"
            >
                <CheckCircleIcon class="mt-px size-4 shrink-0 fill-emerald-400" />
                <span class="flex-1">{{ toast }}</span>
                <button type="button" class="text-white/50 hover:text-white" aria-label="Dismiss" @click="toast = null">
                    <XMarkIcon class="size-4" />
                </button>
            </div>
        </Transition>
    </div>
</template>
