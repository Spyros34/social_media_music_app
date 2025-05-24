<template>
   <div class="flex items-center justify-between p-4 bg-white shadow">
    <!-- LEFT: logo -->
    <InertiaLink href="/" class="flex items-center">
      <img src="/logo.svg" alt="Logo" class="h-8 w-8 mr-2" />

    </InertiaLink>

    <!-- RIGHT: profile slot goes here soon -->
    <div class = 'flex items-center' >
      <!-- we’ll fill this next -->
      <div class='mr-4'>John Doe</div>
      <v-avatar color="green">
         <v-icon icon="mdi-account-circle"></v-icon>
    </v-avatar>
    </div>
  </div>
</template>

<script setup>
import { Link as InertiaLink } from '@inertiajs/inertia-vue3'
</script>