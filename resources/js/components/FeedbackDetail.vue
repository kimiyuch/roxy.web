<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ArrowTopRightOnSquareIcon, ArrowUturnLeftIcon, CheckIcon, TrashIcon } from '@heroicons/vue/20/solid';
import { computed } from 'vue';
import { browserName, timeAgo } from '../utils.js';

const props = defineProps({ item: Object, query: String });

const form = useForm({ resolved: true, note: '' });
const reviewer = computed(() => props.item.reviewer?.name || props.item.reviewer?.email || 'Removed reviewer');
const first = computed(() => props.item.screenshots?.[0]);
const more = computed(() => props.item.screenshots?.slice(1) ?? []);

// Where the picked element sits on the automatic screenshot, which shows the viewport as the reviewer saw it.
const outline = computed(() => {
    const rect = props.item.element?.rect;
    const viewport = props.item.context?.viewport;
    if (!first.value || first.value.kind !== 'auto' || !rect || !viewport?.width || !viewport?.height) return null;

    return {
        left: `${(rect.x / viewport.width) * 100}%`,
        top: `${(rect.y / viewport.height) * 100}%`,
        width: `${(rect.width / viewport.width) * 100}%`,
        height: `${(rect.height / viewport.height) * 100}%`,
    };
});

const browser = computed(() => {
    const context = props.item.context ?? {};
    return [
        browserName(context.user_agent),
        context.viewport && `${context.viewport.width} × ${context.viewport.height}`,
        context.dpr && context.dpr !== 1 && `${context.dpr}×`,
    ]
        .filter(Boolean)
        .join(' · ');
});

const errors = computed(() => props.item.console?.length ?? 0);
const failed = computed(() => props.item.network?.length ?? 0);

function resolve(resolved) {
    form.resolved = resolved;
    form.post(`/app/feedback/${props.item.id}/resolve`, { preserveScroll: true, onSuccess: () => form.reset('note') });
}

