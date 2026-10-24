<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '~/ui/alert-dialog';

withDefaults(
  defineProps<{
    title: string;
    description: string;
    confirmLabel: string;
    tone?: 'default' | 'danger';
    loading?: boolean;
  }>(),
  { tone: 'default', loading: false },
);

const open = defineModel<boolean>('open', { default: false });
defineEmits<{ confirm: [] }>();
</script>

<template>
  <AlertDialog v-model:open="open">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
      </AlertDialogHeader>
      <slot />
      <AlertDialogFooter>
        <AlertDialogCancel variant="ghost">Cancelar</AlertDialogCancel>
        <AlertDialogAction :tone="tone" :loading="loading" @click.prevent="$emit('confirm')">
          {{ confirmLabel }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
