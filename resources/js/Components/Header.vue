<template>
   <div class="w-full flex items-center justify-between p-4 bg-white shadow">
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
      <div class='mr-4'>{{ user?.name ?? 'Guest' }}</div>
       <v-avatar :size="40">
        <v-img :src="user?.avatar" cover />
      </v-avatar>
    </div>
  </div>
</template>

<script setup>
import { usePage, Link as InertiaLink } from '@inertiajs/inertia-vue3'

import { computed } from 'vue'
// grab the shared props directly
const page = usePage()

const props = defineProps({
  user: { type: Object, default: null },
})
// access with props.user or use destructuring:
// const user = computed(() => props.user) // not necessary; template can use user directly if you do:
const { user } = props
</script>