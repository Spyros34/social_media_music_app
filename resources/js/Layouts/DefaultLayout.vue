<template>
  <div class="min-h-screen flex flex-col bg-gray-100">
    <!-- HEADER -->
    <Header :user="authUser" />

    <!-- MAIN: grows to fill available space -->
    <main class="flex-1 mt-6">
      <slot />
    </main>

    <!-- FOOTER: always at bottom -->
    <footer class="p-4 mt-10 text-center text-sm text-gray-600 border-t bg-white">
      © {{ new Date().getFullYear() }} VibeWave
    </footer>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'

const { props: pageProps } = usePage()
const authUser = pageProps.auth.user
</script>

<style>
/* ensure the html/body also fill the screen */
html, body, #app {
  height: 100%;
}
</style>