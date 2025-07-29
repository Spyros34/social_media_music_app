<!-- resources/js/Layouts/ResponsiveLayout.vue -->
<template>
  <div class="min-h-screen flex flex-col bg-gray-100">
    <!-- DESKTOP HEADER (hidden on mobile) -->
    <header class="hidden md:flex w-full flex-none">
      <Header :user="authUser" />
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- MOBILE NAVBAR (only on mobile) -->
    <nav
      :class="[
        'fixed inset-x-0 bottom-0 bg-white border-t md:hidden transition-transform transform duration-300 ease-in-out',
        showNav ? 'translate-y-0' : 'translate-y-full'
      ]"
    >
      <div class="flex justify-evenly items-center py-2">
        <InertiaLink
          href="/"
          :class="[
            'flex flex-col items-center',
            isActive('/') ? 'text-[#1DB954]' : 'text-gray-600'
          ]"
        >
          <v-icon size="20">mdi-home</v-icon>
          <span class="text-xs">Home</span>
        </InertiaLink>

        <InertiaLink
          href="/profile"
          :class="[
            'flex flex-col items-center',
            isActive('/profile') ? 'text-[#1DB954]' : 'text-gray-600'
          ]"
        >
          <v-avatar size="24">
            <v-img :src="authUser.avatar" />
          </v-avatar>
          <span class="text-xs">Profile</span>
        </InertiaLink>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/inertia-vue3'
import Header from '@/Components/Header.vue'

const { props: pageProps } = usePage()
const authUser = pageProps.auth.user || { avatar: '' }

// for active‐link highlighting
const currentPath = computed(() => pageProps.url || window.location.pathname)
function isActive(path) {
  return currentPath.value === path
}

// show/hide on scroll
const showNav = ref(true)
let lastScrollY = 0

function handleScroll() {
  const y = window.scrollY
  // hide when scrolling down, show when scrolling up
  if (y > lastScrollY && y > 50) {
    showNav.value = false
  } else {
    showNav.value = true
  }
  lastScrollY = y
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
/* ensure content isn’t hidden behind the mobile nav */
main {
  padding-bottom: 4rem;
}
</style>