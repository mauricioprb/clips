<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarCheck, ChevronLeft, ChevronRight, FileDown, Plus } from '@lucide/vue';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import DayPanel from '@/Components/Month/DayPanel.vue';
import MonthCalendar from '@/Components/Month/MonthCalendar.vue';
import MonthProgress from '@/Components/Month/MonthProgress.vue';
import SetupChecklist from '@/Components/Month/SetupChecklist.vue';
import { useI18n } from '@/Composables/useI18n';
import { formatMonthTitle, toIsoDate } from '@/Lib/dates';
import { monthUrl, shiftMonth } from '@/Lib/month';
import { formatDuration } from '@/Lib/time';
import type { MonthDay } from '@/types/models';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    month: string;
    days: MonthDay[];
    loggedMinutes: number;
    workdayMinutes: number;
    targetMinutes: number;
    dailyTargetMinutes: number;
    setup: { profile: boolean; schedule: boolean; activities: boolean };
    suggestions: string[];
}>();

const { t } = useI18n();
const selectedDate = ref<string | null>(null);
const confirm = useConfirm();

const today = toIsoDate(new Date());
const currentMonth = today.slice(0, 7);

const title = computed(() => formatMonthTitle(props.month));
const selectedDay = computed(() => props.days.find((day) => day.date === selectedDate.value) ?? null);
const workdays = computed(() => props.days.filter((day) => day.isWorkday).length);
const setupComplete = computed(() => props.setup.profile && props.setup.activities);

function fillMonth(): void {
    confirm.require({
        header: t('month.fillDialog.title'),
        message: t('month.fillDialog.body', { daily: formatDuration(props.dailyTargetMinutes) }),
        acceptProps: { label: t('month.fillDialog.confirm') },
        rejectProps: { label: t('common.cancel'), severity: 'secondary', variant: 'text' },
        accept: () => router.post(monthUrl(props.month, '/preencher'), {}, { preserveScroll: true }),
    });
}
</script>

<template>
    <Head :title="title" />

    <section class="grid gap-4" aria-labelledby="calendar-title">
        <header class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <h1 id="calendar-title" class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                <div class="flex items-center gap-0.5">
                    <Button
                        :as="Link"
                        :href="monthUrl(shiftMonth(month, -1))"
                        icon-only
                        variant="text"
                        severity="secondary"
                        :aria-label="t('month.previous')"
                    >
                        <ChevronLeft class="size-4" aria-hidden="true" />
                    </Button>
                    <Button
                        :as="Link"
                        :href="monthUrl(shiftMonth(month, 1))"
                        icon-only
                        variant="text"
                        severity="secondary"
                        :aria-label="t('month.next')"
                    >
                        <ChevronRight class="size-4" aria-hidden="true" />
                    </Button>
                    <Button
                        :as="Link"
                        href="/mes"
                        variant="text"
                        severity="secondary"
                        :label="t('month.today')"
                        :aria-label="t('month.current')"
                        :aria-current="month === currentMonth ? 'date' : undefined"
                    />
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Button severity="secondary" variant="text" @click="fillMonth">
                    <CalendarCheck class="size-4" aria-hidden="true" />
                    {{ t('month.fill') }}
                </Button>
                <Button as="a" :href="monthUrl(month, '/relatorio')" severity="secondary" variant="text" download>
                    <FileDown class="size-4" aria-hidden="true" />
                    {{ t('month.report') }}
                </Button>
                <Button @click="selectedDate = month === currentMonth ? today : `${month}-01`">
                    <Plus class="size-4" aria-hidden="true" />
                    {{ t('month.add') }}
                </Button>
            </div>
        </header>

        <MonthProgress
            :workday-minutes="workdayMinutes"
            :target-minutes="targetMinutes"
            :logged-minutes="loggedMinutes"
            :workdays="workdays"
            :daily-target-minutes="dailyTargetMinutes"
        />

        <SetupChecklist v-if="!setupComplete" :setup="setup" />

        <MonthCalendar
            :days="days"
            :daily-target-minutes="dailyTargetMinutes"
            :today="today"
            @select="selectedDate = $event"
        />
    </section>

    <DayPanel
        :day="selectedDay"
        :daily-target-minutes="dailyTargetMinutes"
        :suggestions="suggestions"
        @close="selectedDate = null"
    />
</template>
