<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import WeekTimetable from '@/Components/Schedule/WeekTimetable.vue';
import { useI18n, type TranslationKey } from '@/Composables/useI18n';
import { formatDate, fromIsoDate, toIsoDate } from '@/Lib/dates';
import { clockToMinutes, formatDuration, minutesToClock } from '@/Lib/time';
import type { WeeklySlot } from '@/types/models';

defineOptions({ layout: AppLayout });

const props = defineProps<{ slots: WeeklySlot[]; weeklyWorkloadMinutes: number }>();

const { t } = useI18n();
const confirm = useConfirm();
const editing = ref<WeeklySlot | null>(null);
const dialogOpen = ref(false);
const start = ref('08:00');
const end = ref('10:00');
const validFrom = ref<Date | null>(null);
const validUntil = ref<Date | null>(null);
const form = useForm({ weekday: 1, start_minute: 0, end_minute: 0, description: '', valid_from: '', valid_until: '' });

const weekdayOptions = [1, 2, 3, 4, 5].map((weekday) => ({
    value: weekday,
    label: t(`schedule.weekday.${weekday}` as TranslationKey),
}));

const rows = computed(() =>
    weekdayOptions.map((option) => {
        const slots = props.slots.filter((slot) => slot.weekday === option.value);

        return { ...option, slots, minutes: slots.reduce((sum, slot) => sum + slot.endMinute - slot.startMinute, 0) };
    }),
);

const scheduledMinutes = computed(() => rows.value.reduce((sum, row) => sum + row.minutes, 0));
const freeMinutes = computed(() => Math.max(props.weeklyWorkloadMinutes - scheduledMinutes.value, 0));

function validity(slot: WeeklySlot): string | null {
    if (slot.validFrom && slot.validUntil) {
        return t('schedule.range', { from: formatDate(slot.validFrom), until: formatDate(slot.validUntil) });
    }

    if (slot.validFrom) {
        return t('schedule.from', { from: formatDate(slot.validFrom) });
    }

    return slot.validUntil ? t('schedule.until', { until: formatDate(slot.validUntil) }) : null;
}

function openNew(weekday = 1, startMinute = 480): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.weekday = weekday;
    start.value = minutesToClock(startMinute);
    end.value = minutesToClock(Math.min(startMinute + 120, 1440));
    validFrom.value = null;
    validUntil.value = null;
    dialogOpen.value = true;
}

function openEdit(slot: WeeklySlot): void {
    editing.value = slot;
    form.clearErrors();
    form.weekday = slot.weekday;
    form.description = slot.description;
    start.value = minutesToClock(slot.startMinute);
    end.value = minutesToClock(slot.endMinute);
    validFrom.value = slot.validFrom ? fromIsoDate(slot.validFrom) : null;
    validUntil.value = slot.validUntil ? fromIsoDate(slot.validUntil) : null;
    dialogOpen.value = true;
}

function submit(): void {
    const payload = form.transform((data) => ({
        ...data,
        start_minute: clockToMinutes(start.value) ?? -1,
        end_minute: clockToMinutes(end.value) ?? -1,
        valid_from: validFrom.value ? toIsoDate(validFrom.value) : null,
        valid_until: validUntil.value ? toIsoDate(validUntil.value) : null,
    }));
    const options = { preserveScroll: true, onSuccess: () => (dialogOpen.value = false) };

    if (editing.value) {
        payload.put(`/grade-semanal/${editing.value.id}`, options);
    } else {
        payload.post('/grade-semanal', options);
    }
}

function remove(slot: WeeklySlot): void {
    confirm.require({
        header: t('schedule.deleteTitle'),
        message: t('schedule.deleteBody'),
        acceptProps: { label: t('common.delete'), severity: 'danger' },
        rejectProps: { label: t('common.cancel'), severity: 'secondary', variant: 'text' },
        accept: () =>
            router.delete(`/grade-semanal/${slot.id}`, {
                preserveScroll: true,
                onSuccess: () => (dialogOpen.value = false),
            }),
    });
}
</script>

