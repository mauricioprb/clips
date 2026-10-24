<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ArrowLeft } from '@lucide/vue';
import { AuthCard, type AuthCardLayout } from '@/ui/auth-card';
import { Alert } from '@/ui/alert';
import { Button } from '@/ui/button';
import { Form } from '@/ui/form';
import { Input } from '@/ui/input';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  title?: string;
  description?: string;
  layout?: AuthCardLayout;
  expressive?: boolean;
  asideTitle?: string;
  asideDescription?: string;
  status?: string;
  error?: string;
  processing?: boolean;
  emailLabel?: string;
  emailPlaceholder?: string;
  submitLabel?: string;
  backLabel?: string;
  backHref?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  title: () => useFuseLocale().authForgotPassword.title,
  description: () => useFuseLocale().authForgotPassword.description,
  layout: 'card',
  expressive: false,
  processing: false,
  emailLabel: () => useFuseLocale().authForgotPassword.emailLabel,
  emailPlaceholder: () => useFuseLocale().authForgotPassword.emailPlaceholder,
  submitLabel: () => useFuseLocale().authForgotPassword.submitLabel,
  backLabel: () => useFuseLocale().authForgotPassword.backLabel,
});

const email = defineModel<string>('email', { default: '' });

defineEmits<{ submit: [] }>();
</script>

<template>
  <AuthCard
    :title="title"
    :description="description"
    :layout="layout"
    :expressive="expressive"
    :aside-title="asideTitle"
    :aside-description="asideDescription"
    :class="props.class"
  >
    <template v-if="$slots.logo" #logo><slot name="logo" /></template>
    <template v-if="$slots.aside" #aside><slot name="aside" /></template>

    <div class="flex flex-col gap-6">
      <Alert v-if="status" variant="soft" intent="success">{{ status }}</Alert>
      <Form @submit="$emit('submit')">
        <Input
          v-model="email"
          type="email"
          name="email"
          autocomplete="email"
          required
          :label="emailLabel"
          :placeholder="emailPlaceholder"
          :error="error"
        />
        <Button type="submit" size="lg" class="w-full" :loading="processing">
          {{ submitLabel }}
        </Button>
      </Form>
    </div>

    <template v-if="backHref" #footer>
      <Button as="a" :href="backHref" variant="ghost" size="sm">
        <ArrowLeft />
        {{ backLabel }}
      </Button>
    </template>
  </AuthCard>
</template>
