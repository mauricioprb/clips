<script setup lang="ts">
import { ChevronLeft, ChevronRight, Plus, RefreshCw, Trash2 } from '@lucide/vue';
import type { Holiday, HolidayRecurrence } from '~/composables/useHolidays';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import { PageHeader } from '~/ui/page-header';
import { Select } from '~/ui/select';
import { SettingsSection } from '~/ui/settings-section';
import { Skeleton } from '~/ui/skeleton';
import { toast } from '~/ui/toast';

useHead({ title: 'Feriados' });

const { list, load, create, remove } = useHolidays();

const recurrenceOptions: { value: HolidayRecurrence; label: string }[] = [
  { value: 'yearly', label: 'Todo ano nesta data' },
  { value: 'once', label: 'Só nesta data' },
  { value: 'corpus_christi', label: 'Corpus Christi (data móvel)' },
];

const year = ref(new Date().getFullYear());
const loading = ref(true);
const loadError = ref('');
const name = ref('');
const recurrence = ref<HolidayRecurrence>('yearly');
const date = ref('');
const formError = ref('');
const saving = ref(false);
const removingId = ref<string | null>(null);

function describe(holiday: Holiday): string {
  if (holiday.recurrence === 'corpus_christi') return 'Corpus Christi, data móvel';
  if (!holiday.date) return '';

  return holiday.recurrence === 'yearly'
    ? `Todo ano em ${formatDayAndMonth(holiday.date)}`
    : formatDate(holiday.date);
}

async function loadYear() {
  loading.value = true;
  loadError.value = '';
  try {
    await load(year.value);
  } catch (exception) {
    loadError.value = errorMessage(exception);
  } finally {
    loading.value = false;
  }
}

async function submit() {
  if (saving.value) return;
  formError.value = '';
  saving.value = true;
  try {
    await create(
      {
        name: name.value,
        recurrence: recurrence.value,
        date: recurrence.value === 'corpus_christi' ? null : date.value,
      },
      year.value,
    );
    name.value = '';
    date.value = '';
    toast.success('Feriado adicionado.');
  } catch (exception) {
    formError.value = errorMessage(exception);
  } finally {
    saving.value = false;
  }
}

async function removeHoliday(holiday: Holiday) {
  if (removingId.value) return;
  removingId.value = holiday.id;
  try {
    await remove(holiday.id, year.value);
    toast.success('Feriado removido.');
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    removingId.value = null;
  }
}

watch(year, loadYear, { immediate: true });
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader title="Configurações" :bordered="false" />
    <SettingsNav />

    <SettingsSection
      title="Seus feriados"
      description="Estaduais e municipais que valem na sua instituição. Os nacionais já estão incluídos."
    >
      <div class="flex flex-col gap-3 px-5 py-5 sm:px-6">
        <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="submit">
          <Input
            id="holiday-name"
            v-model="name"
            label="Nome"
            placeholder="Ex.: Aniversário da cidade"
            :maxlength="120"
            required
            class="min-w-0 flex-1"
          />
          <Select
            id="holiday-recurrence"
            v-model="recurrence"
            label="Repetição"
            :options="recurrenceOptions"
            class="sm:w-60"
          />
          <Input
            v-if="recurrence !== 'corpus_christi'"
            id="holiday-date"
            v-model="date"
            label="Data"
            type="date"
            required
            class="sm:w-44"
          />
          <Button type="submit" :icon-leading="Plus" :loading="saving">Adicionar</Button>
        </form>
        <Alert v-if="formError" intent="danger" variant="soft">{{ formError }}</Alert>
      </div>

      <p
        v-if="list && !list.holidays.length"
        class="px-5 py-5 text-body-sm text-muted-foreground sm:px-6"
      >
        Nenhum feriado próprio.
      </p>
      <ul v-else-if="list" class="divide-y divide-border">
        <li
          v-for="holiday in list.holidays"
          :key="holiday.id"
          class="flex items-center gap-3 px-5 py-2 sm:px-6"
        >
          <span class="flex min-w-0 flex-1 flex-col">
            <span class="text-body-sm font-medium wrap-anywhere">{{ holiday.name }}</span>
            <span class="text-caption text-muted-foreground">{{ describe(holiday) }}</span>
          </span>
          <Button
            type="button"
            variant="ghost"
            tone="danger"
            size="sm"
            icon-only
            :icon-leading="Trash2"
            :aria-label="`Remover ${holiday.name}`"
            :loading="removingId === holiday.id"
            :disabled="removingId !== null"
            @click="removeHoliday(holiday)"
          />
        </li>
      </ul>
    </SettingsSection>

    <SettingsSection
      :title="`Feriados de ${year}`"
      description="Nacionais e os seus, já considerados no calendário."
    >
      <div class="flex items-center gap-1 px-5 py-3 sm:px-6">
        <Button
          type="button"
          variant="ghost"
          size="sm"
          icon-only
          :icon-leading="ChevronLeft"
          aria-label="Ano anterior"
          @click="year--"
        />
        <span class="text-body-sm font-semibold tabular-nums">{{ year }}</span>
        <Button
          type="button"
          variant="ghost"
          size="sm"
          icon-only
          :icon-leading="ChevronRight"
          aria-label="Próximo ano"
          @click="year++"
        />
      </div>
      <div v-if="loadError" class="flex flex-col items-start gap-4 px-5 py-5 sm:px-6">
        <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
        <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadYear">
          Tentar novamente
        </Button>
      </div>
      <div v-else-if="loading" role="status" class="flex flex-col gap-2 px-5 py-5 sm:px-6">
        <span class="sr-only">Carregando os feriados…</span>
        <Skeleton class="h-5 w-3/5" /><Skeleton class="h-5 w-2/5" />
      </div>
      <ul v-else-if="list" class="divide-y divide-border">
        <li
          v-for="holiday in list.calendar"
          :key="holiday.date"
          class="flex gap-4 px-5 py-2 text-body-sm sm:px-6"
        >
          <span class="w-24 shrink-0 text-muted-foreground tabular-nums">
            {{ formatDate(holiday.date) }}
          </span>
          <span>{{ holiday.name }}</span>
        </li>
      </ul>
    </SettingsSection>
  </div>
</template>
