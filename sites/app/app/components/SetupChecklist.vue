<script setup lang="ts">
import { NuxtLink } from '#components';
import { Alert } from '~/ui/alert';
import { Button } from '~/ui/button';

defineProps<{ setup: { profile: boolean; schedule: boolean; activities: boolean } }>();
</script>

<template>
  <Alert intent="info" variant="soft">
    {{
      setup.profile
        ? 'Cadastre atividades padrão para completar os dias livres do mês.'
        : 'Preencha os dados da bolsa para identificar o seu relatório.'
    }}
    <template #action>
      <Button
        :as="NuxtLink"
        :to="setup.profile ? '/activities' : '/settings/profile'"
        variant="outline"
        size="sm"
        class="border-current text-current hover:border-current hover:bg-current/10 active:bg-current/20"
      >
        {{ setup.profile ? 'Cadastrar atividades' : 'Preencher dados da bolsa' }}
      </Button>
    </template>
  </Alert>
</template>
