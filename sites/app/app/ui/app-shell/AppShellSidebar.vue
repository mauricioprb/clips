<script setup lang="ts">
import { computed, type Component, type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Badge } from '@/ui/badge';
import { Container } from '@/ui/container';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarTrigger,
} from '@/ui/sidebar';
import AppShellUserMenu from './AppShellUserMenu.vue';
import type { AppShellNavGroup, AppShellUser } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  navigation: AppShellNavGroup[];
  user?: AppShellUser;
  linkAs?: string | Component;
  variant?: 'default' | 'floating' | 'inset';
  size?: 'md' | 'sm';
  navigationLabel?: string;
  userMenuLabel?: string;
  fluid?: boolean;
  contentClass?: HTMLAttributes['class'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  linkAs: 'a',
  variant: 'default',
  size: 'md',
  navigationLabel: () => useFuseLocale().appShellSidebar.navigationLabel,
  userMenuLabel: () => useFuseLocale().appShellSidebar.userMenuLabel,
});

const collapsed = defineModel<boolean>('collapsed', { default: false });

const inset = computed(() => props.variant === 'inset');
const onCanvas = computed(() => props.variant !== 'default');
</script>

<template>
  <SidebarProvider
    v-model:collapsed="collapsed"
    :data-app-shell-canvas="onCanvas || undefined"
    :class="
      cn(
        '[--app-canvas:color-mix(in_oklab,var(--color-muted)_60%,var(--color-background))]',
        onCanvas ? 'bg-(--app-canvas)' : 'bg-background',
        props.class,
      )
    "
  >
    <Sidebar
      :variant="variant === 'inset' ? 'default' : variant"
      :mobile-label="navigationLabel"
      :class="
        cn('sticky top-0', variant === 'floating' && 'top-2', inset && 'border-r-0 bg-transparent')
      "
    >
      <SidebarHeader v-if="$slots.logo" class="group-data-[variant=floating]/sidebar:pt-5">
        <slot name="logo" />
      </SidebarHeader>
      <SidebarContent>
        <nav :aria-label="navigationLabel" class="contents">
          <SidebarGroup v-for="(group, index) in navigation" :key="index">
            <SidebarGroupLabel v-if="group.label">
              {{ group.label }}
            </SidebarGroupLabel>
            <SidebarMenu>
              <SidebarMenuItem v-for="item in group.items" :key="item.href">
                <SidebarMenuButton
                  :size="size"
                  :as="linkAs"
                  :href="item.href"
                  :active="item.active"
                  :tooltip="item.label"
                  :aria-current="item.active ? 'page' : undefined"
                  :class="inset && 'hover:bg-background/70 data-[active=true]:bg-background'"
                >
                  <component :is="item.icon" v-if="item.icon" />
                  <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <span class="truncate">{{ item.label }}</span>
                    <Badge v-if="item.badge" variant="soft" intent="neutral" size="sm">
                      {{ item.badge }}
                    </Badge>
                  </span>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroup>
        </nav>
      </SidebarContent>
      <SidebarFooter v-if="$slots['sidebar-footer'] || user">
        <slot name="sidebar-footer" />
        <AppShellUserMenu
          v-if="user"
          :user="user"
          :menu-label="userMenuLabel"
          side="top"
          align="start"
          class="group-data-[collapsed=true]/sidebar:justify-center"
        >
          <slot name="user-menu" />
        </AppShellUserMenu>
      </SidebarFooter>
    </Sidebar>

    <div :class="cn('flex min-w-0 flex-1 flex-col')">
      <div
        :class="
          cn(
            'flex flex-1 flex-col',
            inset &&
              'mx-2 min-h-[calc(100svh-0.5rem)] rounded-b-xl border-x border-b border-border bg-background desktop:ml-0',
            inset && !$slots.footer && 'mb-2',
          )
        "
      >
        <div
          v-if="inset"
          aria-hidden="true"
          class="pointer-events-none sticky top-0 z-40 -mx-0.5 -mb-5 h-5 overflow-hidden"
        >
          <div
            class="absolute inset-x-px top-2 h-8 rounded-t-xl border border-border shadow-[0_0_0_100vmax_var(--app-canvas)]"
          />
        </div>
        <header
          :class="
            cn(
              'sticky top-0 z-30 h-14 shrink-0 border-b border-border bg-background',
              variant === 'floating' && 'border-b-0 bg-(--app-canvas)',
              inset && 'top-2 mt-2',
            )
          "
        >
          <div class="flex h-full items-center gap-3 px-4 tablet:px-8">
            <SidebarTrigger class="-ml-1" />
            <div
              class="flex min-w-0 flex-1 items-center [&_li:last-child]:min-w-0 [&_li:last-child]:truncate [&_ol]:flex-nowrap max-tablet:[&_li:not(:last-child)]:hidden"
            >
              <slot name="breadcrumb" />
            </div>
            <div v-if="$slots.search" class="flex flex-1 justify-end">
              <slot name="search" />
            </div>
            <div class="flex shrink-0 items-center gap-2 empty:hidden">
              <slot name="header-actions" />
            </div>
          </div>
        </header>
        <main class="flex-1">
          <Container :class="cn('flex flex-col gap-8 py-8', fluid && 'max-w-none', contentClass)">
            <slot />
          </Container>
        </main>
      </div>
      <footer
        v-if="$slots.footer"
        :class="
          cn(
            'text-body-sm text-muted-foreground',
            !inset && 'border-t border-border',
            inset && 'mx-2 desktop:ml-0',
          )
        "
      >
        <Container
          :class="
            cn(
              'flex max-w-none flex-col gap-3 py-5 tablet:flex-row tablet:items-center tablet:justify-between',
              contentClass,
            )
          "
        >
          <slot name="footer" />
        </Container>
      </footer>
    </div>
  </SidebarProvider>
</template>

<style>
html:has([data-app-shell-canvas]) {
  background-color: color-mix(in oklab, var(--color-muted) 60%, var(--color-background));
}
</style>
