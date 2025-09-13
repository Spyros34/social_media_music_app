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
        'fixed inset-x-0 bottom-0 bg-white border-t md:hidden transition-transform transform duration-300 ease-in-out z-50',
        showNav ? 'translate-y-0' : 'translate-y-full'
      ]"
    >
      <div class="flex justify-evenly items-center py-2">
        <Link
          href="/"
          :class="[
            'flex flex-col items-center',
            isActive('/') ? 'text-[#1DB954]' : 'text-gray-600'
          ]"
        >
          <v-icon size="20">mdi-home</v-icon>
          <span class="text-xs">Home</span>
        </Link>

        <!-- Self profile link -->
        <Link
          :href="profileUrl"
          :class="[
            'flex flex-col items-center',
            isActive(profileUrl) ? 'text-[#1DB954]' : 'text-gray-600'
          ]"
        >
          <v-avatar size="24">
            <v-img :src="avatarSrc" />
          </v-avatar>
          <span class="text-xs">Profile</span>
        </Link>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'

const page = usePage()
const pageProps = page.props
const authUser = pageProps.auth?.user || { id: null, name: '', avatar: '' }

/* Build the correct profile URL: /u/:id (fallback to /profile if no user) */
const profileUrl = computed(() => {
  const u = pageProps?.auth?.user
  return u?.id ? `/u/${u.id}` : '/profile'
})

/* Avatar fallback */
const avatarSrc = computed(() => {
  const u = pageProps?.auth?.user
  if (u?.avatar) return u.avatar
  const name = u?.name || 'User'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1DB954&color=ffffff&bold=true`
})

/* active‐link highlighting */
const currentPath = computed(() => window.location.pathname)
function isActive(path) {
  const p = typeof path === 'string' ? path : String(path)
  return currentPath.value === p || currentPath.value.startsWith(p)
}

/* show/hide mobile nav on scroll */
const showNav = ref(true)
let lastScrollY = 0

function handleScroll() {
  const y = window.scrollY
  showNav.value = !(y > lastScrollY && y > 50)
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