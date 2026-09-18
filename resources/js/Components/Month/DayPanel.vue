<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import AutoComplete from 'primevue/autocomplete';
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import FormField from '@/Components/UI/FormField.vue';
import { useI18n } from '@/Composables/useI18n';
import { formatDayTitle } from '@/Lib/dates';
import { clockToMinutes, formatDuration, minutesToClock } from '@/Lib/time';
import type { MonthDay, TimeEntry } from '@/types/models';

const props = defineProps<{
    day: MonthDay | null;
    dailyTargetMinutes: number;
    suggestions: string[];
}>();

const emit = defineEmits<{ close: [] }>();

const { t } = useI18n();
const editing = ref<TimeEntry | null>(null);
const start = ref('08:00');
const end = ref('09:00');
const matches = ref<string[]>([]);
const form = useForm({ date: '', start_minute: 0, end_minute: 0, description: '' });

const title = computed(() => (props.day ? formatDayTitle(props.day.date) : ''));
const subtitle = computed(() => {
    if (!props.day) {
        return '';
    }

    if (!props.day.isWorkday) {
        return `${props.day.holiday ?? t('month.day.weekend')}. ${t('day.notCounted')}`;
    }

    return t('day.logged', {
        logged: formatDuration(props.day.minutes),
        target: formatDuration(props.dailyTargetMinutes),
    });
});

function resetForm(): void {
    const lastEnd = props.day?.entries.at(-1)?.endMinute;
    const startMinute = lastEnd !== undefined && lastEnd < 1380 ? lastEnd : 480;

    editing.value = null;
    start.value = minutesToClock(startMinute);
    end.value = minutesToClock(startMinute + 60);
    form.reset('description');
    form.clearErrors();
}

function edit(entry: TimeEntry): void {
    editing.value = entry;
    start.value = minutesToClock(entry.startMinute);
    end.value = minutesToClock(entry.endMinute);
    form.description = entry.description;
    form.clearErrors();
}

function search(query: string): void {
    const needle = query.trim().toLocaleLowerCase('pt-BR');
    matches.value = props.suggestions.filter((suggestion) => suggestion.toLocaleLowerCase('pt-BR').includes(needle));
}

function submit(): void {
    if (!props.day) {
        return;
    }

    form.date = props.day.date;
    form.start_minute = clockToMinutes(start.value) ?? -1;
    form.end_minute = clockToMinutes(end.value) ?? -1;

    const options = { preserveScroll: true, preserveState: true, onSuccess: resetForm };

    if (editing.value) {
        form.put(`/lancamentos/${editing.value.id}`, options);
    } else {
        form.post('/lancamentos', options);
    }
}

function remove(entry: TimeEntry): void {
    router.delete(`/lancamentos/${entry.id}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => editing.value?.id === entry.id && resetForm(),
    });
}

watch(
    () => props.day?.date,
    (date) => date && resetForm(),
    { immediate: true },
);
</script>

<template>
    <Drawer
        :visible="day !== null"
        position="right"
        :header="title"
        class="w-full! md:w-[28rem]!"
        @update:visible="(visible: boolean) => !visible && emit('close')"
    >
        <template v-if="day">
            <p class="mb-4 text-sm text-(--muted)">{{ subtitle }}</p>

            <p v-if="day.entries.length === 0" class="text-sm text-(--muted)">{{ t('day.empty') }}</p>
            <ul v-else class="-mx-2 grid gap-1">
                <li
                    v-for="entry in day.entries"
                    :key="entry.id"
                    class="flex items-start gap-3 rounded-(--radius-md) px-2 py-2"
                    :class="editing?.id === entry.id ? 'bg-(--accent-soft)' : ''"
                >
                    <span class="w-24 shrink-0 pt-0.5 text-sm text-(--muted) tabular-nums">
                        {{ minutesToClock(entry.startMinute) }}-{{ minutesToClock(entry.endMinute) }}
                    </span>
                    <span class="grid min-w-0 flex-1 justify-items-start gap-1">
                        <span class="text-sm font-medium break-words">{{ entry.description }}</span>
                        <Tag
                            v-if="entry.source !== 'manual'"
                            severity="secondary"
                            :value="t(entry.source === 'schedule' ? 'day.source.schedule' : 'day.source.activity')"
                        />
                    </span>
                    <Button
                        icon-only
                        variant="text"
                        severity="secondary"
                        class="size-11 shrink-0"
                        :aria-label="t('day.editLabel', { description: entry.description })"
                        @click="edit(entry)"
                    >
                        <Pencil class="size-5" aria-hidden="true" />
                    </Button>
                    <Button
                        icon-only
                        variant="text"
                        severity="secondary"
                        class="size-11 shrink-0 hover:text-(--danger)!"
                        :aria-label="t('day.deleteLabel', { description: entry.description })"
                        @click="remove(entry)"
                    >
                        <Trash2 class="size-5" aria-hidden="true" />
                    </Button>
                </li>
            </ul>

            <form class="mt-6 grid gap-4 border-t border-(--line) pt-5" @submit.prevent="submit">
                <h3 class="text-sm font-semibold">{{ editing ? t('day.editEntry') : t('day.newEntry') }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    <FormField
                        id="entry-start"
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
                            class="tabular-nums"
                            :invalid="invalid"
                            :aria-describedby="describedBy"
                        />
                    </FormField>
                    <FormField
                        id="entry-end"
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
                            class="tabular-nums"
                            :invalid="invalid"
                            :aria-describedby="describedBy"
                        />
                    </FormField>
                </div>
                <FormField
                    id="entry-description"
                    v-slot="{ id, invalid }"
                    :label="t('day.description')"
                    :error="form.errors.description"
                >
                    <AutoComplete
                        v-model="form.description"
                        :input-id="id"
                        :suggestions="matches"
                        :show-empty-message="false"
                        :placeholder="t('day.descriptionPlaceholder')"
                        :invalid="invalid"
                        maxlength="255"
                        dropdown
                        fluid
                        @complete="search($event.query)"
                    />
                </FormField>
                <div class="flex flex-wrap justify-end gap-2">
                    <Button
                        v-if="editing"
                        type="button"
                        variant="text"
                        severity="secondary"
                        :label="t('day.cancelEdit')"
                        @click="resetForm"
                    />
                    <Button
                        type="submit"
                        :label="editing ? t('day.update') : t('day.add')"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </template>
    </Drawer>
</template>
