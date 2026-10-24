<script setup lang="ts">
import { ListChecks, Pencil, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import type { DefaultActivity, Priority } from '~/composables/useActivities';
import { Alert } from '~/ui/alert';
import { Badge } from '~/ui/badge';
import { Button } from '~/ui/button';
import { EmptyState } from '~/ui/empty-state';
import Input from '~/ui/input/Input.vue';
import { PageHeader } from '~/ui/page-header';
import { Select } from '~/ui/select';
import { Skeleton } from '~/ui/skeleton';
import { toast } from '~/ui/toast';

useHead({ title: 'Atividades padrão' });

const { activities, load, save, remove } = useActivities();

const priorityLabels: Record<Priority, string> = {
  high: 'Prioridade alta',
  medium: 'Prioridade média',
  low: 'Prioridade baixa',
};
const priorityIntents: Record<Priority, 'primary' | 'neutral' | 'warning'> = {
  high: 'primary',
  medium: 'warning',
  low: 'neutral',
};
const priorityOptions = (Object.keys(priorityLabels) as Priority[]).map((value) => ({
  value,
  label: priorityLabels[value],
}));

const loading = ref(true);
const loadError = ref('');
const description = ref('');
const priority = ref<Priority>('medium');
const createError = ref('');
const creating = ref(false);
const editingId = ref<string | null>(null);
const editDescription = ref('');
const editPriority = ref<Priority>('medium');
const editError = ref('');
const savingEdit = ref(false);
const deleting = ref<DefaultActivity | null>(null);
const removing = ref(false);

async function create() {
  if (creating.value) return;
  createError.value = '';
  creating.value = true;
  try {
    await save({ description: description.value, priority: priority.value });
    description.value = '';
    toast.success('Atividade salva.');
  } catch (exception) {
    createError.value = errorMessage(exception);
  } finally {
    creating.value = false;
  }
}

function startEditing(activity: DefaultActivity) {
  editingId.value = activity.id;
  editDescription.value = activity.description;
  editPriority.value = activity.priority;
  editError.value = '';
}

async function saveEdit() {
  if (!editingId.value || savingEdit.value) return;
  editError.value = '';
  savingEdit.value = true;
  try {
    await save(
      { description: editDescription.value, priority: editPriority.value },
      editingId.value,
    );
    editingId.value = null;
    toast.success('Atividade salva.');
  } catch (exception) {
    editError.value = errorMessage(exception);
  } finally {
    savingEdit.value = false;
  }
}

async function confirmRemove() {
  if (!deleting.value) return;
  removing.value = true;
  try {
    await remove(deleting.value.id);
    toast.success('Atividade excluída.');
    deleting.value = null;
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    removing.value = false;
  }
}

async function loadActivities() {
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

onMounted(loadActivities);
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      title="Atividades padrão"
      description="Completam os dias úteis até a carga horária. Prioridade alta entra 3 vezes no rodízio, média 2 e baixa 1."
      :bordered="false"
    />

    <form
      class="flex flex-col gap-3 rounded-xl border border-border bg-card p-5 sm:flex-row sm:items-end"
      @submit.prevent="create"
    >
      <Input
        id="new-activity"
        v-model="description"
        label="Nova atividade"
        placeholder="Ex.: Escrita da dissertação"
        :maxlength="255"
        :disabled="creating"
        required
        class="min-w-0 flex-1"
      />
      <Select
        id="new-activity-priority"
        v-model="priority"
        label="Prioridade"
        :options="priorityOptions"
        class="sm:w-56"
      />
      <Button type="submit" :icon-leading="Plus" :loading="creating">Adicionar</Button>
    </form>
    <Alert v-if="createError" intent="danger" variant="soft">{{ createError }}</Alert>

    <div v-if="loadError" class="flex flex-col items-start gap-4">
      <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
      <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadActivities">
        Tentar novamente
      </Button>
    </div>
    <div v-else-if="loading" role="status" class="flex flex-col gap-4">
      <span class="sr-only">Carregando as atividades…</span>
      <Skeleton class="h-6 w-4/5" /><Skeleton class="h-6 w-3/5" />
    </div>
    <EmptyState
      v-else-if="!activities.length"
      title="Nenhuma atividade cadastrada ainda"
      description="Sem atividades padrão, o Clips só lança a grade semanal ao completar o mês."
      class="min-h-56"
    >
      <template #icon><ListChecks aria-hidden="true" /></template>
    </EmptyState>
    <ul v-else class="divide-y divide-border rounded-xl border border-border bg-card">
      <li v-for="activity in activities" :key="activity.id" class="px-4 py-3">
        <form
          v-if="editingId === activity.id"
          class="flex flex-col gap-3 sm:flex-row sm:items-end"
          @submit.prevent="saveEdit"
        >
          <Input
            :id="`edit-${activity.id}`"
            v-model="editDescription"
            label="Atividade"
            :maxlength="255"
            :error="editError"
            required
            class="min-w-0 flex-1"
            @keydown.esc="editingId = null"
          />
          <Select
            :id="`edit-priority-${activity.id}`"
            v-model="editPriority"
            label="Prioridade"
            :options="priorityOptions"
            class="sm:w-56"
          />
          <div class="flex gap-2">
            <Button type="button" variant="ghost" @click="editingId = null">Cancelar</Button>
            <Button type="submit" :loading="savingEdit">Salvar</Button>
          </div>
        </form>
        <div v-else class="flex items-center gap-3">
          <span class="min-w-0 flex-1 text-body-sm wrap-anywhere">{{ activity.description }}</span>
          <Badge :intent="priorityIntents[activity.priority]" size="sm">
            {{ priorityLabels[activity.priority] }}
          </Badge>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            icon-only
            :icon-leading="Pencil"
            :aria-label="`Editar ${activity.description}`"
            @click="startEditing(activity)"
          />
          <Button
            type="button"
            variant="ghost"
            tone="danger"
            size="sm"
            icon-only
            :icon-leading="Trash2"
            :aria-label="`Excluir ${activity.description}`"
            @click="deleting = activity"
          />
        </div>
      </li>
    </ul>

    <ConfirmDialog
      :open="deleting !== null"
      :title="`Excluir “${deleting?.description ?? ''}”?`"
      description="Os lançamentos que ela já gerou continuam no calendário."
      confirm-label="Excluir"
      tone="danger"
      :loading="removing"
      @update:open="(open) => !open && (deleting = null)"
      @confirm="confirmRemove"
    />
  </div>
</template>
