<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { AvatarFallback, AvatarImage, AvatarRoot, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { avatarVariants, type AvatarVariants } from '.';

interface Props extends PrimitiveProps {
  src?: string;
  alt?: string;
  initials?: string;
  seed?: string;
  delayMs?: number;
  shape?: AvatarVariants['shape'];
  size?: AvatarVariants['size'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'span',
  alt: '',
  shape: 'circle',
  size: 'md',
});

const fills = ['fill-primary', 'fill-primary/45', 'fill-primary-active'];

const compositions = [
  [
    { tag: 'circle', attrs: { cx: 30, cy: 10, r: 18 }, fill: 0 },
    { tag: 'rect', attrs: { x: 2, y: 22, width: 18, height: 18 }, fill: 1 },
    { tag: 'circle', attrs: { cx: 14, cy: 14, r: 5 }, fill: 2 },
  ],
  [
    { tag: 'path', attrs: { d: 'M0 40 A20 20 0 0 1 40 40 Z' }, fill: 0 },
    { tag: 'circle', attrs: { cx: 20, cy: 14, r: 7 }, fill: 1 },
  ],
  [
    { tag: 'circle', attrs: { cx: 26, cy: 26, r: 18 }, fill: 0 },
    { tag: 'circle', attrs: { cx: 26, cy: 26, r: 9 }, fill: 2 },
    { tag: 'circle', attrs: { cx: 9, cy: 9, r: 5 }, fill: 1 },
  ],
  [
    { tag: 'path', attrs: { d: 'M0 0 H24 A24 24 0 0 1 0 24 Z' }, fill: 0 },
    { tag: 'rect', attrs: { x: 22, y: 22, width: 18, height: 18 }, fill: 1 },
    { tag: 'circle', attrs: { cx: 31, cy: 9, r: 4 }, fill: 2 },
  ],
];

function hash(value: string) {
  let result = 5381;
  for (const char of value) result = (result * 33) ^ char.charCodeAt(0);
  return result >>> 0;
}

const art = computed(() => {
  if (!props.seed) return undefined;
  const value = hash(props.seed);
  const offset = value % fills.length;
  return {
    rotation: ((value >>> 3) % 4) * 90,
    shapes: (compositions[(value >>> 5) % compositions.length] ?? []).map((shape) => ({
      ...shape,
      class: fills[(shape.fill + offset) % fills.length],
    })),
  };
});
</script>

<template>
  <AvatarRoot
    :as="as"
    :as-child="asChild"
    :class="cn(avatarVariants({ shape, size }), props.class)"
  >
    <AvatarImage
      v-if="src"
      :src="src"
      :alt="alt"
      :role="alt ? 'img' : 'presentation'"
      class="absolute inset-0 size-full object-cover"
    />
    <AvatarFallback
      :delay-ms="delayMs"
      :class="
        cn(
          'absolute inset-0 flex items-center justify-center rounded-[inherit] select-none font-medium text-muted-foreground',
          !(art && !initials) && 'bg-accent',
        )
      "
    >
      <slot name="fallback">
        <svg
          v-if="art && !initials"
          viewBox="0 0 40 40"
          aria-hidden="true"
          class="absolute inset-0 size-full overflow-hidden rounded-[inherit]"
        >
          <rect width="40" height="40" class="fill-primary-soft" />
          <g :transform="`rotate(${art.rotation} 20 20)`">
            <component
              :is="part.tag"
              v-for="(part, index) in art.shapes"
              :key="index"
              v-bind="part.attrs"
              :class="part.class"
            />
          </g>
        </svg>
        <template v-else>{{ initials }}</template>
      </slot>
    </AvatarFallback>
  </AvatarRoot>
</template>
