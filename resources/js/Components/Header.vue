<template>
   <div class="flex items-center justify-between p-2 bg-white shadow">
    <!-- LEFT: logo -->
     <InertiaLink href="/" class="flex items-center">
      <!-- 1. Fixed-size wrapper -->
      <div class="mr-2 h-16 w-16 flex-shrink-0">
        <!-- 2. Fill the wrapper -->
        <img
          src="/logo.svg"
          alt="Logo"
          class="h-full w-full object-contain"
        />
      </div>
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