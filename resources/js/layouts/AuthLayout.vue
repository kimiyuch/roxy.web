<script setup>
defineProps({ title: String, subtitle: String });
</script>

<template>
    <div class="relative isolate flex min-h-dvh flex-col bg-white">
        <div class="absolute inset-x-0 top-0 -z-10 h-[36rem] bg-linear-to-b from-roxy-100 to-white" />

        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <a href="/" class="block">
                    <img src="/images/roxy.webp" alt="Roxy" width="384" height="384" class="mx-auto size-20" />
                </a>
                <h1 class="mt-6 text-center font-display text-3xl font-semibold tracking-tight text-roxy-950">{{ title }}</h1>
                <p v-if="subtitle" class="mt-2 text-center text-base/7 text-pretty text-neutral-600">{{ subtitle }}</p>

                <div class="mt-8 rounded-2xl bg-white p-6 shadow-xl ring-1 shadow-roxy-950/5 ring-roxy-950/10 sm:p-8">
                    <slot />
                </div>

                <slot name="after" />
            </div>
        </div>

        <footer class="px-6 py-6 text-center text-sm/6 text-neutral-500">
            <a href="/" class="font-medium hover:text-roxy-700">Roxy</a>, made with <span aria-label="love">❤️</span> by
            <a href="https://kimiyu.ch" class="font-medium hover:text-roxy-700">kimiyu.ch</a>
        </footer>
    </div>
</template>
