<script setup lang="ts">
import { computed, useId, type HTMLAttributes } from 'vue';
import { SelectRoot, SelectValue } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import type { InputWrapperVariants } from '@/ui/input';
import SelectTrigger from './SelectTrigger.vue';
import SelectContent from './SelectContent.vue';
import SelectItem from './SelectItem.vue';
import type { SelectOption } from '.';

interface Props {
  options?: SelectOption[];
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  options: () => [],
});

const model = defineModel<string>();

defineOptions({ inheritAttrs: false });

const autoId = useId();
const selectId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${selectId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="selectId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <SelectRoot v-model="model" :disabled="disabled">
      <SelectTrigger
        :id="selectId"
        v-bind="$attrs"
        :status="resolvedStatus"
        :aria-invalid="resolvedStatus === 'error' || undefined"
        :aria-describedby="message ? messageId : undefined"
      >
        <span
          v-if="$slots.icon"
          class="inline-flex shrink-0 items-center opacity-70 [&_svg]:size-4"
        >
          <slot name="icon" />
        </span>
        <SelectValue :placeholder="placeholder ?? 'Select…'" class="min-w-0 flex-1 truncate" />
      </SelectTrigger>

      <SelectContent>
        <SelectItem
          v-for="opt in options"
          :key="opt.value"
          :value="opt.value"
          :disabled="opt.disabled"
        >
          {{ opt.label }}
        </SelectItem>
      </SelectContent>
    </SelectRoot>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
