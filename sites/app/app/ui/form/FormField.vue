<script setup lang="ts">
import { computed, useId, type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';

interface Props {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  disabled?: boolean;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

const autoId = useId();
const fieldId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${fieldId.value}-message`);
const status = computed(() => (props.error ? 'error' : 'default'));
const message = computed(() => props.error ?? props.hint);

const controlProps = computed(() => ({
  id: fieldId.value,
  required: props.required || undefined,
  disabled: props.disabled || undefined,
  'aria-invalid': props.error ? true : undefined,
  'aria-describedby': message.value ? messageId.value : undefined,
}));
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="fieldId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <slot :id="fieldId" :control-props="controlProps" :status="status" />

    <FieldMessage v-if="message" :id="messageId" :status="status">
      {{ message }}
    </FieldMessage>
  </div>
</template>
