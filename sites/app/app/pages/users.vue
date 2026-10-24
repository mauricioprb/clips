<script setup lang="ts">
import { MoreHorizontal, RefreshCw, UserPlus } from '@lucide/vue';
import type { Member, MemberStatus } from '~/composables/useMembers';
import { Alert } from '~/ui/alert';
import { Badge } from '~/ui/badge';
import { Button } from '~/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '~/ui/dialog';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '~/ui/dropdown-menu';
import Input from '~/ui/input/Input.vue';
import { Label } from '~/ui/label';
import { PageHeader } from '~/ui/page-header';
import { Skeleton } from '~/ui/skeleton';
import { Switch } from '~/ui/switch';
import { toast } from '~/ui/toast';

definePageMeta({ adminOnly: true });
useHead({ title: 'Usuários' });

const { members, load, invite, resendInvitation, setAdmin, setActive } = useMembers();

const statusLabels: Record<MemberStatus, string> = {
  active: 'Ativo',
  invitation_pending: 'Convite pendente',
  blocked: 'Bloqueado',
};
const statusIntents: Record<MemberStatus, 'success' | 'warning' | 'danger'> = {
  active: 'success',
  invitation_pending: 'warning',
  blocked: 'danger',
};

const loading = ref(true);
const loadError = ref('');
const inviteOpen = ref(false);
const name = ref('');
const email = ref('');
const makeAdmin = ref(false);
const inviteError = ref('');
const inviting = ref(false);
const blocking = ref<Member | null>(null);
const working = ref(false);

async function loadMembers() {
  loading.value = true;
  loadError.value = '';
  try {
    await load();
  } catch (exception) {
    loadError.value = errorMessage(exception);
  } finally {
    loading.value = false;
  }
}

function openInvite() {
  name.value = '';
  email.value = '';
  makeAdmin.value = false;
  inviteError.value = '';
  inviteOpen.value = true;
}

async function sendInvite() {
  if (inviting.value) return;
  inviteError.value = '';
  inviting.value = true;
  try {
    await invite(name.value.trim(), email.value.trim(), makeAdmin.value);
    toast.success('Convite enviado.');
    inviteOpen.value = false;
  } catch (exception) {
    inviteError.value = errorMessage(exception);
  } finally {
    inviting.value = false;
  }
}

async function run(action: () => Promise<void>, message: string) {
  if (working.value) return;
  working.value = true;
  try {
    await action();
    toast.success(message);
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    working.value = false;
  }
}

async function confirmBlock() {
  const member = blocking.value;
  if (!member) return;
  await run(() => setActive(member, false), 'Usuário bloqueado.');
  blocking.value = null;
}

onMounted(loadMembers);
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader
      title="Usuários"
      description="Quem pode acessar o Clips. O acesso é só por convite: a pessoa recebe um link por e-mail e cria a própria senha."
      :bordered="false"
    >
      <template #actions>
        <Button type="button" :icon-leading="UserPlus" @click="openInvite">Convidar pessoa</Button>
      </template>
    </PageHeader>

    <div v-if="loadError" class="flex flex-col items-start gap-4">
      <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
      <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadMembers">
        Tentar novamente
      </Button>
    </div>
    <div v-else-if="loading" role="status" class="flex flex-col gap-4">
      <span class="sr-only">Carregando os usuários…</span>
      <Skeleton class="h-12 w-full" /><Skeleton class="h-12 w-full" />
    </div>
    <ul v-else class="divide-y divide-border rounded-xl border border-border bg-card">
      <li
        v-for="member in members"
        :key="member.userId"
        class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3"
      >
        <span class="flex min-w-0 flex-1 flex-col">
          <span class="text-body-sm font-medium wrap-anywhere">
            {{ member.name || member.email }}
            <span v-if="member.isSelf" class="text-muted-foreground">(você)</span>
          </span>
          <span class="text-caption text-muted-foreground wrap-anywhere">{{ member.email }}</span>
          <span
            v-if="member.status === 'invitation_pending'"
            class="text-caption text-muted-foreground"
          >
            Convite enviado em {{ formatDate(member.invitedAt.slice(0, 10)) }}
          </span>
        </span>
        <Badge v-if="member.isAdmin" intent="primary" size="sm">Administra</Badge>
        <Badge :intent="statusIntents[member.status]" size="sm">
          {{ statusLabels[member.status] }}
        </Badge>
        <DropdownMenu v-if="!member.isSelf">
          <DropdownMenuTrigger as-child>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              icon-only
              :icon-leading="MoreHorizontal"
              :aria-label="`Ações para ${member.name || member.email}`"
              :disabled="working"
            />
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem
              v-if="member.status === 'invitation_pending'"
              @select="run(() => resendInvitation(member), 'Convite reenviado.')"
            >
              Reenviar convite
            </DropdownMenuItem>
            <DropdownMenuItem
              @select="
                run(
                  () => setAdmin(member, !member.isAdmin),
                  member.isAdmin
                    ? 'Permissão de administrar removida.'
                    : 'Agora essa pessoa pode convidar e gerenciar usuários.',
                )
              "
            >
              {{ member.isAdmin ? 'Remover permissão de administrar' : 'Permitir administrar' }}
            </DropdownMenuItem>
            <DropdownMenuItem
              v-if="member.status === 'blocked'"
              @select="run(() => setActive(member, true), 'Usuário desbloqueado.')"
            >
              Desbloquear acesso
            </DropdownMenuItem>
            <DropdownMenuItem v-else variant="danger" @select="blocking = member">
              Bloquear acesso
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </li>
    </ul>

    <Dialog v-model:open="inviteOpen">
      <DialogContent close-label="Fechar">
        <DialogHeader>
          <DialogTitle>Convidar pessoa</DialogTitle>
          <DialogDescription>
            Enviaremos um link para a pessoa criar a senha. O link vale até ser usado ou reenviado.
          </DialogDescription>
        </DialogHeader>
        <form id="invite-form" class="flex flex-col gap-4" @submit.prevent="sendInvite">
          <Alert v-if="inviteError" intent="danger" variant="soft">{{ inviteError }}</Alert>
          <Input id="invite-name" v-model="name" label="Nome completo" :maxlength="128" required />
          <Input id="invite-email" v-model="email" label="E-mail" type="email" required />
          <div class="flex items-start gap-3">
            <Switch id="invite-admin" v-model="makeAdmin" />
            <div class="flex flex-col gap-0.5">
              <Label for="invite-admin">Pode convidar e gerenciar usuários</Label>
              <p class="text-caption text-muted-foreground">
                Dê essa permissão só a quem coordena as bolsas.
              </p>
            </div>
          </div>
        </form>
        <DialogFooter>
          <Button type="button" variant="ghost" @click="inviteOpen = false">Cancelar</Button>
          <Button type="submit" form="invite-form" :loading="inviting">Enviar convite</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <ConfirmDialog
      :open="blocking !== null"
      :title="`Bloquear ${blocking?.name || blocking?.email || ''}?`"
      description="A pessoa perde o acesso na hora. Os lançamentos dela continuam guardados e o acesso pode ser devolvido depois."
      confirm-label="Bloquear acesso"
      tone="danger"
      :loading="working"
      @update:open="(open) => !open && (blocking = null)"
      @confirm="confirmBlock"
    />
  </div>
</template>
