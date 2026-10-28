<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, GlobeAltIcon, PlusIcon } from '@heroicons/vue/20/solid';
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import SiteDetail from '../../components/SiteDetail.vue';
import SiteForm from '../../components/SiteForm.vue';
import { siteDot, timeAgo } from '../../utils.js';

// Like the inbox: sites in the middle, the open one (or "Add site") on the right.
const props = defineProps({ sites: Array, selected: Object, reviewers: Array, creating: Boolean });

const showsDetail = computed(() => props.creating || props.selected);
const form = useForm({ name: '', start_url: '', origins: '', repository: '', position: 'bottom-right', color: null });
</script>

<template>
    <Head :title="creating ? 'Add site' : (selected?.name ?? 'Sites')" />
    <AppLayout>
        <div class="flex h-full">
            <!-- List -->
            <section class="flex min-w-0 flex-1 flex-col lg:max-w-sm lg:border-r lg:border-roxy-950/5" :class="{ 'max-lg:hidden': showsDetail }">
                <div class="flex items-center justify-between gap-x-3 h-14 shrink-0 border-b border-roxy-950/5 px-4">
                    <h1 class="font-display text-base font-semibold text-roxy-950">Sites</h1>
                    <Link
                        href="/app/sites/create"
                        class="inline-flex items-center gap-x-1 rounded-md px-2 py-1 text-sm/5 font-medium ring-1"
                        :class="creating ? 'bg-roxy-600 text-white ring-roxy-600' : 'text-roxy-800 ring-roxy-950/10 hover:bg-roxy-50'"
                    >
                        <PlusIcon class="size-4" />
                        Add site
                    </Link>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    <ul v-if="sites.length" class="divide-y divide-roxy-950/5">
                        <li v-for="site in sites" :key="site.id">
                            <Link
                                :href="`/app/sites/${site.id}`"
                                preserve-scroll
                                class="relative flex items-center gap-x-3 px-4 py-3"
                                :class="site.id === selected?.id ? 'bg-roxy-50/70' : 'hover:bg-neutral-50'"
                            >
                                <span class="size-2.5 shrink-0 rounded-full" :class="siteDot(site.id)" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm/5 font-medium text-roxy-950">{{ site.name }}</p>
                                    <p class="truncate text-xs/5 text-neutral-500">
                                        {{ site.host }} · {{ site.reviewers_count }} {{ site.reviewers_count === 1 ? 'reviewer' : 'reviewers' }}
                                    </p>
                                    <p v-if="site.last_feedback_at" class="truncate text-xs/5 text-neutral-400">Last feedback {{ timeAgo(site.last_feedback_at) }}</p>
                                </div>
                                <span v-if="site.open_count" class="shrink-0 rounded-full bg-galah-100 px-2 text-xs/5 font-medium text-galah-600 tabular-nums">{{ site.open_count }} open</span>
                            </Link>
                        </li>
                    </ul>

                    <div v-else class="px-6 py-16 text-center">
                        <GlobeAltIcon class="mx-auto size-6 fill-roxy-300" />
                        <p class="mt-2 text-sm/6 text-pretty text-neutral-500">No sites yet. Add the one you’re building, put Roxy on it and invite your client.</p>
                    </div>
                </div>
            </section>

            <!-- A site, or "Add site" -->
            <section class="min-w-0 flex-1 flex-col" :class="showsDetail ? 'flex' : 'hidden lg:flex'">
                <SiteDetail v-if="selected" :key="selected.id" :site="selected" :reviewers="reviewers" class="min-h-0 flex-1" />

                <form v-else-if="creating" class="flex h-full flex-col" @submit.prevent="form.post('/app/sites')">
                    <div class="flex items-center gap-x-3 h-14 shrink-0 border-b border-roxy-950/5 px-4 sm:px-6">
                        <Link href="/app/sites" class="rounded-md p-1 text-neutral-400 hover:bg-roxy-50 hover:text-roxy-950 lg:hidden" aria-label="Back to sites">
                            <ArrowLeftIcon class="size-4" />
                        </Link>
                        <h2 class="font-display text-base font-semibold text-roxy-950">Add site</h2>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-6">
                        <p class="mb-6 text-sm/6 text-pretty text-neutral-500">Next you’ll get the script tag for the site and can invite your client.</p>
                        <SiteForm :form="form" />
                    </div>
                    <div class="flex justify-end gap-x-3 border-t border-roxy-950/5 px-5 py-4 sm:px-6">
                        <Link href="/app/sites" class="rounded-md px-3 py-1.5 text-sm/6 font-semibold text-neutral-600 hover:text-roxy-950">Cancel</Link>
                        <PrimaryButton :disabled="form.processing">Add site</PrimaryButton>
                    </div>
                </form>

                <div v-else class="grid flex-1 place-items-center bg-gray-50">
                    <div class="text-center">
                        <img src="/images/roxy.webp" alt="" class="mx-auto size-16 opacity-60 grayscale-50" />
                        <p class="mt-3 text-sm text-neutral-500">Pick a site to see its reviewers and settings.</p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
