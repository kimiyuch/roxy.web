<script setup>
defineProps({ title: String });
</script>

<template>
    <div class="flex min-h-full items-center justify-center bg-roxy-100 px-6 py-12">
        <div class="w-full max-w-sm">
            <img src="/images/icon.png" alt="" class="mx-auto size-20" />
            <h1 class="mt-4 text-center text-xl font-semibold">{{ title }}</h1>
            <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <slot />
            </div>
        </div>
    </div>
</template>
