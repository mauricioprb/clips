import type { Component } from 'vue';

export { default as AppShellSearch } from './AppShellSearch.vue';
export { default as AppShellSidebar } from './AppShellSidebar.vue';
export { default as AppShellStacked } from './AppShellStacked.vue';
export { default as AppShellUserMenu } from './AppShellUserMenu.vue';

export interface AppShellNavItem {
  label: string;
  href: string;
  icon?: Component;
  active?: boolean;
  badge?: string;
}

export interface AppShellNavGroup {
  label?: string;
  items: AppShellNavItem[];
}

export interface AppShellUser {
  name: string;
  email?: string;
  avatar?: string;
  initials?: string;
}
