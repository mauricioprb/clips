<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import AuthLayout from '@/Components/Layout/AuthLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps<{
    token: string;
    email: string;
}>();

const { t } = useI18n();
const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });

function submit(): void {
    form.post('/redefinir-senha', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <AuthLayout :title="t('auth.reset.title')">
        <form class="grid gap-5" @submit.prevent="submit">
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
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>

            <FormField
                id="password"
                v-slot="{ id, invalid, describedBy }"
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
                    :input-props="{ 'aria-describedby': describedBy }"
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

            <Button type="submit" :label="t('auth.reset.submit')" :loading="form.processing" fluid />
        </form>
    </AuthLayout>
</template>
