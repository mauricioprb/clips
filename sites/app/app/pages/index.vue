<script setup lang="ts">
import { FileText, LogOut, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import AppShellStacked from '~/ui/app-shell/AppShellStacked.vue';
import { PageHeader } from '~/ui/page-header';
import { DropdownMenuItem } from '~/ui/dropdown-menu';
import { EmptyState } from '~/ui/empty-state';
import Input from '~/ui/input/Input.vue';
import { Label } from '~/ui/label';
import { Skeleton } from '~/ui/skeleton';

const { user, logout } = useAuth();
const { notes, load, create, remove } = useNotes();

const title = ref('');
const error = ref('');
const loadError = ref('');
const loading = ref(true);
const saving = ref(false);
const removingId = ref<string | null>(null);
const signingOut = ref(false);

async function loadNotes() {
  loading.value = true;
  loadError.value = '';
  try {
    await load();
  } catch (exception) {
    loadError.value = errorMessage(exception);
  } finally {
    loading.value = false;
  }
}

async function createNote() {
  if (saving.value || !title.value.trim()) return;
  saving.value = true;
  error.value = '';
  try {
    await create(title.value);
    title.value = '';
    loadError.value = '';
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    saving.value = false;
  }
}

async function removeNote(id: string) {
  if (removingId.value) return;
  removingId.value = id;
  error.value = '';
  try {
    await remove(id);
    loadError.value = '';
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    removingId.value = null;
  }
}

async function signOut() {
  if (signingOut.value) return;
  signingOut.value = true;
  error.value = '';
  try {
    await logout();
    await navigateTo('/login');
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    signingOut.value = false;
  }
}

onMounted(loadNotes);
</script>

<template>
  <AppShellStacked
    :navigation="[]"
    :user="user ? { name: user.name, email: user.email } : undefined"
    user-menu-label="Abrir menu da conta"
    menu-label="Abrir navegação"
    navigation-label="Navegação principal"
    content-class="mx-auto w-full max-w-3xl gap-8 py-8 sm:py-12"
  >
    <template #logo><AppBrand /></template>
    <template #header-actions><ThemeToggle /></template>
    <template #user-menu>
      <DropdownMenuItem :disabled="signingOut" @select="signOut">
        <LogOut class="size-4" aria-hidden="true" /> Sair
      </DropdownMenuItem>
    </template>

    <PageHeader
      title="Minhas notas"
      description="Ideias e lembretes, em um lugar só."
      :bordered="false"
    />

    <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>

    <form
      class="flex flex-col gap-1.5 rounded-xl border border-border bg-card p-5 sm:p-6"
      @submit.prevent="createNote"
    >
      <Label for="title">Nova nota</Label>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
        <Input
          id="title"
          v-model="title"
          name="title"
          placeholder="O que você quer lembrar?"
          :maxlength="255"
          :disabled="saving"
          required
          class="min-w-0 flex-1"
        />
        <Button
          type="submit"
          :loading="saving"
          :disabled="!title.trim()"
          :icon-leading="Plus"
          loading-text="Salvando…"
          >Adicionar nota</Button
        >
      </div>
    </form>

    <section class="min-w-0" aria-labelledby="saved-notes-title" :aria-busy="loading">
      <div class="mb-2 flex items-center justify-between gap-3 border-b border-border pb-4">
        <h2 id="saved-notes-title" class="text-body-md font-semibold text-foreground">
          Notas salvas
        </h2>
        <span v-if="!loading && !loadError" class="text-caption text-muted-foreground" role="status"
          >{{ notes.length }} {{ notes.length === 1 ? 'nota' : 'notas' }}</span
        >
      </div>
      <div v-if="loading" role="status" class="space-y-5 py-6">
        <span class="sr-only">Carregando suas notas…</span>
        <Skeleton class="h-5 w-4/5" /><Skeleton class="h-5 w-3/5" /><Skeleton class="h-5 w-2/3" />
      </div>
      <div v-else-if="loadError" class="py-5">
        <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
        <Button
          type="button"
          variant="outline"
          :icon-leading="RefreshCw"
          class="mt-4"
          @click="loadNotes"
          >Tentar novamente</Button
        >
      </div>
      <EmptyState
        v-else-if="!notes.length"
        title="Nenhuma nota por aqui"
        description="Escreva sua primeira nota no campo acima."
        class="min-h-56"
      >
        <template #icon><FileText aria-hidden="true" /></template>
      </EmptyState>
      <ul v-else class="divide-y divide-border">
        <li v-for="note in notes" :key="note.id" class="flex items-start gap-3 py-5">
          <FileText class="mt-2 size-4 shrink-0 text-primary-text" aria-hidden="true" />
          <p class="min-w-0 flex-1 py-1.5 text-body-sm leading-relaxed wrap-anywhere">
            {{ note.title }}
          </p>
          <Button
            type="button"
            variant="ghost"
            tone="danger"
            size="sm"
            icon-only
            :icon-leading="Trash2"
            :aria-label="`Apagar nota: ${note.title}`"
            :loading="removingId === note.id"
            :disabled="removingId !== null"
            @click="removeNote(note.id)"
          />
        </li>
      </ul>
      <p class="border-t border-border pt-4 text-caption text-muted-foreground">
        Só você tem acesso a estas notas.
      </p>
    </section>
  </AppShellStacked>
</template>
