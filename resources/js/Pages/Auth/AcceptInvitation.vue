<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import AuthLayout from '@/Components/Layout/AuthLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps<{
    token: string;
    email: string | null;
    name: string | null;
}>();

const { t } = useI18n();
const form = useForm({ token: props.token, email: props.email ?? '', password: '', password_confirmation: '' });

function submit(): void {
    form.post('/convite', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <AuthLayout
        v-if="email"
        :title="t('invitation.title')"
        :subtitle="t('invitation.subtitle', { name: name?.split(' ')[0] ?? '' })"
    >
        <form class="grid gap-5" @submit.prevent="submit">
            <FormField id="email" v-slot="{ id }" :label="t('auth.email')">
                <InputText :id="id" :model-value="email" type="email" readonly fluid />
            </FormField>

            <FormField
                id="password"
                v-slot="{ id, invalid }"
                :label="t('security.new')"
                :hint="t('auth.register.passwordHint')"
                :error="form.errors.password"
            >
                <Password
                    v-model="form.password"
                    :input-id="id"
                    :feedback="false"
                    toggle-mask
                    autocomplete="new-password"
                    autofocus
                    required
                    fluid
                    :invalid="invalid"
                />
            </FormField>

            <FormField id="password_confirmation" v-slot="{ id }" :label="t('security.confirm')">
                <Password
                    v-model="form.password_confirmation"
                    :input-id="id"
                    :feedback="false"
                    toggle-mask
                    autocomplete="new-password"
                    required
                    fluid
                />
            </FormField>

            <Button type="submit" :label="t('invitation.submit')" :loading="form.processing" fluid />
        </form>
    </AuthLayout>

    <AuthLayout v-else :title="t('invitation.expiredTitle')" :subtitle="t('invitation.expiredBody')">
        <Link href="/entrar" class="text-sm font-semibold text-(--accent) hover:underline">{{
            t('auth.forgot.back')
        }}</Link>
    </AuthLayout>
</template>
