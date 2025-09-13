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

    <!-- MOBILE NAVBAR (glass, visible only on mobile) -->
    <nav :class="['glass-tabbar md:hidden', showNav ? 'translate-y-0' : 'translate-y-full']">
      <div class="tabbar-inner">
        <Link
          href="/"
          :class="['tab-item', isHomeActive && 'is-active']"
        >
          <v-icon size="22">mdi-home</v-icon>
          <span>Home</span>
        </Link>

        <Link
          :href="profileUrl"
          :class="['tab-item', isProfileActive && 'is-active']"
        >
          <v-avatar size="22">
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

/* shared props */
const page = usePage()
const authUser = page.props.auth?.user || { id: null, name: '', avatar: '' }

/* profile url + avatar fallback */
const profileUrl = computed(() => (authUser?.id ? `/u/${authUser.id}` : '/profile'))
const avatarSrc  = computed(() =>
  authUser?.avatar ||
  `https://ui-avatars.com/api/?name=${encodeURIComponent(authUser?.name || 'User')}&background=1DB954&color=ffffff&bold=true`
)

/* active state — Home matches exactly "/", Profile matches its prefix */
const currentPath     = computed(() => window.location.pathname)
const isHomeActive    = computed(() => currentPath.value === '/')
const isProfileActive = computed(() => currentPath.value.startsWith(profileUrl.value))

/* hide/show the bar on scroll (down = hide, up = show) */
const showNav = ref(true)
let lastY = 0
function onScroll () {
  const y = window.scrollY
  showNav.value = !(y > lastY && y > 50)
  lastY = y
}
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
:root { --tabbar-h: 60px; }

/* keep content above the bar */
main { padding-bottom: calc(var(--tabbar-h) + env(safe-area-inset-bottom,0px)); }

/* — brighter glass tab bar — */
.glass-tabbar{
  position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
  height: calc(var(--tabbar-h,60px) + env(safe-area-inset-bottom,0px));
  padding-bottom: env(safe-area-inset-bottom,0px);

  /* brighter, warmer glass */
  background:
    /* soft top glow for contrast */
    linear-gradient(180deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,.06) 35%, rgba(255,255,255,0) 40%),
    /* subtle radial highlight */
    radial-gradient(50% 80% at 50% -20%, rgba(255,255,255,.16), rgba(255,255,255,0) 65%),
    /* base tint (a touch lighter than before) */
    rgba(10,30,34,.32);
  backdrop-filter: blur(10px) saturate(160%);
  

  /* sharper separator & gentle lift */
  border-top: 1px solid rgba(255,255,255,.28);
  box-shadow:
    inset 0 1px 0 rgba(255,255,255,.18),  /* inner top highlight */
    0 -10px 24px rgba(0,0,0,.28);
  transition: transform .28s ease;
}

.tabbar-inner{
  display:flex; align-items:center; justify-content:space-around;
  height: var(--tabbar-h,60px);
  padding: 6px 10px;
}

.tab-item{
  flex:1; min-width:0; height:44px; position:relative;
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
  color: rgba(255,255,255,.96);        /* a bit brighter */
  text-decoration:none; text-shadow:0 1px 2px rgba(0,0,0,.30);
  -webkit-tap-highlight-color: transparent;
  user-select:none;
  outline:none;
}
.tab-item span{ font-size:11px; line-height:1; }

/* active (keep it clean: green text only) */
.tab-item.is-active{ color:#1DB954; }
.tab-item.is-active::after{ content:none !important; display:none !important; }

/* hover on devices that support it */
@media (hover:hover){
  .tab-item:hover{ background: rgba(255,255,255,.08); border-radius:10px; }
}

/* accessibility fallback */
@media (prefers-reduced-transparency: reduce){
  .glass-tabbar{
    background:#141416; backdrop-filter:none; -webkit-backdrop-filter:none;
  }
}

</style>