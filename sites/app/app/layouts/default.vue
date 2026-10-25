<script setup lang="ts">
import { NuxtLink } from '#components';
import { CalendarDays, CalendarRange, ListChecks, LogOut, Settings, Users } from '@lucide/vue';
import AppShellStacked from '~/ui/app-shell/AppShellStacked.vue';
import { DropdownMenuItem } from '~/ui/dropdown-menu';
import { NavbarMobileLink } from '~/ui/navbar';
import { toast } from '~/ui/toast';

const route = useRoute();
const { user, access, logout } = useAuth();
const signingOut = ref(false);
const menuOpen = ref(false);

watch(
  () => route.path,
  () => (menuOpen.value = false),
);

const navigation = computed(() =>
  [
    { label: 'Mês', href: '/', icon: CalendarDays, section: '/month' },
    { label: 'Grade semanal', href: '/schedule', icon: CalendarRange, section: '/schedule' },
    { label: 'Atividades', href: '/activities', icon: ListChecks, section: '/activities' },
    { label: 'Configurações', href: '/settings/profile', icon: Settings, section: '/settings' },
    ...(access.value.isAdmin
      ? [{ label: 'Usuários', href: '/users', icon: Users, section: '/users' }]
      : []),
  ].map(({ section, ...item }) => ({ ...item, active: route.path.startsWith(section) })),
);

async function signOut() {
  if (signingOut.value) return;
  signingOut.value = true;
  try {
    await logout();
    await navigateTo('/login');
  } catch (exception) {
    toast.danger(errorMessage(exception));
  } finally {
    signingOut.value = false;
  }
}
</script>

<template>
  <AppShellStacked
    v-model:open="menuOpen"
    :navigation="navigation"
    :link-as="NuxtLink"
    :user="user ? { name: user.name || user.email, email: user.email } : undefined"
    user-menu-label="Abrir menu da conta"
    menu-label="Abrir navegação"
    navigation-label="Navegação principal"
    content-class="mx-auto w-full max-w-6xl gap-6 py-6 sm:py-8"
  >
    <template #logo><AppBrand /></template>
    <template #header-actions><ThemeToggle /></template>
    <template #user-menu>
      <DropdownMenuItem @select="navigateTo('/settings/security')">
        <Settings class="size-4" aria-hidden="true" /> Conta e senha
      </DropdownMenuItem>
      <DropdownMenuItem :disabled="signingOut" @select="signOut">
        <LogOut class="size-4" aria-hidden="true" /> Sair
      </DropdownMenuItem>
    </template>
    <template #mobile-user-menu>
      <NavbarMobileLink :as="NuxtLink" href="/settings/security">
        <Settings aria-hidden="true" /> <span>Conta e senha</span>
      </NavbarMobileLink>
      <NavbarMobileLink as="button" type="button" :disabled="signingOut" @click="signOut">
        <LogOut aria-hidden="true" /> <span>Sair</span>
      </NavbarMobileLink>
    </template>
    <slot />
  </AppShellStacked>
</template>
