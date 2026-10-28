<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, ArrowPathIcon, ArrowTopRightOnSquareIcon, CheckIcon, InboxIcon, LinkIcon, TrashIcon, UserPlusIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';
import CodeBlock from './CodeBlock.vue';
import PrimaryButton from './PrimaryButton.vue';
import SiteForm from './SiteForm.vue';
import TextInput from './TextInput.vue';
import { siteDot, timeAgo } from '../utils.js';

// The third column of the sites page: script tag, reviewers, settings, delete.
const props = defineProps({ site: Object, reviewers: Array });

const settings = useForm({
    name: props.site.name,
    start_url: props.site.start_url,
    origins: props.site.origins.join('\n'),
    repository: props.site.repository ?? '',
    position: props.site.position,
    color: props.site.color,
});

const invite = useForm({ email: '', name: '' });

function sendInvite() {
    invite.post(`/app/sites/${props.site.id}/reviewers`, { preserveScroll: true, preserveState: true, onSuccess: () => invite.reset() });
}

function resend(reviewer) {
    router.post(`/app/sites/${props.site.id}/reviewers/${reviewer.id}/resend`, {}, { preserveScroll: true });
}

function remove(reviewer) {
    if (confirm(`Remove ${reviewer.name || reviewer.email}? They’re signed out everywhere and can’t give feedback anymore. Their feedback stays.`)) {
        router.delete(`/app/sites/${props.site.id}/reviewers/${reviewer.id}`, { preserveScroll: true });
    }
}

const copied = ref(null);
async function copyLink(reviewer) {
    await navigator.clipboard.writeText(reviewer.link);
    copied.value = reviewer.id;
    setTimeout(() => (copied.value = null), 1500);
}

function save() {
    settings.put(`/app/sites/${props.site.id}`, { preserveScroll: true, preserveState: true, onSuccess: () => settings.defaults() });
}

function destroy() {
    if (confirm(`Delete ${props.site.name} with all its feedback and screenshots? This can’t be undone.`)) {
        router.delete(`/app/sites/${props.site.id}`);
    }
}

