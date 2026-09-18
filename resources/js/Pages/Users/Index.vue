<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Ban, EllipsisVertical, Plus, RotateCcw, Send, ShieldCheck, ShieldOff } from '@lucide/vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Menu from 'primevue/menu';
import { type MenuItem } from 'primevue/menuitem';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { useI18n } from '@/Composables/useI18n';
import { formatDate } from '@/Lib/dates';
import type { ManagedUser, UserStatus } from '@/types/models';

defineOptions({ layout: AppLayout });

defineProps<{ users: ManagedUser[] }>();

const { t } = useI18n();
const confirm = useConfirm();
const inviting = ref(false);
const target = ref<ManagedUser | null>(null);
const actionsMenu = ref<InstanceType<typeof Menu> | null>(null);
const form = useForm({ name: '', email: '', is_admin: false });

const severities: Record<UserStatus, 'success' | 'warn' | 'danger'> = {
    active: 'success',
    invitation_pending: 'warn',
    blocked: 'danger',
};

function openInvite(): void {
    form.reset();
    form.clearErrors();
    inviting.value = true;
}

function invite(): void {
    form.post('/usuarios', { preserveScroll: true, onSuccess: () => (inviting.value = false) });
}

function resend(user: ManagedUser): void {
    router.post(`/usuarios/${user.id}/convite`, {}, { preserveScroll: true });
}

function setAdmin(user: ManagedUser, isAdmin: boolean): void {
    router.put(`/usuarios/${user.id}`, { is_admin: isAdmin }, { preserveScroll: true });
}

const actions = computed<MenuItem[]>(() => {
    const user = target.value;

    if (!user) {
        return [];
    }

    return [
        {
            label: t('users.resend'),
            icon: Send,
            visible: user.status === 'invitation_pending',
            command: () => resend(user),
        },
        {
            label: user.isAdmin ? t('users.revokeAdmin') : t('users.grantAdmin'),
            icon: user.isAdmin ? ShieldOff : ShieldCheck,
            command: () => setAdmin(user, !user.isAdmin),
        },
        { separator: true },
        {
            label: user.status === 'blocked' ? t('users.unblock') : t('users.block'),
            icon: user.status === 'blocked' ? RotateCcw : Ban,
            class: user.status === 'blocked' ? undefined : 'text-(--danger)',
            command: () => setActive(user, user.status === 'blocked'),
        },
    ];
});

function openActions(event: Event, user: ManagedUser): void {
    target.value = user;
    actionsMenu.value?.toggle(event);
}

function setActive(user: ManagedUser, active: boolean): void {
    const submit = () => router.put(`/usuarios/${user.id}`, { active }, { preserveScroll: true });

    if (active) {
        submit();

        return;
    }

    confirm.require({
        header: t('users.blockTitle', { name: user.name }),
        message: t('users.blockBody'),
        acceptProps: { label: t('users.block'), severity: 'danger' },
        rejectProps: { label: t('common.cancel'), severity: 'secondary', variant: 'text' },
        accept: submit,
    });
}
</script>

<template>
    <Head :title="t('users.title')" />

    <PageHeader :title="t('users.title')" :description="t('users.description')">
        <template #actions>
            <Button @click="openInvite">
                <Plus class="size-4" aria-hidden="true" />
                {{ t('users.invite') }}
            </Button>
        </template>
    </PageHeader>

    <ul class="card mt-8 divide-y divide-(--line)">
        <li v-for="user in users" :key="user.id" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3">
            <div class="min-w-0 flex-1">
                <p class="flex flex-wrap items-center gap-2 text-sm font-medium">
                    {{ user.name }}
                    <span v-if="user.isSelf" class="text-xs font-normal text-(--muted)">{{ t('users.you') }}</span>
                    <span v-if="user.isAdmin" class="text-xs font-normal text-(--muted)">{{ t('users.admin') }}</span>
                </p>
                <p class="truncate text-sm text-(--muted)">{{ user.email }}</p>
                <p v-if="user.status === 'invitation_pending' && user.invitationSentAt" class="text-xs text-(--muted)">
                    {{ t('users.invitedAt', { date: formatDate(user.invitationSentAt.slice(0, 10)) }) }}
                </p>
            </div>
            <Tag :severity="severities[user.status]" :value="t(`users.status.${user.status}`)" />
            <Button
                v-if="!user.isSelf"
                icon-only
                variant="text"
                severity="secondary"
                aria-haspopup="menu"
                aria-controls="user-actions"
                :aria-label="t('users.actions', { name: user.name })"
                @click="openActions($event, user)"
            >
                <EllipsisVertical class="size-4" aria-hidden="true" />
            </Button>
            <span v-else class="size-9" aria-hidden="true" />
        </li>
    </ul>

    <Menu id="user-actions" ref="actionsMenu" :model="actions" popup />

    <Dialog v-model:visible="inviting" modal :header="t('users.invite')" class="w-[min(100%-2rem,28rem)]">
        <form class="grid gap-4" @submit.prevent="invite">
            <p class="text-sm text-(--muted)">{{ t('users.inviteHint') }}</p>
            <FormField
                id="invite-name"
                v-slot="{ id, invalid, describedBy }"
                :label="t('users.name')"
                :error="form.errors.name"
            >
                <InputText
                    :id="id"
                    v-model="form.name"
                    required
                    fluid
                    autofocus
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>
            <FormField
                id="invite-email"
                v-slot="{ id, invalid, describedBy }"
                :label="t('auth.email')"
                :error="form.errors.email"
            >
                <InputText
                    :id="id"
                    v-model="form.email"
                    type="email"
                    required
                    fluid
                    :invalid="invalid"
                    :aria-describedby="describedBy"
                />
            </FormField>
            <label class="flex items-start gap-2 text-sm">
                <Checkbox v-model="form.is_admin" binary input-id="invite-admin" class="mt-0.5" />
                <span>
                    {{ t('users.makeAdmin') }}
                    <span class="block text-xs text-(--muted)">{{ t('users.makeAdminHint') }}</span>
                </span>
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <Button
                    type="button"
                    variant="text"
                    severity="secondary"
                    :label="t('common.cancel')"
                    @click="inviting = false"
                />
                <Button type="submit" :label="t('users.send')" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
