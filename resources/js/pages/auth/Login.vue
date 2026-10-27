<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import TextInput from '../../components/TextInput.vue';

const page = usePage();
const form = useForm({ email: '', password: '', remember: true });

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Sign in" />
    <AuthLayout title="Welcome back" subtitle="Sign in to see what your clients told Roxy.">
        <p v-if="page.props.flash?.status" class="mb-6 rounded-md bg-emerald-50 px-3 py-2 text-sm/6 text-emerald-800 ring-1 ring-emerald-600/15">{{ page.props.flash.status }}</p>
        <form class="space-y-5" @submit.prevent="submit">
            <TextInput id="email" v-model="form.email" label="Email" type="email" autocomplete="username" required autofocus :error="form.errors.email" />
            <TextInput id="password" v-model="form.password" label="Password" type="password" autocomplete="current-password" required :error="form.errors.password">
                <template #hint>
                    <Link href="/forgot-password" class="text-sm/6 font-medium text-roxy-700 hover:text-roxy-600">Forgot it?</Link>
                </template>
            </TextInput>
            <PrimaryButton class="w-full" :disabled="form.processing">Sign in</PrimaryButton>
        </form>

        <template #after>
            <p class="mt-6 text-center text-sm/6 text-pretty text-neutral-500">
                Giving feedback on a site? You don’t need an account: use the link from your invitation email.
            </p>
        </template>
    </AuthLayout>
</template>
