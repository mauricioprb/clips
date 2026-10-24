<script setup lang="ts">
import { Toaster } from '~/ui/toast';

useHead({ titleTemplate: (title) => (title && title !== 'Clips' ? `${title} | Clips` : 'Clips') });
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <Toaster />
  </div>
</template>