function discard() {
    if (confirm('Delete this feedback and its screenshots for good?')) {
        router.delete(`/app/feedback/${props.item.id}${props.query}`);
    }
}
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-6">
            <div class="flex items-center gap-x-2 text-xs/5 text-neutral-500">
                <span
                    class="rounded-full px-2 font-medium"
                    :class="item.status === 'open' ? 'bg-galah-100 text-galah-600' : 'bg-emerald-50 text-emerald-700'"
                >
                    {{ item.status === 'open' ? 'Open' : 'Done' }}
                </span>
                <span class="truncate">{{ reviewer }} · {{ timeAgo(item.created_at) }}</span>
                <div class="ml-auto flex shrink-0 items-center gap-x-1">
                    <a :href="item.url" target="_blank" rel="noopener" title="Open the page" class="rounded-md p-1.5 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950">
                        <ArrowTopRightOnSquareIcon class="size-4" />
                        <span class="sr-only">Open the page</span>
                    </a>
                    <button type="button" title="Discard" class="rounded-md p-1.5 text-neutral-400 hover:bg-red-50 hover:text-red-600" @click="discard">
                        <TrashIcon class="size-4" />
                        <span class="sr-only">Discard</span>
                    </button>
                </div>
            </div>

            <p class="mt-3 text-base/6 text-pretty whitespace-pre-line text-neutral-950">{{ item.body }}</p>

            <!-- Screenshots: the first one big, with the picked element outlined -->
            <a v-if="first" :href="first.url" target="_blank" rel="noopener" class="relative mt-4 block overflow-hidden rounded-lg ring-1 ring-neutral-950/10">
                <img :src="first.url" :width="first.width" :height="first.height" alt="Screenshot" class="h-auto w-full" />
                <span v-if="outline" class="absolute rounded-xs outline-2 outline-offset-2 outline-pin-500 outline-dashed" :style="outline" />
            </a>
            <div v-if="more.length" class="mt-2 flex flex-wrap gap-2">
                <a v-for="shot in more" :key="shot.id" :href="shot.url" target="_blank" rel="noopener" class="block overflow-hidden rounded-md ring-1 ring-neutral-950/10">
                    <img :src="shot.url" alt="Screenshot" class="h-16 w-auto" />
                </a>
            </div>

            <dl class="mt-5 grid grid-cols-[6rem_1fr] gap-x-3 gap-y-1.5 text-xs/5">
                <dt class="text-neutral-500">Site</dt>
                <dd class="truncate text-neutral-800">
                    {{ item.site?.name }}<span v-if="item.site?.repository" class="text-neutral-400"> · {{ item.site.repository }}</span>
                </dd>
                <dt class="text-neutral-500">Page</dt>
                <dd class="truncate">
                    <a :href="item.url" target="_blank" rel="noopener" class="text-neutral-800 hover:text-roxy-700">{{ item.url.replace(/^https?:\/\//, '') }}</a>
                </dd>
                <template v-if="item.element">
                    <dt class="text-neutral-500">Element</dt>
                    <dd class="font-mono break-all text-neutral-800">{{ item.element.selector }}</dd>
                </template>
                <template v-if="item.element?.text">
                    <dt class="text-neutral-500">Text</dt>
                    <dd class="truncate text-neutral-800">“{{ item.element.text }}”</dd>
                </template>
                <template v-if="browser">
                    <dt class="text-neutral-500">Browser</dt>
                    <dd class="truncate text-neutral-800" :title="item.context?.user_agent">{{ browser }}</dd>
                </template>
                <dt class="text-neutral-500">Console</dt>
                <dd :class="errors ? 'font-medium text-red-700' : 'text-neutral-800'">{{ errors ? `${errors} ${errors === 1 ? 'error' : 'errors'}` : 'No errors' }}</dd>
                <dt class="text-neutral-500">Requests</dt>
                <dd :class="failed ? 'font-medium text-red-700' : 'text-neutral-800'">{{ failed ? `${failed} failed` : 'None failed' }}</dd>
            </dl>

            <!-- The raw details, for when the summary isn't enough -->
            <div class="mt-5 space-y-2">
                <details v-if="errors" class="rounded-lg ring-1 ring-neutral-950/10">
                    <summary class="cursor-pointer px-3 py-2 text-xs/5 font-medium text-neutral-700 select-none">Console errors</summary>
                    <ul class="space-y-2 border-t border-neutral-950/5 px-3 py-2 font-mono text-xs/5">
                        <li v-for="(entry, i) in item.console" :key="i" class="break-all">
                            <span class="text-red-700">{{ entry.message }}</span>
                            <span v-if="entry.source" class="block text-neutral-500">{{ entry.source }}</span>
                        </li>
                    </ul>
                </details>
                <details v-if="failed" class="rounded-lg ring-1 ring-neutral-950/10">
                    <summary class="cursor-pointer px-3 py-2 text-xs/5 font-medium text-neutral-700 select-none">Failed requests</summary>
                    <ul class="space-y-1 border-t border-neutral-950/5 px-3 py-2 font-mono text-xs/5">
                        <li v-for="(entry, i) in item.network" :key="i" class="break-all">
                            <span class="font-medium text-red-700">{{ entry.status || entry.error || 'failed' }}</span> {{ entry.method }} {{ entry.url }}
                        </li>
                    </ul>
                </details>
                <details v-if="item.element?.html" class="rounded-lg ring-1 ring-neutral-950/10">
                    <summary class="cursor-pointer px-3 py-2 text-xs/5 font-medium text-neutral-700 select-none">Element HTML</summary>
                    <pre class="overflow-x-auto border-t border-neutral-950/5 px-3 py-2 font-mono text-xs/5 text-neutral-800">{{ item.element.html }}</pre>
                </details>
            </div>
        </div>

        <!-- Resolve -->
        <div class="border-t border-roxy-950/5 bg-white px-5 py-4 sm:px-6">
            <form v-if="item.status === 'open'" class="flex items-center gap-x-2" @submit.prevent="resolve(true)">
                <input
                    v-model="form.note"
                    type="text"
                    :placeholder="`Note for ${item.reviewer?.name?.split(' ')[0] || 'the reviewer'} (optional)`"
                    class="min-w-0 flex-1 rounded-md border-0 px-3 py-1.5 text-sm/6 ring-1 ring-neutral-950/10 placeholder:text-neutral-400 focus:ring-2 focus:ring-roxy-500 focus:outline-none"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex shrink-0 items-center gap-x-1.5 rounded-md bg-roxy-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-roxy-700 disabled:opacity-50"
                >
                    <CheckIcon class="size-4" />
                    Done
                </button>
            </form>
            <div v-else class="flex items-start gap-x-3">
                <div class="min-w-0 flex-1 text-sm/6">
                    <p class="text-neutral-500">Done<template v-if="item.resolved_by"> by {{ item.resolved_by }}</template> · {{ timeAgo(item.resolved_at) }}</p>
                    <p v-if="item.resolution_note" class="whitespace-pre-line text-neutral-800">{{ item.resolution_note }}</p>
                </div>
                <button
                    type="button"
                    :disabled="form.processing"
                    class="inline-flex shrink-0 items-center gap-x-1.5 rounded-md px-3 py-1.5 text-sm/6 font-semibold text-roxy-800 ring-1 ring-roxy-950/10 hover:bg-roxy-50 disabled:opacity-50"
                    @click="resolve(false)"
                >
                    <ArrowUturnLeftIcon class="size-4" />
                    Reopen
                </button>
            </div>
        </div>
    </div>
</template>
