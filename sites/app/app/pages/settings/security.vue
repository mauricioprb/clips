<script setup lang="ts">
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import { PageHeader } from '~/ui/page-header';
import { SettingsSection } from '~/ui/settings-section';
import { toast } from '~/ui/toast';

useHead({ title: 'Conta e senha' });

const { user, updateEmail, changePassword } = useAuth();
const { deleteOwnAccount } = useMembers();

const email = ref(user.value?.email ?? '');
const emailPassword = ref('');
const emailError = ref('');
const savingEmail = ref(false);

const currentPassword = ref('');
const newPassword = ref('');
const confirmation = ref('');
const passwordError = ref('');
const savingPassword = ref(false);

const confirmDelete = ref(false);
const deleteEmail = ref('');
const deleteError = ref('');
const deleting = ref(false);

async function saveEmail() {
  if (savingEmail.value) return;
  emailError.value = '';
  savingEmail.value = true;
  try {
    await updateEmail(email.value, emailPassword.value);
    emailPassword.value = '';
    toast.success('E-mail alterado.');
  } catch (exception) {
    emailError.value = errorMessage(exception);
  } finally {
    savingEmail.value = false;
  }
}

async function savePassword() {
  if (savingPassword.value) return;
  passwordError.value = '';

  if (newPassword.value !== confirmation.value) {
    passwordError.value = 'As senhas não conferem.';
    return;
  }

  savingPassword.value = true;
  try {
    await changePassword(newPassword.value, currentPassword.value);
    currentPassword.value = '';
    newPassword.value = '';
    confirmation.value = '';
    toast.success('Senha alterada.');
  } catch (exception) {
    passwordError.value = errorMessage(exception);
  } finally {
    savingPassword.value = false;
  }
}

async function deleteAccount() {
  deleteError.value = '';
  deleting.value = true;
  try {
    await deleteOwnAccount(deleteEmail.value);
    await navigateTo('/login');
  } catch (exception) {
    deleteError.value = errorMessage(exception);
  } finally {
    deleting.value = false;
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader title="Configurações" :bordered="false" />
    <SettingsNav />

    <SettingsSection
      title="E-mail"
      description="É o e-mail usado para entrar e para recuperar a senha."
    >
      <form class="flex max-w-md flex-col gap-4 px-5 py-5 sm:px-6" @submit.prevent="saveEmail">
        <Alert v-if="emailError" intent="danger" variant="soft">{{ emailError }}</Alert>
        <Input
          id="account-email"
          v-model="email"
          label="E-mail"
          type="email"
          autocomplete="email"
          required
        />
        <Input
          id="account-email-password"
          v-model="emailPassword"
          label="Senha atual"
          type="password"
          autocomplete="current-password"
          required
        />
        <Button type="submit" class="self-start" :loading="savingEmail">Alterar e-mail</Button>
      </form>
    </SettingsSection>

    <SettingsSection title="Senha" description="Informe a senha atual para definir uma nova.">
      <form class="flex max-w-md flex-col gap-4 px-5 py-5 sm:px-6" @submit.prevent="savePassword">
        <Alert v-if="passwordError" intent="danger" variant="soft">{{ passwordError }}</Alert>
        <Input
          id="current-password"
          v-model="currentPassword"
          label="Senha atual"
          type="password"
          autocomplete="current-password"
          required
        />
        <Input
          id="new-password"
          v-model="newPassword"
          label="Nova senha"
          type="password"
          autocomplete="new-password"
          minlength="8"
          hint="Use pelo menos 8 caracteres."
          required
        />
        <Input
          id="new-password-confirmation"
          v-model="confirmation"
          label="Confirme a nova senha"
          type="password"
          autocomplete="new-password"
          minlength="8"
          required
        />
        <Button type="submit" class="self-start" :loading="savingPassword">Alterar senha</Button>
      </form>
    </SettingsSection>

    <SettingsSection
      title="Excluir conta"
      description="Apaga a sua conta e todos os lançamentos. Não é possível desfazer."
    >
      <div class="px-5 py-5 sm:px-6">
        <Button type="button" tone="danger" @click="confirmDelete = true">Excluir conta</Button>
      </div>
    </SettingsSection>

    <ConfirmDialog
      v-model:open="confirmDelete"
      title="Excluir a sua conta?"
      description="Digite o e-mail da sua conta para confirmar. Todos os seus lançamentos serão apagados."
      confirm-label="Excluir definitivamente"
      tone="danger"
      :loading="deleting"
      @confirm="deleteAccount"
    >
      <Alert v-if="deleteError" intent="danger" variant="soft">{{ deleteError }}</Alert>
      <Input
        id="delete-email"
        v-model="deleteEmail"
        label="E-mail da conta"
        type="email"
        autocomplete="off"
        required
      />
    </ConfirmDialog>
  </div>
</template>
