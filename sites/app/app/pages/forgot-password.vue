<script setup lang="ts">
import { AuthForgotPassword } from '~/ui/auth-forgot-password';

definePageMeta({ layout: false });
useHead({ title: 'Redefinir senha' });

const { requestRecovery } = useAuth();

const email = ref('');
const status = ref('');
const error = ref('');
const processing = ref(false);

async function submit() {
  if (processing.value) return;
  error.value = '';
  status.value = '';
  processing.value = true;
  try {
    await requestRecovery(email.value);
    status.value =
      'Se o e-mail tiver uma conta no Clips, você vai receber um link para criar uma nova senha.';
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    processing.value = false;
  }
}
</script>

<template>
  <main>
    <AuthForgotPassword
      v-model:email="email"
      layout="simple"
      title="Redefinir senha"
      description="Informe o e-mail da sua conta. Vamos enviar um link para você criar uma nova senha."
      submit-label="Enviar link"
      back-href="/login"
      :status="status"
      :error="error"
      :processing="processing"
      @submit="submit"
    >
      <template #logo><AppBrand /></template>
    </AuthForgotPassword>
  </main>
</template>
