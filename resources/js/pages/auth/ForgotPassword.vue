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
    <AuthLayout title="Forgot your password?" subtitle="We’ll email you a link to choose a new one.">
        <p v-if="page.props.flash?.status" class="mb-6 rounded-md bg-emerald-50 px-3 py-2 text-sm/6 text-emerald-800 ring-1 ring-emerald-600/15">{{ page.props.flash.status }}</p>
        <form class="space-y-5" @submit.prevent="form.post('/forgot-password')">
            <TextInput id="email" v-model="form.email" label="Email" type="email" autocomplete="username" required autofocus :error="form.errors.email" />
            <PrimaryButton class="w-full" :disabled="form.processing">Email me a link</PrimaryButton>
        </form>

        <template #after>
            <p class="mt-6 text-center text-sm/6">
                <Link href="/login" class="font-medium text-roxy-700 hover:text-roxy-600"><span aria-hidden="true">←</span> Back to sign in</Link>
            </p>
        </template>
    </AuthLayout>
</template>
