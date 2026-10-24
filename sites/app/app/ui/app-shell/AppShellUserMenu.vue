<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ChevronsUpDown } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { Avatar } from '@/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/ui/dropdown-menu';
import type { AppShellUser } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  user: AppShellUser;
  compact?: boolean;
  menuLabel?: string;
  side?: 'top' | 'right' | 'bottom' | 'left';
  align?: 'start' | 'center' | 'end';
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  compact: false,
  menuLabel: () => useFuseLocale().appShellUserMenu.menuLabel,
  side: 'bottom',
  align: 'end',
});
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger
      :aria-label="menuLabel"
      :class="
        cn(
          'flex min-w-0 cursor-pointer items-center gap-3 rounded-lg text-left transition-colors hover:bg-secondary/60 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
          compact ? 'rounded-full' : 'w-full p-2',
          props.class,
        )
      "
    >
      <Avatar :src="user.avatar" :initials="user.initials" :seed="user.name" size="sm" />
      <template v-if="!compact">
        <span class="flex min-w-0 flex-1 flex-col group-data-[collapsed=true]/sidebar:hidden">
          <span class="truncate text-action-md text-foreground">
            {{ user.name }}
          </span>
          <span v-if="user.email" class="truncate text-caption text-muted-foreground">
            {{ user.email }}
          </span>
        </span>
        <ChevronsUpDown
          class="size-4 shrink-0 text-muted-foreground group-data-[collapsed=true]/sidebar:hidden"
        />
      </template>
    </DropdownMenuTrigger>
    <DropdownMenuContent :side="side" :align="align" class="min-w-56">
      <DropdownMenuLabel class="flex flex-col">
        <span class="truncate text-foreground">{{ user.name }}</span>
        <span v-if="user.email" class="truncate text-caption font-normal text-muted-foreground">
          {{ user.email }}
        </span>
      </DropdownMenuLabel>
      <template v-if="$slots.default">
        <DropdownMenuSeparator />
        <slot />
      </template>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
