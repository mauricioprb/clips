<script setup lang="ts">
import { Plus, RefreshCw, Trash2 } from '@lucide/vue';
import type { WeeklySlot } from '~/composables/useSchedule';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import { DatePicker } from '~/ui/date-picker';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/ui/dialog';
import Input from '~/ui/input/Input.vue';
import { PageHeader } from '~/ui/page-header';
import { Select } from '~/ui/select';
import { Skeleton } from '~/ui/skeleton';
import { toast } from '~/ui/toast';

useHead({ title: 'Grade semanal' });

const { slots, load, save, remove } = useSchedule();
const { profile, load: loadProfile } = useProfile();

const weekdays = [
  'Segunda-feira',
  'Terça-feira',
  'Quarta-feira',
  'Quinta-feira',
  'Sexta-feira',
].map((label, index) => ({ value: index + 1, label }));
const weekdayOptions = weekdays.map(({ value, label }) => ({ value: String(value), label }));

const loading = ref(true);
const loadError = ref('');
const dialogOpen = ref(false);
const confirmDelete = ref(false);
const editing = ref<WeeklySlot | null>(null);
const weekday = ref('1');
const start = ref('08:00');
const end = ref('10:00');
const description = ref('');
const validFrom = ref('');
const validUntil = ref('');
const validFromValue = isoDateModel(validFrom);
const validUntilValue = isoDateModel(validUntil);
const formError = ref('');
const saving = ref(false);
const deleting = ref(false);

const rows = computed(() =>
  weekdays.map((option) => {
    const daySlots = slots.value.filter((slot) => slot.weekday === option.value);

    return {
      ...option,
      slots: daySlots,
      minutes: daySlots.reduce((sum, slot) => sum + slot.endMinute - slot.startMinute, 0),
    };
  }),
);
const scheduledMinutes = computed(() => rows.value.reduce((sum, row) => sum + row.minutes, 0));
const weeklyWorkload = computed(() => profile.value?.weeklyWorkloadMinutes ?? 0);

function validity(slot: WeeklySlot): string | null {
  if (slot.validFrom && slot.validUntil) {
    return `${formatDate(slot.validFrom)} a ${formatDate(slot.validUntil)}`;
  }

  if (slot.validFrom) return `A partir de ${formatDate(slot.validFrom)}`;

  return slot.validUntil ? `Até ${formatDate(slot.validUntil)}` : null;
}

function openNew(day = 1, startMinute = 480) {
  editing.value = null;
  weekday.value = String(day);
  start.value = minutesToClock(startMinute);
  end.value = minutesToClock(Math.min(startMinute + 120, 1440));
  description.value = '';
  validFrom.value = '';
  validUntil.value = '';
  formError.value = '';
  dialogOpen.value = true;
}

function openEdit(slot: WeeklySlot) {
  editing.value = slot;
  weekday.value = String(slot.weekday);
  start.value = minutesToClock(slot.startMinute);
  end.value = minutesToClock(slot.endMinute);
  description.value = slot.description;
  validFrom.value = slot.validFrom ?? '';
  validUntil.value = slot.validUntil ?? '';
  formError.value = '';
  dialogOpen.value = true;
}

async function submit() {
  if (saving.value) return;
  formError.value = '';
  saving.value = true;
  try {
    await save(
      {
        weekday: Number(weekday.value),
        startMinute: clockToMinutes(start.value) ?? -1,
        endMinute: end.value === '00:00' ? 1440 : (clockToMinutes(end.value) ?? -1),
        description: description.value,
        validFrom: validFrom.value || null,
        validUntil: validUntil.value || null,
      },
      editing.value?.id,
    );
    toast.success('Horário salvo.');
    dialogOpen.value = false;
  } catch (exception) {
    formError.value = errorMessage(exception);
  } finally {
    saving.value = false;
  }
}

async function removeEditing() {
  if (!editing.value) return;
  deleting.value = true;
  try {
    await remove(editing.value.id);
    toast.success('Horário excluído.');
    confirmDelete.value = false;
    dialogOpen.value = false;
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    deleting.value = false;
  }
}

async function loadAll() {
  loading.value = true;
  loadError.value = '';
  try {
    await Promise.all([load(), loadProfile()]);
  } catch (exception) {
    loadError.value = errorMessage(exception);
  } finally {
    loading.value = false;
  }
}

