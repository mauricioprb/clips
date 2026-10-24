<script setup lang="ts">
import { computed, useId, type HTMLAttributes } from 'vue';
import { ComboboxRoot } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Badge } from '@/ui/badge';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import type { InputWrapperVariants } from '@/ui/input';
import ComboboxAnchor from './ComboboxAnchor.vue';
import ComboboxInput from './ComboboxInput.vue';
import ComboboxTrigger from './ComboboxTrigger.vue';
import ComboboxContent from './ComboboxContent.vue';
import ComboboxItem from './ComboboxItem.vue';
import ComboboxEmpty from './ComboboxEmpty.vue';
import type { ComboboxOption } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  options?: ComboboxOption[];
  multiple?: boolean;
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  emptyText?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  options: () => [],
  emptyText: () => useFuseLocale().combobox.emptyText,
});

const model = defineModel<string | string[]>();

defineOptions({ inheritAttrs: false });

const autoId = useId();
const inputId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${inputId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);

const selectedOptions = computed(() =>
  (Array.isArray(model.value) ? model.value : [])
    .map((value) => props.options.find((o) => o.value === value))
    .filter((o): o is ComboboxOption => Boolean(o)),
);

function displayValue(value: unknown) {
  if (props.multiple) return '';
  return props.options.find((o) => o.value === value)?.label ?? '';
}

function removeValue(value: string) {
  if (!Array.isArray(model.value)) return;
  model.value = model.value.filter((v) => v !== value);
}
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="inputId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <ComboboxRoot v-model="model" :multiple="multiple" :disabled="disabled">
      <ComboboxAnchor :status="resolvedStatus" :disabled="disabled">
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
          <span
            v-if="$slots.icon"
            class="inline-flex shrink-0 items-center opacity-70 [&_svg]:size-4"
          >
            <slot name="icon" />
          </span>
          <Badge
            v-for="opt in selectedOptions"
            :key="opt.value"
            intent="neutral"
            variant="soft"
            size="sm"
            dismissible
            @close="removeValue(opt.value)"
          >
            {{ opt.label }}
          </Badge>
          <ComboboxInput
            :id="inputId"
            v-bind="$attrs"
            :display-value="displayValue"
            :placeholder="placeholder ?? 'Search…'"
            :aria-invalid="resolvedStatus === 'error' || undefined"
            :aria-describedby="message ? messageId : undefined"
          />
        </div>
        <ComboboxTrigger />
      </ComboboxAnchor>

      <ComboboxContent>
        <ComboboxEmpty>{{ emptyText }}</ComboboxEmpty>
        <ComboboxItem
          v-for="opt in options"
          :key="opt.value"
          :value="opt.value"
          :disabled="opt.disabled"
        >
          {{ opt.label }}
        </ComboboxItem>
      </ComboboxContent>
    </ComboboxRoot>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
