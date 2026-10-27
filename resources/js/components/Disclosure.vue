<script setup>
import { ChevronRightIcon } from '@heroicons/vue/16/solid';
import { ref, useId } from 'vue';

const props = defineProps({ label: String, count: Number, tone: { type: String, default: 'neutral' }, open: Boolean });
const expanded = ref(props.open);
const id = useId();
</script>

<template>
    <div>
        <button
            type="button"
            class="group flex w-full items-center gap-x-1.5 rounded-md py-1 text-left text-xs/5 font-medium text-neutral-700 hover:text-roxy-950"
            :aria-expanded="expanded"
            :aria-controls="id"
            @click="expanded = !expanded"
        >
            <ChevronRightIcon class="size-4 shrink-0 text-neutral-400 transition-transform group-hover:text-roxy-500" :class="{ 'rotate-90': expanded }" />
            {{ label }}
            <span
                v-if="count"
                class="rounded-full px-1.5 text-[0.6875rem]/4 font-medium tabular-nums"
                :class="tone === 'red' ? 'bg-red-50 text-red-700' : 'bg-neutral-100 text-neutral-600'"
            >
                {{ count }}
            </span>
        </button>
        <div v-show="expanded" :id="id" class="mt-1 ml-5.5 overflow-hidden rounded-lg bg-neutral-50 ring-1 ring-neutral-950/5">
            <slot />
        </div>
    </div>
</template>
