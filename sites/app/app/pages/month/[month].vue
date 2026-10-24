<script setup lang="ts">
import { NuxtLink } from '#components';
import { CalendarCheck, ChevronLeft, ChevronRight, FileDown, Plus, RefreshCw } from '@lucide/vue';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import { Skeleton } from '~/ui/skeleton';
import { toast } from '~/ui/toast';

definePageMeta({
  validate: (route) => /^\d{4}-(0[1-9]|1[0-2])$/.test(String(route.params.month)),
});

const route = useRoute();
const { summary, load, fill, downloadReport } = useMonth();

const month = computed(() => String(route.params.month));
const today = toIsoDate(new Date());
const title = computed(() => formatMonthTitle(month.value));
const data = computed(() => (summary.value?.month === month.value ? summary.value : null));
const selectedDate = ref<string | null>(null);
const selectedDay = computed(
  () => data.value?.days.find((day) => day.date === selectedDate.value) ?? null,
);
const workdays = computed(() => data.value?.days.filter((day) => day.isWorkday).length ?? 0);
const loadError = ref('');
const confirmFill = ref(false);
const filling = ref(false);
const downloading = ref(false);

useHead({ title });

async function loadMonth() {
  loadError.value = '';
  try {
    await load(month.value);
  } catch (exception) {
    loadError.value = errorMessage(exception);
  }
}

async function fillMonth() {
  filling.value = true;
  try {
    const created = await fill(month.value);
    toast.success(created > 0 ? 'Mês completado.' : 'Não havia nada para completar neste mês.');
    confirmFill.value = false;
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    filling.value = false;
  }
}

async function download() {
  downloading.value = true;
  try {
    await downloadReport(month.value);
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    downloading.value = false;
  }
}

watch(month, loadMonth, { immediate: true });
</script>

<template>
  <section class="flex flex-col gap-4" aria-labelledby="month-title">
    <header class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
        <h1 id="month-title" class="text-heading-3 font-semibold">{{ title }}</h1>
        <div class="flex items-center gap-0.5">
          <Button
            :as="NuxtLink"
            :to="`/month/${shiftMonth(month, -1)}`"
            variant="ghost"
            size="sm"
            icon-only
            :icon-leading="ChevronLeft"
            aria-label="Mês anterior"
          />
          <Button
            :as="NuxtLink"
            :to="`/month/${shiftMonth(month, 1)}`"
            variant="ghost"
            size="sm"
            icon-only
            :icon-leading="ChevronRight"
            aria-label="Próximo mês"
          />
          <Button
            :as="NuxtLink"
            :to="`/month/${currentMonth()}`"
            variant="ghost"
            size="sm"
            aria-label="Ir para o mês atual"
            :aria-current="month === currentMonth() ? 'date' : undefined"
          >
            Hoje
          </Button>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <Button
          type="button"
          variant="ghost"
          :icon-leading="CalendarCheck"
          :disabled="!data"
          @click="confirmFill = true"
        >
          Completar mês
        </Button>
        <Button
          type="button"
          variant="ghost"
          :icon-leading="FileDown"
          :loading="downloading"
          loading-text="Gerando PDF…"
          :disabled="!data"
          @click="download"
        >
          Baixar PDF
        </Button>
        <Button
          type="button"
          :icon-leading="Plus"
          :disabled="!data"
          @click="selectedDate = month === currentMonth() ? today : `${month}-01`"
        >
          Registrar horas
        </Button>
      </div>
    </header>

    <div v-if="loadError" class="flex flex-col items-start gap-4">
      <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
      <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadMonth">
        Tentar novamente
      </Button>
    </div>
    <div v-else-if="!data" role="status" class="flex flex-col gap-3">
      <span class="sr-only">Carregando o mês…</span>
      <Skeleton class="h-5 w-72" />
      <Skeleton class="h-96 w-full" />
    </div>
    <template v-else>
      <MonthProgress
        :workday-minutes="data.workdayMinutes"
        :target-minutes="data.targetMinutes"
        :logged-minutes="data.loggedMinutes"
        :workdays="workdays"
        :daily-target-minutes="data.dailyTargetMinutes"
      />
      <SetupChecklist v-if="!data.setup.profile || !data.setup.activities" :setup="data.setup" />
      <MonthCalendar
        :days="data.days"
        :daily-target-minutes="data.dailyTargetMinutes"
        :today="today"
        @select="selectedDate = $event"
      />
    </template>

    <DayPanel
      :day="selectedDay"
      :daily-target-minutes="data?.dailyTargetMinutes ?? 0"
      :suggestions="data?.suggestions ?? []"
      @close="selectedDate = null"
    />

    <ConfirmDialog
      v-model:open="confirmFill"
      title="Completar o mês?"
      :description="`Os horários da grade semanal serão lançados e cada dia útil será completado até ${formatDuration(data?.dailyTargetMinutes ?? 0)} com as suas atividades padrão. Os lançamentos que você já fez não mudam.`"
      confirm-label="Completar mês"
      :loading="filling"
      @confirm="fillMonth"
    />
  </section>
</template>
