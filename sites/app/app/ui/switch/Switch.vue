<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import { cn } from '@/lib/utils';
import { switchThumbVariants, switchTrackVariants, type SwitchVariants } from '.';

interface Props {
  size?: SwitchVariants['size'];
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
});

const checked = defineModel<boolean>({ default: false });
</script>

<template>
  <SwitchRoot
    v-model="checked"
    :disabled="disabled"
    :class="cn(switchTrackVariants({ size }), props.class)"
  >
    <SwitchThumb :class="switchThumbVariants({ size })" />
  </SwitchRoot>
</template>
