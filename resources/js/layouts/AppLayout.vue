<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-full">
        <header class="border-b border-neutral-200 bg-white">
            <div class="mx-auto flex h-14 max-w-6xl items-center gap-6 px-6">
                <Link href="/app/feedback" class="flex items-center gap-2 font-semibold">
                    <img src="/images/favicon.png" alt="" class="size-6" />
                    Roxy
                </Link>
                <nav class="flex gap-4 text-sm">
                    <Link
                        href="/app/feedback"
                        class="text-neutral-600 hover:text-neutral-950"
                        :class="{ 'text-neutral-950! font-medium': page.url.startsWith('/app/feedback') }"
                    >
                        Feedback
                    </Link>
                </nav>
                <div class="ml-auto flex items-center gap-4 text-sm text-neutral-600">
                    <span>{{ user?.name }}</span>
                    <button type="button" class="hover:text-neutral-950" @click="logout">Sign out</button>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-6 py-8">
            <slot />
        </main>
    </div>
</template>
