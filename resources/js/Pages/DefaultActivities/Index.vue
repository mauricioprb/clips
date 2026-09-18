<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, Pencil, Plus, Trash2 } from '@lucide/vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Menu from 'primevue/menu';
import { type MenuItem } from 'primevue/menuitem';
import Select from 'primevue/select';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import PriorityIcon from '@/Components/UI/PriorityIcon.vue';
import { useI18n } from '@/Composables/useI18n';
import type { DefaultActivity, Priority } from '@/types/models';

defineOptions({ layout: AppLayout });

const props = defineProps<{ activities: DefaultActivity[] }>();

const { t } = useI18n();
const confirm = useConfirm();
const priorities: Priority[] = ['high', 'medium', 'low'];

const adding = ref(false);
const editingId = ref<string | null>(null);
const priorityTarget = ref<DefaultActivity | null>(null);
const priorityMenu = ref<InstanceType<typeof Menu> | null>(null);
const createForm = useForm<{ description: string; priority: Priority }>({ description: '', priority: 'medium' });
const editForm = useForm<{ description: string; priority: Priority }>({ description: '', priority: 'medium' });

const priorityOptions = priorities.map((priority) => ({
    value: priority,
    label: t(`activities.priority.${priority}`),
}));

const sorted = computed(() =>
    [...props.activities].sort(
        (a, b) =>
            priorities.indexOf(a.priority) - priorities.indexOf(b.priority) ||
            a.description.localeCompare(b.description, 'pt-BR'),
    ),
);

const priorityItems = computed<MenuItem[]>(() =>
    priorities.map((priority) => ({
        label: t(`activities.priority.${priority}`),
        priority,
        command: () => priorityTarget.value && changePriority(priorityTarget.value, priority),
    })),
);

async function startAdding(): Promise<void> {
    editingId.value = null;
    adding.value = true;
    createForm.reset();
    createForm.clearErrors();
    await nextTick();
    document.getElementById('new-activity')?.focus();
}

function create(): void {
    createForm.post('/atividades-padrao', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset('description');
            document.getElementById('new-activity')?.focus();
        },
    });
}

async function startEditing(activity: DefaultActivity): Promise<void> {
    adding.value = false;
    editingId.value = activity.id;
    editForm.description = activity.description;
    editForm.priority = activity.priority;
    editForm.clearErrors();
    await nextTick();
    document.getElementById(`edit-${activity.id}`)?.focus();
}

function save(activity: DefaultActivity): void {
    editForm.put(`/atividades-padrao/${activity.id}`, {
        preserveScroll: true,
        onSuccess: () => (editingId.value = null),
    });
}

function openPriorityMenu(event: Event, activity: DefaultActivity): void {
    priorityTarget.value = activity;
    priorityMenu.value?.toggle(event);
}

function changePriority(activity: DefaultActivity, priority: Priority): void {
    if (priority !== activity.priority) {
        router.put(
            `/atividades-padrao/${activity.id}`,
            { description: activity.description, priority },
            { preserveScroll: true },
        );
    }
}

function remove(activity: DefaultActivity): void {
    confirm.require({
        header: t('activities.deleteTitle', { name: activity.description }),
        message: t('activities.deleteBody'),
        acceptProps: { label: t('common.delete'), severity: 'danger' },
        rejectProps: { label: t('common.cancel'), severity: 'secondary', variant: 'text' },
        accept: () => router.delete(`/atividades-padrao/${activity.id}`, { preserveScroll: true }),
    });
}
</script>

