<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import AuthLayout from '@/Components/Layout/AuthLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const form = useForm({ email: '', password: '', remember: false });

function submit(): void {
    form.post('/entrar', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <AuthLayout :title="t('auth.login.title')" :subtitle="t('auth.login.subtitle')">
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
                    autofocus
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>

            <div class="grid gap-1.5">
                <FormField
                    id="password"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('auth.password')"
                    :error="form.errors.password"
                >
                    <Password
                        v-model="form.password"
                        :input-id="id"
                        :feedback="false"
                        toggle-mask
                        autocomplete="current-password"
                        required
                        fluid
                        :invalid="invalid"
                        :input-props="{ 'aria-describedby': describedBy }"
                    />
                </FormField>
                <Link
                    href="/esqueci-a-senha"
                    class="justify-self-end text-sm font-medium text-(--accent) hover:underline"
                >
                    {{ t('auth.login.forgot') }}
                </Link>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <Checkbox v-model="form.remember" binary input-id="remember" />
                {{ t('auth.login.remember') }}
            </label>

            <Button type="submit" :label="t('auth.login.submit')" :loading="form.processing" fluid />
        </form>

        <p class="mt-8 text-center text-sm leading-relaxed text-(--muted)">
            {{ t('auth.login.inviteOnly') }}
        </p>
    </AuthLayout>
</template>
