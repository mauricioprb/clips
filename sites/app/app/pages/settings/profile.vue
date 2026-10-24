<script setup lang="ts">
import { RefreshCw } from '@lucide/vue';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import { PageHeader } from '~/ui/page-header';
import { SettingsRow, SettingsSection } from '~/ui/settings-section';
import { Skeleton } from '~/ui/skeleton';
import { TagsInput } from '~/ui/tags-input';
import { toast } from '~/ui/toast';

useHead({ title: 'Dados da bolsa' });

const { user, updateName } = useAuth();
const { profile, load, save } = useProfile();

const name = ref('');
const advisorName = ref('');
const scholarshipName = ref('');
const laboratories = ref<string[]>([]);
const weeklyHours = ref(20);
const loading = ref(true);
const loadError = ref('');
const error = ref('');
const saving = ref(false);

const dailyTarget = computed(() => formatDuration(Math.round((weeklyHours.value * 60) / 5)));

function fillForm() {
  name.value = user.value?.name ?? '';
  advisorName.value = profile.value?.advisorName ?? '';
  scholarshipName.value = profile.value?.scholarshipName ?? '';
  laboratories.value = [...(profile.value?.laboratories ?? [])];
  weeklyHours.value = (profile.value?.weeklyWorkloadMinutes ?? 1200) / 60;
}

async function submit() {
  if (saving.value) return;
  error.value = '';

  if (!name.value.trim()) {
    error.value = 'Informe o seu nome completo.';
    return;
  }

  saving.value = true;
  try {
    if (name.value.trim() !== user.value?.name) {
      await updateName(name.value.trim());
    }
    await save({
      advisorName: advisorName.value,
      scholarshipName: scholarshipName.value,
      laboratories: laboratories.value,
      weeklyWorkloadMinutes: Math.round(Number(weeklyHours.value) * 60),
    });
    toast.success('Dados salvos.');
  } catch (exception) {
    error.value = errorMessage(exception);
  } finally {
    saving.value = false;
  }
}

async function loadProfile() {
  loading.value = true;
  loadError.value = '';
  try {
    await load();
    fillForm();
  } catch (exception) {
    loadError.value = errorMessage(exception);
  } finally {
    loading.value = false;
  }
}

onMounted(loadProfile);
</script>

<template>
  <div class="flex flex-col gap-6">
    <PageHeader title="Configurações" :bordered="false" />
    <SettingsNav />

    <div v-if="loadError" class="flex flex-col items-start gap-4">
      <Alert intent="danger" variant="soft">{{ loadError }}</Alert>
      <Button type="button" variant="outline" :icon-leading="RefreshCw" @click="loadProfile">
        Tentar novamente
      </Button>
    </div>
    <div v-else-if="loading" role="status" class="flex flex-col gap-4">
      <span class="sr-only">Carregando os dados da bolsa…</span>
      <Skeleton class="h-40 w-full" /><Skeleton class="h-40 w-full" />
    </div>
    <form v-else class="flex flex-col gap-8" @submit.prevent="submit">
      <Alert v-if="error" intent="danger" variant="soft">{{ error }}</Alert>

      <SettingsSection
        title="Bolsista"
        description="O seu nome vai no relatório. Para trocar o e-mail, use a seção Conta e senha."
      >
        <SettingsRow label="Nome completo" for="profile-name">
          <Input id="profile-name" v-model="name" autocomplete="name" :maxlength="128" required />
        </SettingsRow>
      </SettingsSection>

      <SettingsSection title="Bolsa" description="Estes dados aparecem no cabeçalho do relatório.">
        <SettingsRow label="Orientador" for="profile-advisor">
          <Input
            id="profile-advisor"
            v-model="advisorName"
            placeholder="Nome completo do orientador"
            :maxlength="255"
            required
          />
        </SettingsRow>
        <SettingsRow label="Bolsa" for="profile-scholarship">
          <Input
            id="profile-scholarship"
            v-model="scholarshipName"
            placeholder="Ex.: CAPES"
            :maxlength="255"
            required
          />
        </SettingsRow>
        <SettingsRow
          label="Laboratórios ou salas"
          description="Digite o nome e pressione Enter."
          for="profile-laboratories"
        >
          <TagsInput
            id="profile-laboratories"
            v-model="laboratories"
            placeholder="Ex.: Laboratório de Nanociências"
            :max="10"
            delete-label="Remover"
            add-on-blur
          />
        </SettingsRow>
      </SettingsSection>

      <SettingsSection
        title="Carga horária"
        description="Define a meta de cada dia útil ao completar o mês."
      >
        <SettingsRow
          label="Horas por semana"
          :description="`Meta de ${dailyTarget} por dia útil.`"
          for="profile-hours"
        >
          <Input
            id="profile-hours"
            v-model.number="weeklyHours"
            type="number"
            min="1"
            max="44"
            step="0.5"
            required
            class="sm:w-32"
          />
        </SettingsRow>
      </SettingsSection>

      <div class="flex justify-end">
        <Button type="submit" :loading="saving">Salvar dados</Button>
      </div>
    </form>
  </div>
</template>
