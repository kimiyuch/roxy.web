<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import { timeAgo } from '../../utils.js';

const props = defineProps({ feedback: Object, sites: Array, filters: Object });

const statuses = [
    { value: 'open', label: 'Open' },
    { value: 'done', label: 'Done' },
    { value: 'all', label: 'All' },
];

function filter(changes) {
    router.get('/app/feedback', { ...props.filters, ...changes }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Feedback" />
    <AppLayout>
        <div class="flex flex-wrap items-center gap-4">
            <h1 class="text-2xl font-semibold">Feedback</h1>
            <div class="ml-auto flex items-center gap-3">
                <select
                    :value="filters.site ?? ''"
                    class="rounded-lg border-0 py-1.5 pr-8 pl-3 text-sm ring-1 ring-neutral-300"
                    @change="filter({ site: $event.target.value || null })"
                >
                    <option value="">All sites</option>
                    <option v-for="site in sites" :key="site.id" :value="site.id">{{ site.name }}</option>
                </select>
                <div class="flex rounded-lg bg-neutral-200/60 p-0.5 text-sm">
                    <button
                        v-for="status in statuses"
                        :key="status.value"
                        type="button"
                        class="rounded-md px-3 py-1"
                        :class="filters.status === status.value ? 'bg-white font-medium shadow-sm' : 'text-neutral-600'"
                        @click="filter({ status: status.value })"
                    >
                        {{ status.label }}
                    </button>
                </div>
            </div>
        </div>

        <ul v-if="feedback.data.length" class="mt-6 divide-y divide-neutral-200 overflow-hidden rounded-xl bg-white ring-1 ring-black/5">
            <li v-for="item in feedback.data" :key="item.id">
                <Link :href="`/app/feedback/${item.id}`" class="flex gap-4 px-5 py-4 hover:bg-neutral-50">
                    <span
                        class="mt-1.5 size-2 shrink-0 rounded-full"
                        :class="item.status === 'open' ? 'bg-pin-500' : 'bg-neutral-300'"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-2 text-sm">{{ item.body }}</p>
                        <p class="mt-1 truncate text-xs text-neutral-500">
                            {{ item.site?.name }} · {{ item.path }}
                            <template v-if="item.element?.selector"> · <code>{{ item.element.selector }}</code></template>
                        </p>
                    </div>
                    <div class="shrink-0 text-right text-xs text-neutral-500">
                        <p>{{ item.reviewer?.name || item.reviewer?.email }}</p>
                        <p class="mt-1">{{ timeAgo(item.created_at) }}<template v-if="item.screenshot_count"> · {{ item.screenshot_count }} 📷</template></p>
                    </div>
                </Link>
            </li>
        </ul>
        <p v-else class="mt-16 text-center text-sm text-neutral-500">Nothing here.</p>

        <div v-if="feedback.meta?.last_page > 1" class="mt-6 flex justify-between text-sm">
            <Link v-if="feedback.links.prev" :href="feedback.links.prev" class="text-roxy-700">← Newer</Link>
            <span v-else />
            <Link v-if="feedback.links.next" :href="feedback.links.next" class="text-roxy-700">Older →</Link>
        </div>
    </AppLayout>
</template>
