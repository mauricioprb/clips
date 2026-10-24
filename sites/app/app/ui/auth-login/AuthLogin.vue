<script setup lang="ts">
import AuthCard from '~/ui/auth-card/AuthCard.vue';
import { Alert } from '@/ui/alert';
import { Button } from '@/ui/button';
import Input from '~/ui/input/Input.vue';

withDefaults(defineProps<{ processing?: boolean; error?: string }>(), {
  processing: false,
  error: '',
});

const email = defineModel<string>('email', { default: '' });
const password = defineModel<string>('password', { default: '' });
defineEmits<{ submit: [] }>();
</script>

<template>
  <AuthCard
    layout="simple"
    title="Bem-vindo de volta"
    description="Entre na sua conta para acessar suas notas."
  >
    <template #logo><AppBrand /></template>
    <form class="flex flex-col gap-5" @submit.prevent="$emit('submit')">
      <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>
      <Input
        id="email"
        v-model="email"
        label="E-mail"
        type="email"
        name="email"
        autocomplete="email"
        placeholder="voce@exemplo.com"
        :disabled="processing"
        required
      />
      <Input
        id="password"
        v-model="password"
        label="Senha"
        type="password"
        name="password"
        autocomplete="current-password"
        :disabled="processing"
        required
      />
      <Button type="submit" size="lg" class="w-full" :loading="processing" loading-text="Entrando…">
        Entrar
      </Button>
    </form>
    <template #footer>
      Ainda não tem conta?
      <NuxtLink
        to="/signup"
        class="font-semibold text-primary-text underline-offset-4 hover:underline"
      >
        Criar conta
      </NuxtLink>
    </template>
  </AuthCard>
</template>
