<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import SettingsLayout from '@/Components/Layout/SettingsLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import SettingsSection from '@/Components/UI/SettingsSection.vue';
import { useI18n } from '@/Composables/useI18n';
import { formatDate, formatDayAndMonth, toIsoDate } from '@/Lib/dates';
import type { Holiday, HolidayRecurrence } from '@/types/models';

defineOptions({ layout: [AppLayout, SettingsLayout] });

defineProps<{
    year: number;
    holidays: Holiday[];
    calendar: { date: string; name: string }[];
}>();

const { t } = useI18n();
const date = ref<Date | null>(null);
const dialogOpen = ref(false);
const form = useForm<{ name: string; recurrence: HolidayRecurrence; date: string | null }>({
    name: '',
    recurrence: 'yearly',
    date: null,
});

const recurrenceOptions = (['yearly', 'once', 'corpus_christi'] as const).map((recurrence) => ({
    value: recurrence,
    label: t(`holidays.recurrence.${recurrence}`),
}));

const needsDate = computed(() => form.recurrence !== 'corpus_christi');

function describe(holiday: Holiday): string {
    if (holiday.recurrence === 'corpus_christi' || !holiday.date) {
        return t('holidays.recurrence.corpus_christi');
    }

    return holiday.recurrence === 'yearly'
        ? t('holidays.yearly', { date: formatDayAndMonth(holiday.date) })
        : formatDate(holiday.date);
}

function submit(): void {
    form.transform((data) => ({ ...data, date: needsDate.value && date.value ? toIsoDate(date.value) : null })).post(
        '/configuracoes/feriados',
        {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                date.value = null;
                dialogOpen.value = false;
            },
        },
    );
}

function openDialog(): void {
    form.reset();
    form.clearErrors();
    date.value = null;
    dialogOpen.value = true;
}

function remove(holiday: Holiday): void {
    router.delete(`/configuracoes/feriados/${holiday.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('settings.tab.holidays')" />

    <div class="grid grid-cols-1 gap-6">
        <div class="card">
            <SettingsSection :title="t('holidays.yours')" :description="t('holidays.description')">
                <ul v-if="holidays.length" class="-my-2 divide-y divide-(--line)">
                    <li v-for="holiday in holidays" :key="holiday.id" class="flex items-center gap-3 py-2.5">
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium">{{ holiday.name }}</span>
                            <span class="block text-xs text-(--muted)">{{ describe(holiday) }}</span>
                        </span>
                        <Button
                            icon-only
                            variant="text"
                            severity="secondary"
                            class="size-11 shrink-0 hover:text-(--danger)!"
                            :aria-label="t('holidays.removeLabel', { name: holiday.name })"
                            @click="remove(holiday)"
                        >
                            <Trash2 class="size-5" aria-hidden="true" />
                        </Button>
                    </li>
                </ul>
                <p v-else class="text-sm text-(--muted)">{{ t('holidays.empty') }}</p>
                <div>
                    <Button variant="outlined" severity="secondary" @click="openDialog">
                        <Plus class="size-4" aria-hidden="true" />
                        {{ t('holidays.add') }}
                    </Button>
                </div>
            </SettingsSection>
        </div>

        <div class="card">
            <SettingsSection :title="t('holidays.calendar', { year })" :description="t('holidays.calendarDescription')">
                <ul class="-my-2 divide-y divide-(--line) text-sm">
                    <li v-for="item in calendar" :key="item.date" class="flex gap-4 py-2">
                        <span class="w-12 shrink-0 text-(--muted) tabular-nums">{{
                            formatDayAndMonth(item.date)
                        }}</span>
                        <span>{{ item.name }}</span>
                    </li>
                </ul>
            </SettingsSection>
        </div>
    </div>

    <Dialog v-model:visible="dialogOpen" modal :header="t('holidays.add')" class="w-[min(100%-2rem,28rem)]">
        <form class="grid gap-4" @submit.prevent="submit">
            <FormField
                id="holiday-name"
                v-slot="{ id, invalid, describedBy }"
                :label="t('holidays.name')"
                :error="form.errors.name"
            >
                <InputText
                    :id="id"
                    v-model="form.name"
                    maxlength="120"
                    :placeholder="t('holidays.namePlaceholder')"
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>
            <FormField
                id="holiday-recurrence"
                v-slot="{ id, invalid }"
                :label="t('holidays.recurrence')"
                :error="form.errors.recurrence"
            >
                <Select
                    v-model="form.recurrence"
                    :input-id="id"
                    :options="recurrenceOptions"
                    option-label="label"
                    option-value="value"
                    :invalid="invalid"
                    fluid
                />
            </FormField>
            <FormField
                v-if="needsDate"
                id="holiday-date"
                v-slot="{ id, invalid }"
                :label="t('holidays.date')"
                :error="form.errors.date"
            >
                <DatePicker v-model="date" :input-id="id" fluid :invalid="invalid" />
            </FormField>
            <div class="flex justify-end gap-2 pt-2">
                <Button
                    type="button"
                    variant="text"
                    severity="secondary"
                    :label="t('common.cancel')"
                    @click="dialogOpen = false"
                />
                <Button type="submit" :label="t('holidays.add')" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
