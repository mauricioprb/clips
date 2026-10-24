<script setup lang="ts">
import { computed, useId, type Component, type HTMLAttributes } from 'vue';
import { CircleX } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import { inputWrapperVariants, type InputWrapperVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  type?: string;
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  iconLeading?: Component;
  iconTrailing?: Component;
  clearable?: boolean;
  clearLabel?: string;
  maxlength?: number;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  status: 'default',
  clearLabel: () => useFuseLocale().input.clearLabel,
});

const modelValue = defineModel<string | number>();

defineOptions({ inheritAttrs: false });

const autoId = useId();
const inputId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${inputId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);
const length = computed(() => String(modelValue.value ?? '').length);
const hasValue = computed(() => modelValue.value !== undefined && modelValue.value !== '');

function onInput(event: Event) {
  modelValue.value = (event.target as HTMLInputElement).value;
}
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="inputId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <div
      :class="[
        inputWrapperVariants({ status: resolvedStatus }),
        disabled && 'pointer-events-none bg-muted opacity-60',
      ]"
    >
      <slot name="prefix" />

      <div class="flex min-w-0 flex-1 items-center gap-2 px-3 py-1.5">
        <span v-if="$slots.icon || iconLeading" class="shrink-0 opacity-70 [&_svg]:size-4">
          <slot name="icon"><component :is="iconLeading" /></slot>
        </span>
        <input
          :id="inputId"
          v-bind="$attrs"
          :type="type"
          :required="required"
          :value="modelValue"
          :placeholder="placeholder"
          :disabled="disabled"
          :maxlength="maxlength"
          :aria-invalid="resolvedStatus === 'error' || undefined"
          :aria-describedby="message ? messageId : undefined"
          class="min-w-0 flex-1 bg-transparent outline-none placeholder:text-muted-foreground"
          @input="onInput"
        />
        <span v-if="iconTrailing" class="shrink-0 opacity-70 [&_svg]:size-4">
          <component :is="iconTrailing" />
        </span>
        <button
          v-if="clearable && hasValue && !disabled"
          type="button"
          :aria-label="clearLabel"
          class="shrink-0 cursor-pointer rounded-sm opacity-60 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
          @click="modelValue = undefined"
        >
          <CircleX class="size-5" />
        </button>
      </div>

      <slot name="suffix" />
    </div>

    <div v-if="message || maxlength" class="flex items-start justify-between gap-2">
      <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
        {{ message }}
      </FieldMessage>
      <span v-else />
      <span v-if="maxlength" class="shrink-0 text-caption text-muted-foreground tabular-nums">
        {{ length }}/{{ maxlength }}
      </span>
    </div>
  </div>
</template>
