<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Avatar } from '@/ui/avatar';
import { Container } from '@/ui/container';
import {
  Navbar,
  NavbarActions,
  NavbarBrand,
  NavbarLink,
  NavbarMobileLink,
  NavbarMobileMenu,
  NavbarNav,
  NavbarTrigger,
} from '@/ui/navbar';
import AppShellUserMenu from './AppShellUserMenu.vue';
import type { AppShellNavItem, AppShellUser } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  navigation: AppShellNavItem[];
  user?: AppShellUser;
  linkAs?: string | Component;
  variant?: 'default' | 'tabs';
  headerVariant?: 'plain' | 'banner';
  navigationLabel?: string;
  menuLabel?: string;
  userMenuLabel?: string;
  contentClass?: HTMLAttributes['class'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  linkAs: 'a',
  variant: 'default',
  headerVariant: 'plain',
  navigationLabel: () => useFuseLocale().appShellStacked.navigationLabel,
  menuLabel: () => useFuseLocale().appShellStacked.menuLabel,
  userMenuLabel: () => useFuseLocale().appShellStacked.userMenuLabel,
});

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
  <div
    :class="
      cn(
        'flex min-h-svh flex-col',
        headerVariant === 'banner'
          ? 'bg-[color-mix(in_oklab,var(--color-muted)_60%,var(--color-background))]'
          : 'bg-background',
        props.class,
      )
    "
  >
    <Navbar v-model:open="open" contained :class="variant === 'tabs' && 'desktop:border-b-0'">
      <NavbarTrigger v-if="navigation.length" :label="menuLabel" />
      <NavbarBrand v-if="$slots.logo"><slot name="logo" /></NavbarBrand>
      <NavbarNav v-if="variant === 'default' && navigation.length" :aria-label="navigationLabel">
        <NavbarLink
          v-for="item in navigation"
          :key="item.href"
          :as="linkAs"
          :href="item.href"
          :active="item.active"
          :aria-current="item.active ? 'page' : undefined"
        >
          <component :is="item.icon" v-if="item.icon" class="size-4" />
          {{ item.label }}
        </NavbarLink>
      </NavbarNav>
      <div
        v-if="$slots.search"
        :class="
          cn('flex min-w-0 flex-1 justify-end', variant === 'tabs' && 'tablet:justify-center')
        "
      >
        <slot name="search" />
      </div>
      <NavbarActions class="gap-4 desktop:gap-6">
        <AppShellUserMenu
          v-if="user"
          :user="user"
          :menu-label="userMenuLabel"
          compact
          :class="navigation.length ? 'hidden desktop:flex' : 'flex'"
        >
          <slot name="user-menu" />
        </AppShellUserMenu>
        <slot name="header-actions" />
      </NavbarActions>
      <template #menu>
        <NavbarMobileMenu>
          <NavbarMobileLink
            v-for="item in navigation"
            :key="item.href"
            :as="linkAs"
            :href="item.href"
            :active="item.active"
            :aria-current="item.active ? 'page' : undefined"
          >
            <component :is="item.icon" v-if="item.icon" />
            <span>{{ item.label }}</span>
          </NavbarMobileLink>
          <template v-if="user">
            <div class="my-2 border-t border-border" />
            <div class="flex items-center gap-3 px-3 py-2">
              <Avatar :src="user.avatar" :initials="user.initials" :seed="user.name" size="sm" />
              <div class="flex min-w-0 flex-col">
                <span class="truncate text-action-md font-medium">
                  {{ user.name }}
                </span>
                <span v-if="user.email" class="truncate text-caption text-muted-foreground">
                  {{ user.email }}
                </span>
              </div>
            </div>
            <slot name="mobile-user-menu" />
          </template>
        </NavbarMobileMenu>
      </template>
    </Navbar>
    <nav
      v-if="variant === 'tabs'"
      :aria-label="navigationLabel"
      class="hidden h-12 border-b border-border bg-background desktop:block"
    >
      <div class="mx-auto flex h-full max-w-[1280px] items-stretch gap-1 px-1 tablet:px-5">
        <NavbarLink
          v-for="item in navigation"
          :key="item.href"
          :as="linkAs"
          :href="item.href"
          :active="item.active"
          class="rounded-none py-0 after:absolute after:inset-x-3 after:bottom-0 after:h-0.5 after:rounded-full after:transition-colors hover:after:bg-border focus-visible:ring-inset focus-visible:ring-offset-0 data-[active=true]:text-foreground data-[active=true]:after:bg-primary"
        >
          <component :is="item.icon" v-if="item.icon" class="size-4" />
          {{ item.label }}
        </NavbarLink>
        <div v-if="$slots['nav-actions']" class="ml-auto flex items-center gap-2 pr-3">
          <slot name="nav-actions" />
        </div>
      </div>
    </nav>
    <div
      v-if="headerVariant === 'banner'"
      class="bg-primary pb-28 text-foreground [--border:color-mix(in_oklab,var(--primary-foreground)_24%,transparent)] [--foreground:var(--primary-foreground)] [--muted-foreground:color-mix(in_oklab,var(--primary-foreground)_72%,transparent)]"
    >
      <slot name="header" />
    </div>
    <slot v-else name="header" />
    <main class="flex-1">
      <Container
        :class="
          cn(
            'flex flex-col gap-8 py-8',
            headerVariant === 'banner' && 'relative -mt-24',
            contentClass,
          )
        "
      >
        <slot />
      </Container>
    </main>
  </div>
</template>
