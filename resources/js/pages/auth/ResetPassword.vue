<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';
import PrimaryButton from '../../components/PrimaryButton.vue';
import TextInput from '../../components/TextInput.vue';

const props = defineProps({ token: String, email: String });
const form = useForm({ token: props.token, email: props.email ?? '', password: '', password_confirmation: '' });
</script>

<template>
    <Head title="New password" />
    <AuthLayout title="Choose a new password">
        <form class="space-y-5" @submit.prevent="form.post('/reset-password')">
            <TextInput id="email" v-model="form.email" label="Email" type="email" autocomplete="username" required :error="form.errors.email" />
            <TextInput id="password" v-model="form.password" label="New password" type="password" autocomplete="new-password" required :error="form.errors.password" />
            <TextInput id="password_confirmation" v-model="form.password_confirmation" label="Repeat it" type="password" autocomplete="new-password" required />
            <PrimaryButton class="w-full" :disabled="form.processing">Save password</PrimaryButton>
        </form>
    </AuthLayout>
</template>
