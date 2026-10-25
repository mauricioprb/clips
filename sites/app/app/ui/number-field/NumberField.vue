<script setup lang="ts">
import { computed, useId, type HTMLAttributes } from 'vue';
import {
  NumberFieldRoot,
  NumberFieldInput,
  NumberFieldDecrement,
  NumberFieldIncrement,
} from 'reka-ui';
import { Minus, Plus } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  min?: number;
  max?: number;
  step?: number;
  formatOptions?: Intl.NumberFormatOptions;
  locale?: string;
  decrementLabel?: string;
  incrementLabel?: string;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  step: 1,
  decrementLabel: () => useFuseLocale().numberField.decrementLabel,
  incrementLabel: () => useFuseLocale().numberField.incrementLabel,
});

const model = defineModel<number>();

defineOptions({ inheritAttrs: false });

const autoId = useId();
const fieldId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${fieldId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);

const stepperClass =
  'inline-flex shrink-0 cursor-pointer items-center justify-center px-2.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:relative focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-40 data-[disabled]:pointer-events-none data-[disabled]:opacity-40 [&_svg]:size-4';
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="fieldId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <NumberFieldRoot
      :id="fieldId"
      v-model="model"
      :min="min"
      :max="max"
      :step="step"
      :format-options="formatOptions"
      :locale="locale"
      :disabled="disabled"
      :class="[
        inputWrapperVariants({ status: resolvedStatus }),
        'items-stretch',
        disabled && 'pointer-events-none bg-muted opacity-60',
      ]"
    >
      <NumberFieldDecrement as-child>
        <button
          type="button"
          :aria-label="decrementLabel"
          :class="cn(stepperClass, 'border-r border-border')"
        >
          <Minus />
        </button>
      </NumberFieldDecrement>

      <NumberFieldInput
        v-bind="$attrs"
        :placeholder="placeholder"
        :aria-invalid="resolvedStatus === 'error' || undefined"
        :aria-describedby="message ? messageId : undefined"
        class="min-w-0 flex-1 bg-transparent px-3 py-1.5 text-center tabular-nums outline-none placeholder:text-muted-foreground"
      />

      <NumberFieldIncrement as-child>
        <button
          type="button"
          :aria-label="incrementLabel"
          :class="cn(stepperClass, 'border-l border-border')"
        >
          <Plus />
        </button>
      </NumberFieldIncrement>
    </NumberFieldRoot>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
