<!-- resources/js/Layouts/ResponsiveLayout.vue -->
<template>
  <div class="min-h-screen flex flex-col bg-gray-100">
    <!-- DESKTOP HEADER (hidden on mobile) -->
    <header class="hidden md:flex w-full flex-none">
      <Header :user="authUser" />
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 mt-6 md:mt-0">
      <slot />
    </main>

    <!-- MOBILE NAVBAR (only on mobile) -->
 <nav class="fixed inset-x-0 bottom-0 bg-white border-t md:hidden">
  <div class="flex justify-evenly items-center py-2">
    <InertiaLink
      href="/"
      class="flex flex-col items-center text-gray-600"
    >
      <v-icon size="20">mdi-home</v-icon>
      <span class="text-xs">Home</span>
    </InertiaLink>

    <InertiaLink
      href="/profile"
      class="flex flex-col items-center text-gray-600"
    >
      <v-icon size="20">mdi-account-circle</v-icon>
      <span class="text-xs">Profile</span>
    </InertiaLink>
  </div>
</nav>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/inertia-vue3'
import Header from '@/Components/Header.vue'

const { props: pageProps } = usePage()
const authUser = pageProps.auth.user
</script>

<style scoped>
/* ensure content isn't hidden behind the mobile nav */
main {
  padding-bottom: 4rem;
}
nav div {
  /* reduces vertical padding if you want them even closer */
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
</style>