onMounted(loadAll);
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      title="Grade semanal"
      description="Compromissos fixos, como aulas e reuniões. Ao completar o mês, eles são lançados nos dias em que valem."
      :bordered="false"
    >
      <template #actions>
        <Button type="button" :icon-leading="Plus" @click="openNew()">Adicionar horário</Button>
      </template>
    </PageHeader>

    <div v-if="loadError" class="flex flex-col items-start gap-4">
      <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
      <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadAll">
        Tentar novamente
      </Button>
    </div>
    <div v-else-if="loading" role="status" class="flex flex-col gap-3">
      <span class="sr-only">Carregando a grade semanal…</span>
      <Skeleton class="h-5 w-96" />
      <Skeleton class="h-96 w-full" />
    </div>
    <template v-else>
      <p class="text-body-sm text-muted-foreground">
        <template v-if="scheduledMinutes > 0">
          A grade ocupa {{ formatDuration(scheduledMinutes) }} das
          {{ formatDuration(weeklyWorkload) }} semanais. As outras
          {{ formatDuration(Math.max(weeklyWorkload - scheduledMinutes, 0)) }} são completadas com
          as atividades padrão.
        </template>
        <template v-else>
          Nenhum horário fixo ainda. Sem grade, a carga semanal inteira é completada com as
          atividades padrão.
        </template>
      </p>

      <WeekTimetable
        class="hidden md:block"
        :slots="slots"
        :weekdays="weekdays"
        @create="(day, minute) => openNew(day, minute)"
        @edit="openEdit"
      />

      <ul class="divide-y divide-border rounded-xl border border-border bg-card md:hidden">
        <li v-for="row in rows" :key="row.value" class="flex flex-col gap-3 p-4">
          <div class="flex items-baseline justify-between gap-2">
            <h2 class="text-body-sm font-semibold">{{ row.label }}</h2>
            <p class="text-caption text-muted-foreground tabular-nums">
              {{ row.minutes ? formatDuration(row.minutes) : 'Livre' }}
            </p>
          </div>
          <button
            v-for="slot in row.slots"
            :key="slot.id"
            type="button"
            class="flex w-full items-baseline gap-3 rounded-md border border-border px-3 py-2 text-left transition-colors hover:border-primary hover:bg-primary-soft"
            :aria-label="`Editar ${slot.description} na ${row.label.toLowerCase()}`"
            @click="openEdit(slot)"
          >
            <span class="w-28 shrink-0 text-body-sm font-semibold tabular-nums">
              {{ minutesToClock(slot.startMinute) }}-{{ minutesToClock(slot.endMinute) }}
            </span>
            <span class="flex min-w-0 flex-col">
              <span class="text-body-sm wrap-anywhere">{{ slot.description }}</span>
              <span v-if="validity(slot)" class="text-caption text-muted-foreground">
                {{ validity(slot) }}
              </span>
            </span>
          </button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            :icon-leading="Plus"
            class="self-start"
            @click="openNew(row.value)"
          >
            Adicionar horário
          </Button>
        </li>
      </ul>
    </template>

    <Dialog v-model:open="dialogOpen">
      <DialogContent close-label="Fechar">
        <DialogHeader>
          <DialogTitle>{{ editing ? 'Editar horário' : 'Novo horário' }}</DialogTitle>
          <DialogDescription
            >Deixe as datas em branco para o horário valer sempre.</DialogDescription
          >
        </DialogHeader>
        <form id="slot-form" class="flex flex-col gap-4" @submit.prevent="submit">
          <Alert v-if="formError" intent="danger" variant="soft">{{ formError }}</Alert>
          <Select
            id="slot-weekday"
            v-model="weekday"
            label="Dia da semana"
            :options="weekdayOptions"
            required
          />
          <div class="grid grid-cols-2 gap-3">
            <Input id="slot-start" v-model="start" label="Início" type="time" step="300" required />
            <Input id="slot-end" v-model="end" label="Fim" type="time" step="300" required />
          </div>
          <Input
            id="slot-description"
            v-model="description"
            label="Descrição"
            placeholder="Ex.: Aula de Estrutura da Matéria"
            :maxlength="255"
            required
          />
          <div class="grid grid-cols-2 gap-3">
            <DatePicker
              id="slot-from"
              v-model="validFromValue"
              label="Vale a partir de (opcional)"
              date-style="short"
              clearable
            />
            <DatePicker
              id="slot-until"
              v-model="validUntilValue"
              label="Vale até (opcional)"
              date-style="short"
              clearable
            />
          </div>
        </form>
        <DialogFooter>
          <Button
            v-if="editing"
            type="button"
            variant="ghost"
            tone="danger"
            :icon-leading="Trash2"
            class="sm:mr-auto"
            @click="confirmDelete = true"
          >
            Excluir
          </Button>
          <Button type="button" variant="ghost" @click="dialogOpen = false">Cancelar</Button>
          <Button type="submit" form="slot-form" :loading="saving">Salvar</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <ConfirmDialog
      v-model:open="confirmDelete"
      title="Excluir este horário?"
      description="Os lançamentos que ele já gerou continuam no calendário."
      confirm-label="Excluir"
      tone="danger"
      :loading="deleting"
      @confirm="removeEditing"
    />
  </div>
</template>
