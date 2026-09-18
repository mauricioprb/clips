<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputTags from 'primevue/inputtags';
import InputText from 'primevue/inputtext';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import SettingsLayout from '@/Components/Layout/SettingsLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import SettingsSection from '@/Components/UI/SettingsSection.vue';
import { useI18n } from '@/Composables/useI18n';
import { formatDuration } from '@/Lib/time';

defineOptions({ layout: [AppLayout, SettingsLayout] });

const props = defineProps<{
    profile: {
        name: string;
        email: string;
        advisorName: string | null;
        scholarshipName: string | null;
        laboratories: string[];
        weeklyWorkloadHours: number;
    };
}>();

const { t } = useI18n();
const form = useForm({
    name: props.profile.name,
    email: props.profile.email,
    advisor_name: props.profile.advisorName ?? '',
    scholarship_name: props.profile.scholarshipName ?? '',
    laboratories: props.profile.laboratories,
    weekly_workload_hours: props.profile.weeklyWorkloadHours,
});

const dailyTarget = computed(() => formatDuration(Math.floor(((form.weekly_workload_hours ?? 0) * 60) / 5)));

const laboratoryError = computed(
    () => form.errors.laboratories ?? Object.entries(form.errors).find(([key]) => key.startsWith('laboratories.'))?.[1],
);
</script>

<template>
    <Head :title="t('settings.tab.profile')" />

    <form class="card" @submit.prevent="form.put('/configuracoes/perfil', { preserveScroll: true })">
        <SettingsSection :title="t('profile.studentTitle')" :description="t('profile.studentDescription')">
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    id="name"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('profile.name')"
                    :error="form.errors.name"
                >
                    <InputText
                        :id="id"
                        v-model="form.name"
                        autocomplete="name"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
                <FormField
                    id="email"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('auth.email')"
                    :error="form.errors.email"
                >
                    <InputText
                        :id="id"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
            </div>
        </SettingsSection>

        <SettingsSection :title="t('profile.scholarshipTitle')" :description="t('profile.description')">
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    id="advisor"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('profile.advisor')"
                    :error="form.errors.advisor_name"
                >
                    <InputText
                        :id="id"
                        v-model="form.advisor_name"
                        :placeholder="t('profile.advisorPlaceholder')"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
                <FormField
                    id="scholarship"
                    v-slot="{ id, invalid, describedBy }"
                    :label="t('profile.scholarship')"
                    :error="form.errors.scholarship_name"
                >
                    <InputText
                        :id="id"
                        v-model="form.scholarship_name"
                        :placeholder="t('profile.scholarshipPlaceholder')"
                        required
                        fluid
                        :invalid="invalid"
                        :aria-describedby="describedBy"
                    />
                </FormField>
            </div>
            <FormField
                id="laboratories"
                v-slot="{ id, invalid }"
                :label="t('profile.laboratories')"
                :hint="t('profile.laboratoriesHint')"
                :error="laboratoryError"
            >
                <InputTags
                    v-model="form.laboratories"
                    :input-id="id"
                    :placeholder="form.laboratories.length ? undefined : t('profile.laboratoriesPlaceholder')"
                    add-on-blur
                    :max="10"
                    fluid
                    :invalid="invalid"
                />
            </FormField>
        </SettingsSection>

        <SettingsSection :title="t('profile.workloadTitle')" :description="t('profile.weeklyHoursHint')">
            <FormField
                id="weekly-hours"
                v-slot="{ id, invalid }"
                :label="t('profile.weeklyHours')"
                :error="form.errors.weekly_workload_hours"
            >
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <InputNumber
                        v-model="form.weekly_workload_hours"
                        :input-id="id"
                        :min="1"
                        :max="44"
                        :step="0.5"
                        :min-fraction-digits="0"
                        :max-fraction-digits="1"
                        locale="pt-BR"
                        suffix=" h"
                        show-buttons
                        button-layout="horizontal"
                        input-class="w-20 text-center tabular-nums"
                        :invalid="invalid"
                    />
                    <p class="text-sm text-(--muted)" aria-live="polite">
                        {{ t('profile.dailyTarget', { hours: dailyTarget }) }}
                    </p>
                </div>
            </FormField>
        </SettingsSection>

        <footer
            class="flex justify-end rounded-b-(--radius-lg) border-t border-(--line) bg-(--surface-sunken) px-5 py-3"
        >
            <Button type="submit" :label="t('profile.save')" :loading="form.processing" />
        </footer>
    </form>
</template>
