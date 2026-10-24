<script setup lang="ts">
import AuthSignup from '~/ui/auth-signup/AuthSignup.vue';

const { signup } = useAuth();

const name = ref('');
const email = ref('');
const password = ref('');
const error = ref('');
const processing = ref(false);

async function submit() {
  if (processing.value) return;
  error.value = '';
  processing.value = true;
  try {
    await signup(name.value, email.value, password.value);
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
    <AuthSignup
      v-model:name="name"
      v-model:email="email"
      v-model:password="password"
      :processing="processing"
      :error="error"
      @submit="submit"
    />
  </main>
</template>
