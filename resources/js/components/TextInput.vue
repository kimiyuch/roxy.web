<script setup>
defineProps({ label: String, error: String, id: String });
const model = defineModel();
</script>

<template>
    <div>
        <label :for="id" class="block text-sm font-medium text-neutral-700">{{ label }}</label>
        <input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            class="mt-1 block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-neutral-300 focus:ring-2 focus:ring-roxy-500 focus:outline-none"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
