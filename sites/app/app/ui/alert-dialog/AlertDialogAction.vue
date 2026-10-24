<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { AlertDialogAction } from 'reka-ui';
import { Button, type ButtonVariants } from '@/ui/button';

interface Props {
  variant?: ButtonVariants['variant'];
  tone?: ButtonVariants['tone'];
  size?: ButtonVariants['size'];
  loading?: boolean;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <AlertDialogAction as-child>
    <Button
      v-bind="$attrs"
      :variant="variant"
      :tone="tone"
      :size="size"
      :loading="loading"
      :disabled="disabled"
      :class="props.class"
    >
      <slot />
    </Button>
  </AlertDialogAction>
</template>
