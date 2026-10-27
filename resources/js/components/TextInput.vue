<script setup>
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/20/solid';
import { computed, ref, useAttrs } from 'vue';

defineOptions({ inheritAttrs: false });
defineProps({ label: String, error: String, id: String });
const model = defineModel();

// Password fields get a button to show what's typed
const attrs = useAttrs();
const password = computed(() => attrs.type === 'password');
const visible = ref(false);
</script>

<template>
    <div>
        <div class="flex items-baseline justify-between gap-x-3">
            <label :for="id" class="block text-sm/6 font-medium text-roxy-950">{{ label }}</label>
            <slot name="hint" />
        </div>
        <div class="relative mt-1.5">
            <input
                :id="id"
                v-model="model"
                v-bind="$attrs"
                :type="password && visible ? 'text' : $attrs.type"
                :aria-invalid="error ? 'true' : undefined"
                :aria-describedby="error ? `${id}-error` : undefined"
                class="block w-full rounded-md border-0 bg-white px-3 py-2 text-base/6 text-neutral-950 ring-1 ring-neutral-950/15 placeholder:text-neutral-400 focus:ring-2 focus:ring-roxy-500 focus:outline-none sm:text-sm/6"
                :class="{ 'ring-red-500/60': error, 'pr-10': password }"
            />
            <button
                v-if="password"
                type="button"
                class="absolute inset-y-0 right-0 grid w-10 place-items-center text-neutral-400 hover:text-roxy-700"
                :aria-label="visible ? 'Hide password' : 'Show password'"
                :aria-pressed="visible"
                @click="visible = !visible"
            >
                <component :is="visible ? EyeSlashIcon : EyeIcon" class="size-4" />
            </button>
        </div>
        <p v-if="error" :id="`${id}-error`" class="mt-1.5 text-sm/5 text-red-700">{{ error }}</p>
    </div>
</template>