const initials = (reviewer) =>
    (reviewer.name || reviewer.email)
        .split(/[\s@.]+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex items-center gap-x-3 h-14 shrink-0 border-b border-roxy-950/5 px-4 sm:px-6">
            <Link href="/app/sites" class="rounded-md p-1 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950 lg:hidden" aria-label="Back to sites">
                <ArrowLeftIcon class="size-4" />
            </Link>
            <span class="size-2.5 shrink-0 rounded-full" :class="siteDot(site.id)" />
            <h2 class="truncate font-display text-base font-semibold text-roxy-950">{{ site.name }}</h2>
            <div class="ml-auto flex shrink-0 items-center gap-x-1 text-sm/5">
                <Link :href="`/app/feedback?site=${site.id}`" class="inline-flex items-center gap-x-1.5 rounded-md px-2.5 py-1.5 font-medium text-neutral-600 hover:bg-roxy-50 hover:text-roxy-950">
                    <InboxIcon class="size-4 fill-neutral-400" />
                    Feedback<span v-if="site.open_count" class="rounded-full bg-galah-100 px-1.5 text-xs text-galah-600 tabular-nums">{{ site.open_count }}</span>
                </Link>
                <a :href="site.start_url" target="_blank" rel="noopener" title="Open the site" class="rounded-md p-1.5 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950">
                    <ArrowTopRightOnSquareIcon class="size-4" />
                    <span class="sr-only">Open the site</span>
                </a>
            </div>
        </div>

        <div class="@container min-h-0 flex-1 divide-y divide-roxy-950/5 overflow-y-auto">
            <!-- Install -->
            <section class="p-5 sm:p-6">
                <h3 class="text-sm/6 font-semibold text-roxy-950">Script tag</h3>
                <p class="mt-0.5 text-sm/6 text-pretty text-neutral-500">Put it on every page of the site, just before <code class="font-mono text-xs text-roxy-900">&lt;/body&gt;</code>. Visitors who aren’t invited don’t see a thing.</p>
                <CodeBlock :code="site.script_tag" class="mt-3" />
                <p class="mt-2 text-xs/5 text-neutral-500">To sign in on another device, open any page of the site with <span class="font-mono text-neutral-700">?feedback</span> at the end of the address.</p>
            </section>

            <!-- Reviewers -->
            <section class="p-5 sm:p-6">
                <h3 class="text-sm/6 font-semibold text-roxy-950">Reviewers</h3>
                <p class="mt-0.5 text-sm/6 text-pretty text-neutral-500">The people who give feedback. They get a link by email that opens the site and signs them in. No password.</p>

                <form class="mt-4 grid grid-cols-1 gap-3 @xl:grid-cols-[1fr_1fr_auto] @xl:items-end @md:grid-cols-2" @submit.prevent="sendInvite">
                    <TextInput id="invite-email" v-model="invite.email" label="Email" type="email" placeholder="anna@alpenblick.ch" required :error="invite.errors.email" />
                    <TextInput id="invite-name" v-model="invite.name" label="Name" type="text" placeholder="Anna Keller" :error="invite.errors.name">
                        <template #hint><span class="text-xs/6 text-neutral-500">optional</span></template>
                    </TextInput>
                    <PrimaryButton :disabled="invite.processing" class="@md:max-@xl:col-span-2 @md:max-@xl:justify-self-end @xl:mb-px">
                        <UserPlusIcon class="size-4" />
                        Invite
                    </PrimaryButton>
                </form>

                <ul v-if="reviewers.length" class="mt-4 divide-y divide-roxy-950/5 rounded-lg ring-1 ring-roxy-950/10">
                    <li v-for="reviewer in reviewers" :key="reviewer.id" class="flex items-center gap-x-3 px-3 py-2.5">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-roxy-100 text-xs font-semibold text-roxy-800">{{ initials(reviewer) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm/5 font-medium text-roxy-950">
                                {{ reviewer.name || reviewer.email }}
                                <span v-if="reviewer.name" class="font-normal text-neutral-500">· {{ reviewer.email }}</span>
                            </p>
                            <p class="truncate text-xs/5 text-neutral-500">
                                <template v-if="reviewer.browsers">
                                    Signed in<template v-if="reviewer.browsers > 1"> on {{ reviewer.browsers }} browsers</template>
                                    <template v-if="reviewer.last_seen_at"> · seen {{ timeAgo(reviewer.last_seen_at) }}</template>
                                </template>
                                <template v-else>Invited {{ timeAgo(reviewer.invited_at) }}, not signed in yet</template>
                                <template v-if="reviewer.feedback_count"> · {{ reviewer.feedback_count }} feedback</template>
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center">
                            <button type="button" class="rounded-md p-1.5 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950" :title="copied === reviewer.id ? 'Copied' : 'Copy their sign-in link'" @click="copyLink(reviewer)">
                                <CheckIcon v-if="copied === reviewer.id" class="size-4 text-emerald-600" />
                                <LinkIcon v-else class="size-4" />
                                <span class="sr-only">Copy their sign-in link</span>
                            </button>
                            <button type="button" class="rounded-md p-1.5 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950" title="Email a new link" @click="resend(reviewer)">
                                <ArrowPathIcon class="size-4" />
                                <span class="sr-only">Email a new link</span>
                            </button>
                            <button type="button" class="rounded-md p-1.5 text-neutral-400 hover:bg-red-50 hover:text-red-600" title="Remove" @click="remove(reviewer)">
                                <TrashIcon class="size-4" />
                                <span class="sr-only">Remove</span>
                            </button>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm/6 text-neutral-500">Nobody invited yet.</p>
            </section>

            <!-- Settings -->
            <section class="p-5 sm:p-6">
                <h3 class="text-sm/6 font-semibold text-roxy-950">Settings</h3>
                <p class="mt-0.5 text-sm/6 text-pretty text-neutral-500">Where the site runs, where its code is, and how the button looks.</p>
                <form class="mt-5" @submit.prevent="save">
                    <SiteForm :form="settings" />
                    <div class="mt-6 flex justify-end">
                        <PrimaryButton :disabled="settings.processing || !settings.isDirty">Save</PrimaryButton>
                    </div>
                </form>
            </section>

            <!-- Delete -->
            <section class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
                <div>
                    <h3 class="text-sm/6 font-semibold text-roxy-950">Delete site</h3>
                    <p class="mt-0.5 text-sm/6 text-pretty text-neutral-500">Removes the site with its reviewers, feedback and screenshots.</p>
                </div>
                <button type="button" class="inline-flex items-center gap-x-1.5 rounded-md px-3 py-1.5 text-sm/6 font-semibold text-red-700 ring-1 ring-red-600/20 hover:bg-red-50" @click="destroy">
                    <TrashIcon class="size-4" />
                    Delete
                </button>
            </section>
        </div>
    </div>
</template>
