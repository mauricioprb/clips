<script setup lang="ts">
import AuthCard from '~/ui/auth-card/AuthCard.vue';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import { Skeleton } from '~/ui/skeleton';

definePageMeta({ layout: false });
useHead({ title: 'Convite' });

const route = useRoute();
const { user, acceptInvitation, changePassword } = useAuth();

const membershipId = String(route.query.membershipId ?? '');
const userId = String(route.query.userId ?? '');
const secret = String(route.query.secret ?? '');
const state = ref<'accepting' | 'password' | 'failed'>('accepting');
const password = ref('');
const confirmation = ref('');
const error = ref('');
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
    await changePassword(password.value);
    await navigateTo('/');
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    processing.value = false;
  }
}

onMounted(async () => {
  if (!membershipId || !userId || !secret) {
    error.value = 'Este link de convite está incompleto. Peça um novo à coordenação.';
    state.value = 'failed';
    return;
  }

  try {
    await acceptInvitation(membershipId, userId, secret);
    state.value = 'password';
  } catch (exception) {
    error.value = errorMessage(exception);
    state.value = 'failed';
  }
});
</script>

<template>
  <main>
    <AuthCard
      layout="simple"
      :title="state === 'failed' ? 'Convite indisponível' : 'Crie sua senha'"
      :description="
        state === 'failed'
          ? undefined
          : `Olá${user?.name ? `, ${user.name}` : ''}. Você recebeu um convite para o Clips. Crie uma senha para começar.`
      "
    >
      <template #logo><AppBrand /></template>
      <div v-if="state === 'accepting'" role="status" class="flex flex-col gap-4">
        <span class="sr-only">Conferindo o convite…</span>
        <Skeleton class="h-9 w-full" /><Skeleton class="h-9 w-full" />
      </div>
      <Alert v-else-if="state === 'failed'" intent="danger" variant="soft">{{ error }}</Alert>
      <form v-else class="flex flex-col gap-5" @submit.prevent="submit">
        <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>
        <Input
          id="password"
          v-model="password"
          label="Senha"
          type="password"
          name="password"
          autocomplete="new-password"
          minlength="8"
          hint="Use pelo menos 8 caracteres."
          :disabled="processing"
          required
        />
        <Input
          id="confirmation"
          v-model="confirmation"
          label="Confirme a senha"
          type="password"
          name="confirmation"
          autocomplete="new-password"
          minlength="8"
          :disabled="processing"
          required
        />
        <Button type="submit" size="lg" class="w-full" :loading="processing">
          Criar senha e entrar
        </Button>
      </form>
      <template v-if="state === 'failed'" #footer>
        <NuxtLink
          to="/login"
          class="font-semibold text-primary-text underline-offset-4 hover:underline"
        >
          Ir para o login
        </NuxtLink>
      </template>
    </AuthCard>
  </main>
</template>
