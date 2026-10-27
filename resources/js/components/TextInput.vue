<script setup>
defineOptions({ inheritAttrs: false });
defineProps({ label: String, error: String, id: String });
const model = defineModel();
</script>

<template>
    <div>
        <div class="flex items-baseline justify-between gap-x-3">
            <label :for="id" class="block text-sm/6 font-medium text-roxy-950">{{ label }}</label>
            <slot name="hint" />
        </div>
        <input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? `${id}-error` : undefined"
            class="mt-1.5 block w-full rounded-md border-0 bg-white px-3 py-2 text-base/6 text-neutral-950 ring-1 ring-neutral-950/15 placeholder:text-neutral-400 focus:ring-2 focus:ring-roxy-500 focus:outline-none sm:text-sm/6"
            :class="{ 'ring-red-500/60': error }"
        />
        <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-sm/5 text-red-700">{{ error }}</p>
    </div>
</template>
