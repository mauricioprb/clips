<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import AuthLayout from '@/Components/Layout/AuthLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const form = useForm({ email: '' });
</script>

<template>
    <AuthLayout :title="t('auth.forgot.title')" :subtitle="t('auth.forgot.subtitle')">
        <form class="grid gap-5" @submit.prevent="form.post('/esqueci-a-senha')">
            <FormField
                id="email"
                v-slot="{ id, invalid, describedBy }"
                :label="t('auth.email')"
                :error="form.errors.email"
            >
                <InputText
                    :id="id"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    autofocus
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>

            <Button type="submit" :label="t('auth.forgot.submit')" :loading="form.processing" fluid />
        </form>

        <p class="mt-8 text-center text-sm">
            <Link href="/entrar" class="font-semibold text-(--accent) hover:underline">{{
                t('auth.forgot.back')
            }}</Link>
        </p>
    </AuthLayout>
</template>
