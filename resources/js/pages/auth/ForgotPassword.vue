<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import TextInput from '../../components/TextInput.vue';

const page = usePage();
const form = useForm({ email: '' });
</script>

<template>
    <Head title="Reset password" />
    <AuthLayout title="Reset your password">
        <p v-if="page.props.flash?.status" class="mb-4 text-sm text-green-700">{{ page.props.flash.status }}</p>
        <form class="space-y-4" @submit.prevent="form.post('/forgot-password')">
            <TextInput id="email" v-model="form.email" label="Email" type="email" required autofocus :error="form.errors.email" />
            <PrimaryButton class="w-full" :disabled="form.processing">Email me a reset link</PrimaryButton>
            <p class="text-center text-sm">
                <Link href="/login" class="text-neutral-600 hover:text-neutral-950">Back to sign in</Link>
            </p>
        </form>
    </AuthLayout>
</template>
