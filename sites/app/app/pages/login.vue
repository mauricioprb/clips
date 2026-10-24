<script setup lang="ts">
import AuthLogin from '~/ui/auth-login/AuthLogin.vue';

const { login } = useAuth();

const email = ref('');
const password = ref('');
const error = ref('');
const processing = ref(false);

async function submit() {
  if (processing.value) return;
  error.value = '';
  processing.value = true;
  try {
    await login(email.value, password.value);
    await navigateTo('/');
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    processing.value = false;
  }
}
</script>

<template>
  <main>
    <AuthLogin
      v-model:email="email"
      v-model:password="password"
      :processing="processing"
      :error="error"
      @submit="submit"
    />
  </main>
</template>
