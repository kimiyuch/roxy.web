<script setup>
import { CheckIcon, ClipboardDocumentIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';

const props = defineProps({ code: String });
const copied = ref(false);

async function copy() {
    await navigator.clipboard.writeText(props.code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
}
</script>

<template>
    <div class="relative">
        <pre class="rounded-lg bg-roxy-950 py-3.5 pr-12 pl-4 font-mono text-xs/5 whitespace-pre-wrap text-roxy-100 [overflow-wrap:anywhere]"><code>{{ code }}</code></pre>
        <button
            type="button"
            class="absolute top-2 right-2 grid size-8 place-items-center rounded-md text-roxy-300 hover:bg-white/10 hover:text-white"
            :aria-label="copied ? 'Copied' : 'Copy'"
            @click="copy"
        >
            <CheckIcon v-if="copied" class="size-4 text-emerald-400" />
            <ClipboardDocumentIcon v-else class="size-4" />
        </button>
    </div>
</template>
