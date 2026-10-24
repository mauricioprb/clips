<script setup lang="ts">
const route = useRoute();

const sections = [
  { label: 'Dados da bolsa', to: '/settings/profile' },
  { label: 'Feriados', to: '/settings/holidays' },
  { label: 'Conta e senha', to: '/settings/security' },
];
</script>

<template>
  <nav aria-label="Seções das configurações" class="border-b border-border">
    <ul class="-mb-px flex gap-1 overflow-x-auto">
      <li v-for="section in sections" :key="section.to">
        <NuxtLink
          :to="section.to"
          class="block border-b-2 px-3 py-2 text-action-md whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
          :class="
            route.path === section.to
              ? 'border-primary text-foreground'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          "
          :aria-current="route.path === section.to ? 'page' : undefined"
        >
          {{ section.label }}
        </NuxtLink>
      </li>
    </ul>
  </nav>
</template>
