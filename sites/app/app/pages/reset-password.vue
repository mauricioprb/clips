<script setup lang="ts">
import AuthCard from '~/ui/auth-card/AuthCard.vue';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';

definePageMeta({ layout: false });
useHead({ title: 'Criar nova senha' });

const route = useRoute();
const { resetPassword } = useAuth();

const userId = String(route.query.userId ?? '');
const secret = String(route.query.secret ?? '');
const password = ref('');
const confirmation = ref('');
const error = ref(
  userId && secret ? '' : 'Este link está incompleto. Peça um novo na tela de login.',
);
const done = ref(false);
const processing = ref(false);

async function submit() {
  if (processing.value) return;
  error.value = '';

  if (password.value !== confirmation.value) {
    error.value = 'As senhas não conferem.';
    return;
  }

  processing.value = true;
  try {
    await resetPassword(userId, secret, password.value);
    done.value = true;
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    processing.value = false;
  }
}
</script>

<template>
  <main>
    <AuthCard
      layout="simple"
      title="Criar nova senha"
      description="Escolha uma senha com pelo menos 8 caracteres."
    >
      <template #logo><AppBrand /></template>
      <Alert v-if="done" intent="success" variant="soft">
        Senha alterada. Entre com o seu e-mail e a nova senha.
      </Alert>
      <form v-else class="flex flex-col gap-5" @submit.prevent="submit">
        <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>
        <Input
          id="password"
          v-model="password"
          label="Nova senha"
          type="password"
          name="password"
          autocomplete="new-password"
          minlength="8"
          :disabled="processing"
          required
        />
        <Input
          id="confirmation"
          v-model="confirmation"
          label="Confirme a nova senha"
          type="password"
          name="confirmation"
          autocomplete="new-password"
          minlength="8"
          :disabled="processing"
          required
        />
        <Button type="submit" size="lg" class="w-full" :loading="processing">
          Salvar nova senha
        </Button>
      </form>
      <template #footer>
        <NuxtLink
          to="/login"
          class="font-semibold text-primary-text underline-offset-4 hover:underline"
        >
          Voltar para o login
        </NuxtLink>
      </template>
    </AuthCard>
  </main>
</template>
