<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import { timeAgo } from '../../utils.js';

const props = defineProps({ feedback: Object });
const item = computed(() => props.feedback.data ?? props.feedback);

const form = useForm({ resolved: true, note: '' });

function resolve(resolved) {
    form.resolved = resolved;
    form.post(`/app/feedback/${item.value.id}/resolve`, { preserveScroll: true, onSuccess: () => form.reset('note') });
}

function discard() {
    if (confirm('Delete this feedback and its screenshots for good?')) {
        router.delete(`/app/feedback/${item.value.id}`);
    }
}

const context = computed(() => {
    const c = item.value.context ?? {};
    return [
        ['Page title', c.title],
        ['Viewport', c.viewport && `${c.viewport.width} × ${c.viewport.height} @${c.dpr ?? 1}x`],
        ['Scroll', c.scroll && `${c.scroll.x}, ${c.scroll.y}`],
        ['Language', c.language],
        ['Browser', c.user_agent],
    ].filter(([, value]) => value);
});
</script>

<template>
    <Head title="Feedback" />
    <AppLayout>
        <Link href="/app/feedback" class="text-sm text-neutral-600 hover:text-neutral-950">← Feedback</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[1fr_20rem]">
            <div class="min-w-0 space-y-6">
                <section class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <p class="text-xs text-neutral-500">
                        {{ item.reviewer?.name || item.reviewer?.email || 'Removed reviewer' }} · {{ timeAgo(item.created_at) }} · {{ item.site?.name }}
                    </p>
                    <p class="mt-3 whitespace-pre-line">{{ item.body }}</p>
                    <a :href="item.url" target="_blank" rel="noopener" class="mt-4 block truncate text-sm text-roxy-700 hover:underline">{{ item.url }}</a>
                </section>

                <section v-for="shot in item.screenshots" :key="shot.id" class="overflow-hidden rounded-xl bg-white ring-1 ring-black/5">
                    <a :href="shot.url" target="_blank" rel="noopener">
                        <img :src="shot.url" :width="shot.width" :height="shot.height" alt="Screenshot" class="h-auto w-full" />
                    </a>
                    <p class="px-4 py-2 text-xs text-neutral-500">{{ shot.kind === 'auto' ? 'Viewport, captured automatically' : 'Added by the reviewer' }} · {{ shot.width }} × {{ shot.height }}</p>
                </section>

                <section v-if="item.element" class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <h2 class="text-sm font-semibold">Element</h2>
                    <code class="mt-2 block text-sm break-all text-galah-600">{{ item.element.selector }}</code>
                    <p v-if="item.element.text" class="mt-2 text-sm text-neutral-600">“{{ item.element.text }}”</p>
                    <pre v-if="item.element.html" class="mt-3 overflow-x-auto rounded-lg bg-neutral-950 p-3 text-xs text-neutral-100">{{ item.element.html }}</pre>
                </section>

                <section v-if="item.console?.length" class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <h2 class="text-sm font-semibold">Console errors</h2>
                    <ul class="mt-2 space-y-2 font-mono text-xs">
                        <li v-for="(entry, i) in item.console" :key="i" class="break-all">
                            <span class="text-red-600">{{ entry.message }}</span>
                            <span v-if="entry.source" class="block text-neutral-500">{{ entry.source }}</span>
                        </li>
                    </ul>
                </section>

                <section v-if="item.network?.length" class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <h2 class="text-sm font-semibold">Failed requests</h2>
                    <ul class="mt-2 space-y-1 font-mono text-xs">
                        <li v-for="(entry, i) in item.network" :key="i" class="break-all">
                            <span class="text-red-600">{{ entry.status || entry.error || 'failed' }}</span> {{ entry.method }} {{ entry.url }}
                        </li>
                    </ul>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <template v-if="item.status === 'open'">
                        <label for="note" class="block text-sm font-medium">Note for the reviewer (optional)</label>
                        <textarea id="note" v-model="form.note" rows="3" class="mt-1 block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-neutral-300 focus:ring-2 focus:ring-roxy-500 focus:outline-none" />
                        <PrimaryButton class="mt-3 w-full" :disabled="form.processing" @click="resolve(true)">Mark as done</PrimaryButton>
                    </template>
                    <template v-else>
                        <p class="text-sm font-medium text-green-700">Done</p>
                        <p class="mt-1 text-xs text-neutral-500">{{ item.resolved_by }} · {{ timeAgo(item.resolved_at) }}</p>
                        <p v-if="item.resolution_note" class="mt-3 text-sm whitespace-pre-line">{{ item.resolution_note }}</p>
                        <button type="button" class="mt-4 text-sm text-roxy-700 hover:underline" :disabled="form.processing" @click="resolve(false)">Reopen</button>
                    </template>
                </section>

                <section v-if="context.length" class="rounded-xl bg-white p-6 ring-1 ring-black/5">
                    <dl class="space-y-3 text-sm">
                        <div v-for="[label, value] in context" :key="label">
                            <dt class="text-xs text-neutral-500">{{ label }}</dt>
                            <dd class="break-words">{{ value }}</dd>
                        </div>
                    </dl>
                </section>

                <button type="button" class="text-sm text-red-600 hover:underline" @click="discard">Discard</button>
            </aside>
        </div>
    </AppLayout>
</template>
