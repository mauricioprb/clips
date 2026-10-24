<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { useColorMode } from '~/composables/useColorMode';
import { Button } from '~/ui/button';

const { theme, setTheme } = useColorMode();
</script>

<template>
  <div
    role="group"
    aria-label="Aparência"
    class="inline-flex gap-0.5 rounded-lg border border-border bg-background p-0.5"
  >
    <Button
      type="button"
      :variant="theme === 'light' ? 'secondary' : 'ghost'"
      size="sm"
      icon-only
      :icon-leading="Sun"
      aria-label="Tema claro"
      title="Claro"
      :aria-pressed="theme === 'light'"
      @click="setTheme('light')"
    />
    <Button
      type="button"
      :variant="theme === 'dark' ? 'secondary' : 'ghost'"
      size="sm"
      icon-only
      :icon-leading="Moon"
      aria-label="Tema escuro"
      title="Escuro"
      :aria-pressed="theme === 'dark'"
      @click="setTheme('dark')"
    />
    <Button
      type="button"
      :variant="theme === 'system' ? 'secondary' : 'ghost'"
      size="sm"
      icon-only
      :icon-leading="Monitor"
      aria-label="Tema do sistema"
      title="Sistema"
      :aria-pressed="theme === 'system'"
      @click="setTheme('system')"
    />
  </div>
</template>
