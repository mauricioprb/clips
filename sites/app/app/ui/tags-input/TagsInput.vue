<script setup lang="ts">
import { computed, useId, type HTMLAttributes } from 'vue';
import {
  TagsInputRoot,
  TagsInputInput,
  TagsInputItem,
  TagsInputItemText,
  TagsInputItemDelete,
} from 'reka-ui';
import { X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';
import { badgeVariants } from '@/ui/badge';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  max?: number;
  delimiter?: string;
  addOnPaste?: boolean;
  addOnBlur?: boolean;
  duplicate?: boolean;
  deleteLabel?: string;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  delimiter: ',',
  addOnPaste: true,
  addOnBlur: true,
  duplicate: false,
  deleteLabel: () => useFuseLocale().tagsInput.deleteLabel,
});

const model = defineModel<string[]>({ default: () => [] });

defineOptions({ inheritAttrs: false });

const autoId = useId();
const inputId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${inputId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="inputId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <TagsInputRoot
      v-model="model"
      :disabled="disabled"
      :delimiter="delimiter"
      :add-on-paste="addOnPaste"
      :add-on-blur="addOnBlur"
      :duplicate="duplicate"
      :max="max"
      :class="[
        inputWrapperVariants({ status: resolvedStatus }),
        disabled && 'pointer-events-none bg-muted opacity-60',
      ]"
    >
      <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5 px-3 py-1.5">
        <TagsInputItem
          v-for="tag in model"
          :key="tag"
          :value="tag"
          :class="badgeVariants({ intent: 'neutral', variant: 'soft', size: 'sm' })"
        >
          <TagsInputItemText />
          <TagsInputItemDelete
            :aria-label="`${deleteLabel} ${tag}`"
            class="-mr-0.5 ml-0.5 inline-flex cursor-pointer rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
          >
            <X class="size-3" />
          </TagsInputItemDelete>
        </TagsInputItem>

        <TagsInputInput
          :id="inputId"
          v-bind="$attrs"
          :placeholder="placeholder"
          :aria-invalid="resolvedStatus === 'error' || undefined"
          :aria-describedby="message ? messageId : undefined"
          class="min-w-24 flex-1 bg-transparent text-body-sm outline-none placeholder:text-muted-foreground"
        />
      </div>
    </TagsInputRoot>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
