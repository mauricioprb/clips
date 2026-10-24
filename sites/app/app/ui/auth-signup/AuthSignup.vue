<script setup lang="ts">
import AuthCard from '~/ui/auth-card/AuthCard.vue';
import { Alert } from '@/ui/alert';
import { Button } from '@/ui/button';
import Input from '~/ui/input/Input.vue';

withDefaults(defineProps<{ processing?: boolean; error?: string }>(), {
  processing: false,
  error: '',
});

const name = defineModel<string>('name', { default: '' });
const email = defineModel<string>('email', { default: '' });
const password = defineModel<string>('password', { default: '' });
defineEmits<{ submit: [] }>();
</script>

<template>
  <AuthCard
    layout="simple"
    title="Crie sua conta"
    description="Um espaço só seu para guardar o que precisa lembrar."
  >
    <template #logo><AppBrand /></template>
    <form class="flex flex-col gap-5" @submit.prevent="$emit('submit')">
      <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>
      <Input
        id="name"
        v-model="name"
        label="Nome"
        name="name"
        autocomplete="name"
        placeholder="Como você quer ser chamado?"
        :disabled="processing"
        required
      />
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
        autocomplete="new-password"
        :minlength="8"
        hint="Use pelo menos 8 caracteres."
        :disabled="processing"
        required
      />
      <Button
        type="submit"
        size="lg"
        class="w-full"
        :loading="processing"
        loading-text="Criando conta…"
      >
        Criar conta
      </Button>
    </form>
    <template #footer>
      Já tem conta?
      <NuxtLink
        to="/login"
        class="font-semibold text-primary-text underline-offset-4 hover:underline"
      >
        Entrar
      </NuxtLink>
    </template>
  </AuthCard>
</template>
