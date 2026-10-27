<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import TextInput from '../../components/TextInput.vue';

const form = useForm({ name: '', email: '', password: '' });

function submit() {
    form.post('/register', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Sign up" />
    <AuthLayout title="Create your account" subtitle="Collect client feedback your coding agent can fix.">
        <form class="space-y-5" @submit.prevent="submit">
            <TextInput id="name" v-model="form.name" label="Name" type="text" autocomplete="name" required autofocus :error="form.errors.name" />
            <TextInput id="email" v-model="form.email" label="Email" type="email" autocomplete="username" required :error="form.errors.email" />
            <TextInput id="password" v-model="form.password" label="Password" type="password" autocomplete="new-password" required :error="form.errors.password" />
            <PrimaryButton class="w-full" :disabled="form.processing">Create account</PrimaryButton>
        </form>

        <p class="mt-6 text-center text-sm/6 text-neutral-600">
            Already have an account?
            <Link href="/login" class="font-medium text-roxy-700 hover:text-roxy-600">Sign in</Link>
        </p>
    </AuthLayout>
</template>
