<!-- resources/js/Layouts/DefaultLayout.vue -->
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

    <!-- MOBILE NAVBAR -->
    <nav :class="['glass-tabbar md:hidden', showNav ? 'translate-y-0' : 'translate-y-full']" role="navigation" aria-label="Primary">
      <div class="tabbar-inner">
        <Link
          href="/"
          :aria-current="isHomeActive ? 'page' : undefined"
          :class="['tab-item', isHomeActive && 'is-active']"
        >
          <v-icon size="22" class="icon-align">mdi-home</v-icon>
          <span>Home</span>
        </Link>

        <Link
          :href="profileUrl"
          :aria-current="isProfileActive ? 'page' : undefined"
          :class="['tab-item', isProfileActive && 'is-active']"
        >
          <v-avatar size="22" class="icon-align">
            <v-img :src="avatarSrc" />
          </v-avatar>
          <span>Profile</span>
        </Link>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'

const page = usePage()
const authUser = page.props.auth?.user || { id: null, name: '', avatar: '' }

const profileUrl = computed(() => (authUser?.id ? `/u/${authUser.id}` : '/profile'))
const avatarSrc  = computed(() =>
  authUser?.avatar ||
  `https://ui-avatars.com/api/?name=${encodeURIComponent(authUser?.name || 'User')}&background=1DB954&color=ffffff&bold=true`
)

const currentPath     = computed(() => window.location.pathname)
const isHomeActive    = computed(() => currentPath.value === '/')
const isProfileActive = computed(() => currentPath.value.startsWith(profileUrl.value))

/* lighter hide/show */
const showNav = ref(true)
let lastY = 0
function onScroll () {
  const y = window.scrollY
  const down = y > lastY
  if (y < 24) showNav.value = true
  else if (down && y - lastY > 12) showNav.value = false
  else if (!down && lastY - y > 8) showNav.value = true
  lastY = y
}
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
:root { --tabbar-h: 58px; }

/* keep content above the bar */
main { padding-bottom: calc(var(--tabbar-h) + env(safe-area-inset-bottom,0px)); }

/* — clean glass bar — */
.glass-tabbar{
  position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
  height: calc(var(--tabbar-h) + env(safe-area-inset-bottom,0px));
  padding-bottom: env(safe-area-inset-bottom,0px);

  /* simple translucent plate */
  background: rgba(24, 24, 26, 0.58);
  backdrop-filter: blur(14px) saturate(160%);
  -webkit-backdrop-filter: blur(14px) saturate(160%);

  /* crisp top separator + gentle lift */
  border-top: 1px solid rgba(255,255,255,0.18);
  box-shadow: 0 -8px 18px rgba(0,0,0,0.24);

  transition: transform .22s cubic-bezier(.2,.8,.2,1);
  will-change: transform;
}

.tabbar-inner{
  display:flex; align-items:center; justify-content:space-around;
  height: var(--tabbar-h);
  padding: 8px 12px;
}

/* items */
.tab-item{
  flex:1; min-width:0; height:42px;
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
  color: rgba(255,255,255,0.94);
  text-decoration:none;
  text-shadow: 0 1px 2px rgba(0,0,0,0.22);
  -webkit-tap-highlight-color: transparent;
  user-select:none;
  outline:none;
}
.tab-item span{ font-size:11px; line-height:1; }

/* subtle baseline tweak for icons/avatars */
.icon-align{ transform: translateY(1px); }

/* active: just color (no dots/lines/pills) */
.tab-item.is-active{
  color:#1DB954;
}

/* hover on devices that support it */
@media (hover:hover){
  .tab-item{ border-radius:10px; }
  .tab-item:hover{ background: rgba(255,255,255,0.08); }
}

/* accessibility fallback */
@media (prefers-reduced-transparency: reduce){
  .glass-tabbar{
    background:#3b3b3fa8; backdrop-filter:none; -webkit-backdrop-filter:none;
  }
}
</style>