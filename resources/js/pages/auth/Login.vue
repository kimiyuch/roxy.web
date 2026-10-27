<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import TextInput from '../../components/TextInput.vue';

const form = useForm({ email: '', password: '', remember: true });

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Sign in" />
    <AuthLayout title="Sign in to Roxy">
        <form class="space-y-4" @submit.prevent="submit">
            <TextInput id="email" v-model="form.email" label="Email" type="email" autocomplete="username" required autofocus :error="form.errors.email" />
            <TextInput id="password" v-model="form.password" label="Password" type="password" autocomplete="current-password" required :error="form.errors.password" />
            <PrimaryButton class="w-full" :disabled="form.processing">Sign in</PrimaryButton>
            <p class="text-center text-sm">
                <Link href="/forgot-password" class="text-neutral-600 hover:text-neutral-950">Forgot your password?</Link>
            </p>
        </form>
    </AuthLayout>
</template>