<template>
    <Head :title="t('activities.title')" />

    <PageHeader :title="t('activities.title')" :description="t('activities.description')">
        <template #actions>
            <Button @click="startAdding">
                <Plus class="size-4" aria-hidden="true" />
                {{ t('activities.new') }}
            </Button>
        </template>
    </PageHeader>

    <div v-if="sorted.length || adding" class="card mt-8 divide-y divide-(--line) overflow-hidden">
        <form
            v-if="adding"
            class="flex flex-wrap items-center gap-2 bg-(--surface-sunken) px-4 py-3"
            @submit.prevent="create"
        >
            <label for="new-activity" class="sr-only">{{ t('activities.descriptionLabel') }}</label>
            <InputText
                id="new-activity"
                v-model="createForm.description"
                maxlength="255"
                :placeholder="t('activities.placeholder')"
                required
                class="min-w-48 flex-1"
                :invalid="Boolean(createForm.errors.description)"
                @keydown.esc="adding = false"
            />
            <Select
                v-model="createForm.priority"
                :options="priorityOptions"
                option-label="label"
                option-value="value"
                :aria-label="t('activities.priority')"
                class="w-48"
            >
                <template #value="{ value }">
                    <span class="flex items-center gap-2">
                        <PriorityIcon :priority="value" />
                        {{ t(`activities.priority.${value as Priority}`) }}
                    </span>
                </template>
                <template #option="{ option }">
                    <span class="flex items-center gap-2">
                        <PriorityIcon :priority="option.value" />
                        {{ option.label }}
                    </span>
                </template>
            </Select>
            <Button
                type="button"
                variant="text"
                severity="secondary"
                :label="t('common.cancel')"
                @click="adding = false"
            />
            <Button type="submit" :label="t('activities.add')" :loading="createForm.processing" />
            <p v-if="createForm.errors.description" class="w-full text-sm text-(--danger)">
                {{ createForm.errors.description }}
            </p>
        </form>

        <div v-for="activity in sorted" :key="activity.id" class="group">
            <form
                v-if="editingId === activity.id"
                class="flex flex-wrap items-center gap-2 px-4 py-2"
                @submit.prevent="save(activity)"
            >
                <label :for="`edit-${activity.id}`" class="sr-only">{{ t('activities.descriptionLabel') }}</label>
                <InputText
                    :id="`edit-${activity.id}`"
                    v-model="editForm.description"
                    maxlength="255"
                    required
                    class="min-w-48 flex-1"
                    :invalid="Boolean(editForm.errors.description)"
                    @keydown.esc="editingId = null"
                />
                <Select
                    v-model="editForm.priority"
                    :options="priorityOptions"
                    option-label="label"
                    option-value="value"
                    :aria-label="t('activities.priority')"
                    class="w-48"
                >
                    <template #value="{ value }">
                        <span class="flex items-center gap-2">
                            <PriorityIcon :priority="value" />
                            {{ t(`activities.priority.${value as Priority}`) }}
                        </span>
                    </template>
                    <template #option="{ option }">
                        <span class="flex items-center gap-2">
                            <PriorityIcon :priority="option.value" />
                            {{ option.label }}
                        </span>
                    </template>
                </Select>
                <Button
                    type="button"
                    variant="text"
                    severity="secondary"
                    :label="t('common.cancel')"
                    @click="editingId = null"
                />
                <Button type="submit" :label="t('common.save')" :loading="editForm.processing" />
                <p v-if="editForm.errors.description" class="w-full text-sm text-(--danger)">
                    {{ editForm.errors.description }}
                </p>
            </form>
            <div v-else class="flex items-center gap-3 py-1.5 pr-2 pl-4">
                <PriorityIcon :priority="activity.priority" />
                <span class="min-w-0 flex-1 py-1.5 text-sm break-words">{{ activity.description }}</span>
                <button
                    type="button"
                    class="flex w-40 items-center justify-end gap-1 rounded-(--radius-md) px-2 py-1.5 text-sm text-(--muted) transition-colors hover:bg-(--surface-sunken) hover:text-(--ink)"
                    aria-haspopup="menu"
                    aria-controls="priority-menu"
                    :aria-label="t('activities.changePriority', { description: activity.description })"
                    @click="openPriorityMenu($event, activity)"
                >
                    {{ t(`activities.priority.${activity.priority}`) }}
                    <ChevronDown class="size-4" aria-hidden="true" />
                </button>
                <div class="flex">
                    <Button
                        icon-only
                        variant="text"
                        severity="secondary"
                        class="size-11 shrink-0"
                        :aria-label="`${t('common.edit')} ${activity.description}`"
                        @click="startEditing(activity)"
                    >
                        <Pencil class="size-5" aria-hidden="true" />
                    </Button>
                    <Button
                        icon-only
                        variant="text"
                        severity="secondary"
                        class="size-11 shrink-0 hover:text-(--danger)!"
                        :aria-label="`${t('common.delete')} ${activity.description}`"
                        @click="remove(activity)"
                    >
                        <Trash2 class="size-5" aria-hidden="true" />
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="card mt-8 grid justify-items-center gap-3 px-6 py-12 text-center">
        <p class="text-sm text-(--muted)">{{ t('activities.empty') }}</p>
        <Button variant="outlined" severity="secondary" @click="startAdding">
            <Plus class="size-4" aria-hidden="true" />
            {{ t('activities.new') }}
        </Button>
    </div>

    <Menu id="priority-menu" ref="priorityMenu" :model="priorityItems" popup>
        <template #itemicon="{ item }">
            <PriorityIcon :priority="item.priority" class="mr-2" />
        </template>
    </Menu>
</template>
