<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed, ref } from 'vue';
import { CollapsibleRoot } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
  defaultOpen: { type: Boolean, default: undefined },
  open: { type: Boolean, default: undefined },
  disabled: { type: Boolean, default: undefined },
  class: { type: String as () => HTMLAttributes['class'], default: undefined },
});

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const isControlled = computed(() => props.open !== undefined);

const localOpen = ref(props.defaultOpen ?? false);

const open = computed<boolean>({
  get: () => (isControlled.value ? props.open! : localOpen.value),
  set: (v) => {
    if (isControlled.value) {
      emit('update:open', v);
    } else {
      localOpen.value = v;
    }
  },
});
</script>

<template>
  <CollapsibleRoot
    v-model:open="open"
    :default-open="defaultOpen"
    :disabled="disabled"
    :class="cn(props.class)"
  >
    <slot />
  </CollapsibleRoot>
</template>
