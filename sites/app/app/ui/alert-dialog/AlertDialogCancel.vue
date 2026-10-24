<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { AlertDialogCancel } from 'reka-ui';
import { Button, type ButtonVariants } from '@/ui/button';

interface Props {
  variant?: ButtonVariants['variant'];
  tone?: ButtonVariants['tone'];
  size?: ButtonVariants['size'];
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'outline',
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <AlertDialogCancel as-child>
    <Button
      v-bind="$attrs"
      :variant="variant"
      :tone="tone"
      :size="size"
      :disabled="disabled"
      :class="props.class"
    >
      <slot />
    </Button>
  </AlertDialogCancel>
</template>
