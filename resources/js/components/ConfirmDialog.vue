<script setup>
import { ExclamationTriangleIcon } from '@heroicons/vue/20/solid';
import { nextTick, ref, watch } from 'vue';
import { confirmState as state, settle } from '../composables/confirm.js';

const dialog = ref(null);
const action = ref(null);

watch(
    () => state.open,
    async (open) => {
        if (open) {
            dialog.value?.showModal();
            await nextTick();
            action.value?.focus();
        } else if (dialog.value?.open) {
            dialog.value.close();
        }
    },
);

// Esc closes the native dialog; treat it as Cancel.
const onClose = () => state.open && settle(false);
const onBackdrop = (event) => event.target === dialog.value && settle(false);
</script>

<template>
    <dialog
        ref="dialog"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl bg-white p-0 shadow-xl ring-1 ring-roxy-950/10 backdrop:bg-roxy-950/30 backdrop:backdrop-blur-[1px] open:animate-[confirm-in_120ms_ease-out]"
        aria-labelledby="confirm-title"
        @close="onClose"
        @click="onBackdrop"
    >
        <div class="flex gap-x-4 p-5 sm:p-6">
            <div v-if="state.danger" class="flex size-9 shrink-0 items-center justify-center rounded-full bg-red-50">
                <ExclamationTriangleIcon class="size-5 text-red-600" />
            </div>
            <div class="min-w-0">
                <h2 id="confirm-title" class="text-sm/6 font-semibold text-neutral-950">{{ state.title }}</h2>
                <p v-if="state.message" class="mt-1 text-sm/6 text-pretty text-neutral-600">{{ state.message }}</p>
            </div>
        </div>
        <div class="flex justify-end gap-x-2 border-t border-roxy-950/5 bg-roxy-50/50 px-5 py-3 sm:px-6">
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm/6 font-semibold text-neutral-700 ring-1 ring-roxy-950/10 hover:bg-white"
                @click="settle(false)"
            >
                Cancel
            </button>
            <button
                ref="action"
                type="button"
                class="rounded-md px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2"
                :class="state.danger ? 'bg-red-600 hover:bg-red-700 focus-visible:outline-red-600' : 'bg-roxy-600 hover:bg-roxy-700 focus-visible:outline-roxy-600'"
                @click="settle(true)"
            >
                {{ state.action }}
            </button>
        </div>
    </dialog>
</template>

<style>
@keyframes confirm-in {
    from {
        opacity: 0;
        transform: scale(0.97);
    }
}
</style>
