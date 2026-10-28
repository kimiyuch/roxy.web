<script setup>
import { computed } from 'vue';
import TextInput from './TextInput.vue';

// Name, addresses, repository and how the button looks. Used by "Add site" and the site's settings.
const props = defineProps({ form: Object });

const colors = [
    { value: null, label: 'Roxy rose', swatch: '#d34f72' },
    { value: '#4f79ad', label: 'Sky', swatch: '#4f79ad' },
    { value: '#0f766e', label: 'Teal', swatch: '#0f766e' },
    { value: '#c2410c', label: 'Orange', swatch: '#c2410c' },
    { value: '#1c1917', label: 'Black', swatch: '#1c1917' },
];
const custom = computed(() => props.form.color && !colors.some((color) => color.value === props.form.color));

// The start URL's own address is always allowed; shown so the extra ones make sense
const startOrigin = computed(() => {
    try {
        return new URL(props.form.start_url).origin;
    } catch {
        return null;
    }
});

const originErrors = computed(() =>
    Object.entries(props.form.errors)
        .filter(([key]) => key === 'origins' || key.startsWith('origins.'))
        .map(([, message]) => message)[0],
);
</script>

<template>
    <div class="space-y-6">
        <TextInput id="name" v-model="form.name" label="Name" type="text" placeholder="Alpenblick" required :error="form.errors.name" />

        <div>
            <TextInput id="start_url" v-model="form.start_url" label="Start page" type="url" placeholder="https://staging.alpenblick.ch" required :error="form.errors.start_url" />
            <p class="mt-1.5 text-sm/5 text-neutral-500">Where the link in the invitation email opens. Usually the staging site’s home page.</p>
        </div>

        <div>
            <label for="origins" class="block text-sm/6 font-medium text-roxy-950">More addresses <span class="font-normal text-neutral-500">(optional)</span></label>
            <textarea
                id="origins"
                v-model="form.origins"
                rows="3"
                placeholder="https://www.alpenblick.ch&#10;https://*.alpenblick.ch"
                class="mt-1.5 block w-full rounded-md border-0 px-3 py-2 font-mono text-sm/6 text-neutral-950 ring-1 ring-neutral-950/15 placeholder:text-neutral-400 focus:ring-2 focus:ring-roxy-500 focus:outline-none"
                :class="{ 'ring-red-500/60': originErrors }"
            />
            <p v-if="originErrors" class="mt-1.5 text-sm/5 text-red-700">{{ originErrors }}</p>
            <!-- One line on purpose: Vue drops the spaces between lines next to elements -->
            <p class="mt-1.5 text-sm/5 text-neutral-500">Where else the widget may run, one per line, for example production next to staging. <template v-if="startOrigin"><span class="font-mono text-neutral-700">{{ startOrigin }}</span> is always allowed. </template><span class="font-mono text-neutral-700">https://*.example.ch</span> allows all its subdomains.</p>
        </div>

        <div>
            <TextInput id="repository" v-model="form.repository" label="GitHub repository" type="text" placeholder="kimiyuch/alpenblick.web" :error="form.errors.repository">
                <template #hint><span class="text-sm/6 text-neutral-500">optional</span></template>
            </TextInput>
            <p class="mt-1.5 text-sm/5 text-neutral-500">Your agent gets it with the feedback, so it knows where the code lives.</p>
        </div>

        <fieldset>
            <legend class="text-sm/6 font-medium text-roxy-950">Button</legend>
            <div class="mt-2 flex flex-wrap items-center gap-x-6 gap-y-3">
                <div class="flex rounded-md bg-roxy-50 p-0.5 text-sm/5 font-medium ring-1 ring-roxy-950/5">
                    <button
                        v-for="option in [{ value: 'bottom-left', label: 'Bottom left' }, { value: 'bottom-right', label: 'Bottom right' }]"
                        :key="option.value"
                        type="button"
                        class="rounded px-3 py-1"
                        :class="form.position === option.value ? 'bg-white text-roxy-950 shadow-xs' : 'text-neutral-500 hover:text-roxy-950'"
                        :aria-pressed="form.position === option.value"
                        @click="form.position = option.value"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <div class="flex items-center gap-x-2">
                    <button
                        v-for="color in colors"
                        :key="color.label"
                        type="button"
                        class="size-7 rounded-full ring-offset-2"
                        :class="form.color === color.value ? 'ring-2 ring-roxy-500' : 'ring-1 ring-black/10'"
                        :style="{ backgroundColor: color.swatch }"
                        :title="color.label"
                        :aria-label="color.label"
                        :aria-pressed="form.color === color.value"
                        @click="form.color = color.value"
                    />
                    <label
                        class="relative grid size-7 cursor-pointer place-items-center overflow-hidden rounded-full bg-[conic-gradient(red,yellow,lime,aqua,blue,magenta,red)] ring-offset-2"
                        :class="custom ? 'ring-2 ring-roxy-500' : 'ring-1 ring-black/10'"
                        title="Your own colour"
                    >
                        <span class="sr-only">Your own colour</span>
                        <span v-if="custom" class="size-4 rounded-full ring-2 ring-white" :style="{ backgroundColor: form.color }" />
                        <input type="color" class="absolute inset-0 cursor-pointer opacity-0" :value="form.color ?? '#d34f72'" @input="form.color = $event.target.value" />
                    </label>
                </div>
            </div>
            <p v-if="form.errors.color" class="mt-1.5 text-sm/5 text-red-700">{{ form.errors.color }}</p>
        </fieldset>
    </div>
</template>
