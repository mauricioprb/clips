<script setup lang="ts">
import type { NuxtError } from '#app';
import { FileQuestion, RefreshCw } from '@lucide/vue';
import { Button } from '~/ui/button';
import { EmptyState } from '~/ui/empty-state';

const props = defineProps<{ error: NuxtError }>();
const isNotFound = computed(() => props.error.statusCode === 404);
const message = computed(() =>
  isNotFound.value ? 'Esta página não existe.' : 'Não foi possível abrir o aplicativo.',
);
</script>

<template>
  <main class="relative flex min-h-svh flex-col items-center justify-center gap-8 bg-muted/40 p-6">
    <div class="absolute top-5 right-5 sm:top-8 sm:right-8"><ThemeToggle /></div>
    <AppBrand />
    <EmptyState
      :title="message"
      :description="
        isNotFound
          ? 'Confira o endereço ou volte para o mês atual.'
          : 'Tente novamente em instantes.'
      "
      class="w-full max-w-lg rounded-xl border border-border bg-card"
    >
      <template #icon><FileQuestion aria-hidden="true" /></template>
      <template #action>
        <Button type="button" :icon-leading="RefreshCw" @click="clearError({ redirect: '/' })">
          {{ isNotFound ? 'Voltar para o mês atual' : 'Tentar novamente' }}
        </Button>
      </template>
    </EmptyState>
  </main>
</template>
