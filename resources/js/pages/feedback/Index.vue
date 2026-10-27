<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeftIcon, CameraIcon, InboxIcon } from '@heroicons/vue/20/solid';
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import FeedbackDetail from '../../components/FeedbackDetail.vue';
import { timeAgo } from '../../utils.js';

const props = defineProps({ feedback: Object, counts: Object, selected: Object, filters: Object });

const page = usePage();
const item = computed(() => props.selected?.data ?? props.selected);
const site = computed(() => page.props.sites?.find((site) => site.id === props.filters.site));

// The filters ride along in every link, so opening an entry keeps the list as it is.
const query = computed(() => {
    const params = new URLSearchParams();
    if (props.filters.site) params.set('site', props.filters.site);
    if (props.filters.status !== 'open') params.set('status', props.filters.status);
    return params.size ? `?${params}` : '';
});

function filter(changes) {
    router.get('/app/feedback', { site: props.filters.site, ...changes, status: changes.status ?? props.filters.status }, { preserveScroll: true, replace: true });
}

function short(date) {
    return timeAgo(date)
        .replace(/ minutes? ago/, ' min')
        .replace(/ hours? ago/, ' h')
        .replace(/ days? ago/, ' d')
        .replace(/^yesterday$/, 'Yesterday');
}
</script>

<template>
    <Head :title="site?.name ?? 'Feedback'" />
    <AppLayout>
        <div class="flex h-full">
            <!-- List -->
            <section class="flex min-w-0 flex-1 flex-col lg:max-w-md lg:border-r lg:border-roxy-950/5" :class="{ 'max-lg:hidden': item }">
                <div class="flex items-center justify-between gap-x-3 border-b border-roxy-950/5 px-4 py-3">
                    <h1 class="truncate font-display text-base font-semibold text-roxy-950">{{ site?.name ?? 'Feedback' }}</h1>
                    <div class="flex shrink-0 rounded-md bg-roxy-50 p-0.5 text-xs/5 font-medium ring-1 ring-roxy-950/5">
                        <button
                            v-for="status in ['open', 'done']"
                            :key="status"
                            type="button"
                            class="rounded px-2 py-0.5 capitalize"
                            :class="filters.status === status ? 'bg-white text-roxy-950 shadow-xs' : 'text-neutral-500 hover:text-roxy-950'"
                            @click="filter({ status })"
                        >
                            {{ status }} <span class="tabular-nums">{{ counts[status] ?? 0 }}</span>
                        </button>
                    </div>
                </div>

                <!-- Sites on small screens, where there's no sidebar -->
                <div v-if="$page.props.sites?.length > 1" class="border-b border-roxy-950/5 px-4 py-2 md:hidden">
                    <select
                        :value="filters.site ?? ''"
                        class="w-full rounded-md border-0 py-1 pr-8 pl-2 text-sm ring-1 ring-neutral-950/10"
                        @change="filter({ site: $event.target.value || null })"
                    >
                        <option value="">All sites</option>
                        <option v-for="option in $page.props.sites" :key="option.id" :value="option.id">{{ option.name }}</option>
                    </select>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <ul v-if="feedback.data.length" class="divide-y divide-roxy-950/5">
                        <li v-for="entry in feedback.data" :key="entry.id">
                            <Link
                                :href="`/app/feedback/${entry.id}${query}`"
                                preserve-scroll
                                preserve-state
                                class="relative block px-4 py-3 text-sm/5"
                                :class="entry.id === item?.id ? 'bg-roxy-50/70' : 'hover:bg-neutral-50'"
                            >
                                <span v-if="entry.id === item?.id" class="absolute inset-y-0 left-0 w-0.5 bg-roxy-500" />
                                <div class="flex items-center gap-x-2 text-xs/5 text-neutral-500">
                                    <span class="truncate font-medium text-neutral-700">{{ entry.reviewer?.name || entry.reviewer?.email || 'Removed reviewer' }}</span>
                                    <span>·</span>
                                    <span class="truncate">{{ filters.site ? entry.path : `${entry.site?.name} ${entry.path}` }}</span>
                                    <span class="ml-auto shrink-0">{{ short(entry.created_at) }}</span>
                                </div>
                                <p class="mt-1 line-clamp-2 text-neutral-900">{{ entry.body }}</p>
                                <div class="mt-2 flex flex-wrap gap-1.5 text-xs/5 empty:hidden">
                                    <span v-if="entry.screenshot_count" class="inline-flex items-center gap-x-1 rounded px-1.5 text-neutral-600 ring-1 ring-neutral-950/10">
                                        <CameraIcon class="size-3.5 fill-neutral-400" /> {{ entry.screenshot_count }}
                                    </span>
                                    <span v-if="entry.element?.selector" class="max-w-48 truncate rounded px-1.5 font-mono text-neutral-600 ring-1 ring-neutral-950/10">
                                        {{ entry.element.selector.split(' ').pop() }}
                                    </span>
                                    <span v-if="entry.console?.length" class="rounded bg-red-50 px-1.5 font-medium text-red-700 ring-1 ring-red-600/15">
                                        {{ entry.console.length }} {{ entry.console.length === 1 ? 'error' : 'errors' }}
                                    </span>
                                    <span v-if="entry.network?.length" class="rounded bg-red-50 px-1.5 font-medium text-red-700 ring-1 ring-red-600/15">
                                        {{ entry.network.length }} failed
                                    </span>
                                </div>
                            </Link>
                        </li>
                    </ul>
                    <div v-else class="px-6 py-16 text-center">
                        <InboxIcon class="mx-auto size-6 fill-roxy-300" />
                        <p class="mt-2 text-sm text-neutral-500">{{ filters.status === 'open' ? 'Nothing open. Nice.' : 'Nothing done yet.' }}</p>
                    </div>

                    <div v-if="feedback.meta?.last_page > 1" class="flex justify-between border-t border-roxy-950/5 px-4 py-3 text-sm">
                        <Link v-if="feedback.links.prev" :href="feedback.links.prev" class="text-roxy-700">← Newer</Link>
                        <span v-else />
                        <Link v-if="feedback.links.next" :href="feedback.links.next" class="text-roxy-700">Older →</Link>
                    </div>
                </div>
            </section>

            <!-- Detail -->
            <section class="min-w-0 flex-1 flex-col" :class="item ? 'flex' : 'hidden lg:flex'">
                <template v-if="item">
                    <Link :href="`/app/feedback${query}`" class="flex items-center gap-x-1.5 border-b border-roxy-950/5 px-4 py-3 text-sm text-neutral-600 lg:hidden">
                        <ArrowLeftIcon class="size-4" />
                        {{ site?.name ?? 'Feedback' }}
                    </Link>
                    <FeedbackDetail :key="item.id" :item="item" :query="query" class="min-h-0 flex-1" />
                </template>
                <div v-else class="grid flex-1 place-items-center bg-gray-50">
                    <div class="text-center">
                        <img src="/images/roxy.webp" alt="" class="mx-auto size-16 opacity-60 grayscale-50" />
                        <p class="mt-3 text-sm text-neutral-500">Pick an entry to see it here.</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
