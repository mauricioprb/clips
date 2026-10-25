<script setup lang="ts">
import { Pencil, Trash2 } from '@lucide/vue';
import type { MonthDay, TimeEntry } from '~/composables/useMonth';
import { Alert } from '~/ui/alert';
import { Badge } from '~/ui/badge';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import { Sheet, SheetContent } from '~/ui/sheet';
import { TimePicker } from '~/ui/time-picker';
import { toast } from '~/ui/toast';

const props = defineProps<{
  day: MonthDay | null;
  dailyTargetMinutes: number;
  suggestions: string[];
}>();
const emit = defineEmits<{ close: [] }>();

const { saveEntry, removeEntry } = useMonth();
const editing = ref<TimeEntry | null>(null);
const start = ref<string | undefined>('08:00');
const end = ref<string | undefined>('09:00');
const description = ref('');
const error = ref('');
const saving = ref(false);
const removingId = ref<string | null>(null);
const shownDay = ref(props.day);

watch(
  () => props.day,
  (day) => day && (shownDay.value = day),
);

const title = computed(() => (shownDay.value ? formatDayTitle(shownDay.value.date) : ''));
const subtitle = computed(() => {
  if (!shownDay.value) return '';

  if (!shownDay.value.isWorkday) {
    return `${shownDay.value.holiday ?? 'Fim de semana'}. Este dia não entra no relatório.`;
  }

  return `${formatDuration(shownDay.value.minutes)} de ${formatDuration(props.dailyTargetMinutes)}`;
});

function resetForm() {
  const lastEnd = props.day?.entries.at(-1)?.endMinute;
  const startMinute = lastEnd !== undefined && lastEnd < 1380 ? lastEnd : 480;

  editing.value = null;
  start.value = minutesToClock(startMinute);
  end.value = minutesToClock(startMinute + 60);
  description.value = '';
  error.value = '';
}

function edit(entry: TimeEntry) {
  editing.value = entry;
  start.value = minutesToClock(entry.startMinute);
  end.value = minutesToClock(entry.endMinute);
  description.value = entry.description;
  error.value = '';
}

async function submit() {
  if (!props.day || saving.value) return;
  error.value = '';
  saving.value = true;
  try {
    await saveEntry(
      {
        date: props.day.date,
        startMinute: clockToMinutes(start.value) ?? -1,
        endMinute: end.value === '00:00' ? 1440 : (clockToMinutes(end.value) ?? -1),
        description: description.value,
      },
      editing.value?.id,
    );
    toast.success('Lançamento salvo.');
    resetForm();
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    saving.value = false;
  }
}

async function remove(entry: TimeEntry) {
  if (removingId.value) return;
  removingId.value = entry.id;
  error.value = '';
  try {
    await removeEntry(entry);
    toast.success('Lançamento excluído.');
    if (editing.value?.id === entry.id) resetForm();
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    removingId.value = null;
  }
}

watch(
  () => props.day?.date,
  (date) => date && resetForm(),
  { immediate: true },
);
</script>

<template>
  <Sheet :open="day !== null" @update:open="(open) => !open && emit('close')">
    <SheetContent
      v-if="shownDay"
      side="right"
      :title="title"
      :description="subtitle"
      close-label="Fechar"
      class="w-full max-w-md overflow-y-auto sm:max-w-md"
    >
      <div class="flex flex-col gap-1 pr-8" aria-hidden="true">
        <p class="text-heading-5 font-semibold text-foreground">{{ title }}</p>
        <p class="text-body-sm text-muted-foreground">{{ subtitle }}</p>
      </div>

      <p v-if="shownDay.entries.length === 0" class="text-body-sm text-muted-foreground">
        Nenhum lançamento neste dia.
      </p>
      <ul v-else class="-mx-2 flex flex-col gap-1">
        <li
          v-for="entry in shownDay.entries"
          :key="entry.id"
          class="flex items-start gap-3 rounded-md px-2 py-2"
          :class="editing?.id === entry.id ? 'bg-primary-soft' : ''"
        >
          <span class="w-24 shrink-0 pt-1 text-body-sm text-muted-foreground tabular-nums">
            {{ minutesToClock(entry.startMinute) }}-{{ minutesToClock(entry.endMinute) }}
          </span>
          <span class="flex min-w-0 flex-1 flex-col items-start gap-1 pt-1">
            <span class="text-body-sm font-medium wrap-anywhere">{{ entry.description }}</span>
            <Badge v-if="entry.source !== 'manual'" intent="neutral" size="sm">
              {{ entry.source === 'schedule' ? 'Grade' : 'Automático' }}
            </Badge>
          </span>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            icon-only
            :icon-leading="Pencil"
            :aria-label="`Editar ${entry.description}`"
            @click="edit(entry)"
          />
          <Button
            type="button"
            variant="ghost"
            tone="danger"
            size="sm"
            icon-only
            :icon-leading="Trash2"
            :aria-label="`Excluir ${entry.description}`"
            :loading="removingId === entry.id"
            :disabled="removingId !== null"
            @click="remove(entry)"
          />
        </li>
      </ul>

      <form class="flex flex-col gap-4 border-t border-border pt-5" @submit.prevent="submit">
        <h3 class="text-body-md font-semibold">
          {{ editing ? 'Editar lançamento' : 'Novo lançamento' }}
        </h3>
        <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>
        <div class="grid grid-cols-2 gap-3">
          <TimePicker id="entry-start" v-model="start" label="Início" :step="15" required />
          <TimePicker id="entry-end" v-model="end" label="Fim" :step="15" required />
        </div>
        <Input
          id="entry-description"
          v-model="description"
          label="Atividade"
          placeholder="Ex.: Leitura de artigos"
          list="entry-suggestions"
          :maxlength="255"
          required
        />
        <datalist id="entry-suggestions">
          <option v-for="suggestion in suggestions" :key="suggestion" :value="suggestion" />
        </datalist>
        <div class="flex flex-wrap justify-end gap-2">
          <Button v-if="editing" type="button" variant="ghost" @click="resetForm">
            Cancelar edição
          </Button>
          <Button type="submit" :loading="saving">
            {{ editing ? 'Salvar alterações' : 'Adicionar' }}
          </Button>
        </div>
      </form>
    </SheetContent>
  </Sheet>
</template>