<template>
    <Head :title="t('schedule.title')" />

    <PageHeader :title="t('schedule.title')" :description="t('schedule.description')">
        <template #actions>
            <Button @click="openNew()">
                <Plus class="size-4" aria-hidden="true" />
                {{ t('schedule.add') }}
            </Button>
        </template>
    </PageHeader>

    <p class="mt-6 mb-3 text-sm text-(--muted)">
        <template v-if="scheduledMinutes > 0">
            {{
                t('schedule.summary', {
                    scheduled: formatDuration(scheduledMinutes),
                    workload: formatDuration(weeklyWorkloadMinutes),
                    free: formatDuration(freeMinutes),
                })
            }}
        </template>
        <template v-else>{{ t('schedule.emptySummary') }}</template>
    </p>

    <WeekTimetable
        class="hidden md:block"
        :slots="slots"
        :weekdays="weekdayOptions"
        @create="(weekday, minute) => openNew(weekday, minute)"
        @edit="openEdit"
    />

    <ul class="card divide-y divide-(--line) md:hidden">
        <li
            v-for="row in rows"
            :key="row.value"
            class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-[11rem_minmax(0,1fr)] sm:gap-6 sm:px-5"
        >
            <div class="flex items-baseline justify-between gap-2 sm:block">
                <h2 class="text-sm font-semibold">{{ row.label }}</h2>
                <p class="text-xs text-(--muted) tabular-nums sm:mt-0.5">
                    {{ row.minutes ? formatDuration(row.minutes) : t('schedule.free') }}
                </p>
            </div>
            <div class="grid content-start justify-items-start gap-2">
                <button
                    v-for="slot in row.slots"
                    :key="slot.id"
                    type="button"
                    class="grid w-full grid-cols-[7rem_minmax(0,1fr)] items-baseline gap-x-3 rounded-(--radius-md) border border-(--line) bg-(--surface-raised) px-3 py-2 text-left transition-colors hover:border-(--accent) hover:bg-(--accent-soft)"
                    :aria-label="t('schedule.editLabel', { description: slot.description, weekday: row.label })"
                    @click="openEdit(slot)"
                >
                    <span class="text-sm font-semibold tabular-nums">
                        {{ minutesToClock(slot.startMinute) }}-{{ minutesToClock(slot.endMinute) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm break-words">{{ slot.description }}</span>
                        <span v-if="validity(slot)" class="block text-xs text-(--muted)">{{ validity(slot) }}</span>
                    </span>
                </button>
                <Button size="small" variant="text" class="-ml-2" @click="openNew(row.value)">
                    <Plus class="size-4" aria-hidden="true" />
                    {{ t('schedule.addTo', { weekday: row.label }) }}
                </Button>
            </div>
        </li>
    </ul>

    <Dialog
        v-model:visible="dialogOpen"
        modal
        :header="editing ? t('schedule.dialogEdit') : t('schedule.dialogNew')"
        class="w-[min(100%-2rem,32rem)]"
    >
        <form class="grid gap-4" @submit.prevent="submit">
            <FormField
                id="slot-weekday"
                v-slot="{ id, invalid }"
                :label="t('schedule.weekday')"
                :error="form.errors.weekday"
            >
                <Select
                    v-model="form.weekday"
                    :input-id="id"
                    :options="weekdayOptions"
                    option-label="label"
                    option-value="value"
                    :invalid="invalid"
                    fluid
                />
            </FormField>
            <div class="grid grid-cols-2 gap-3">
                <FormField
                    id="slot-start"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('day.start')"
                    :error="form.errors.start_minute"
                >
                    <InputText
                        :id="id"
                        v-model="start"
                        type="time"
                        step="300"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
                <FormField
                    id="slot-end"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('day.end')"
                    :error="form.errors.end_minute"
                >
                    <InputText
                        :id="id"
                        v-model="end"
                        type="time"
                        step="300"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
            </div>
            <FormField
                id="slot-description"
                v-slot="{ id, invalid, describedBy }"
                :label="t('schedule.descriptionLabel')"
                :error="form.errors.description"
            >
                <InputText
                    :id="id"
                    v-model="form.description"
                    maxlength="255"
                    :placeholder="t('schedule.descriptionPlaceholder')"
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>
            <div class="grid gap-2">
                <div class="grid grid-cols-2 gap-3">
                    <FormField
                        id="slot-from"
                        v-slot="{ id, invalid }"
                        :label="t('schedule.validFrom')"
                        :error="form.errors.valid_from"
                        optional
                    >
                        <DatePicker v-model="validFrom" :input-id="id" show-button-bar fluid :invalid="invalid" />
                    </FormField>
                    <FormField
                        id="slot-until"
                        v-slot="{ id, invalid }"
                        :label="t('schedule.validUntil')"
                        :error="form.errors.valid_until"
                        optional
                    >
                        <DatePicker
                            v-model="validUntil"
                            :input-id="id"
                            :min-date="validFrom ?? undefined"
                            show-button-bar
                            fluid
                            :invalid="invalid"
                        />
                    </FormField>
                </div>
                <p class="text-xs text-(--muted)">{{ t('schedule.validityHint') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-2">
                <Button
                    v-if="editing"
                    type="button"
                    variant="text"
                    severity="danger"
                    :label="t('common.delete')"
                    @click="remove(editing)"
                />
                <Button
                    type="button"
                    class="ml-auto"
                    variant="text"
                    severity="secondary"
                    :label="t('common.cancel')"
                    @click="dialogOpen = false"
                />
                <Button type="submit" :label="t('common.save')" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
