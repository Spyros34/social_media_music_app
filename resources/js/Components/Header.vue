<template>
  <!-- same structure; just better polish -->
  <div :class="['app-header w-full flex items-center justify-between', variant]">
    <!-- LEFT: logo -->
    <InertiaLink href="/" class="flex items-center header-left" aria-label="Home">
      <div class="mr-2 h-12 w-12 md:h-16 md:w-16 flex-shrink-0">
        <img src="/logo.svg" alt="Logo" class="h-full w-full object-contain" />
      </div>
    </InertiaLink>

    <!-- RIGHT: user -->
    <div class="flex items-center header-right">
      <div class="mr-3 username truncate" :title="user?.name || 'Guest'">
        {{ user?.name ?? 'Guest' }}
      </div>

      <!-- avatar links to profile -->
      <InertiaLink :href="profileHref" class="avatar-link" aria-label="Your profile">
        <v-avatar :size="40">
          <v-img :src="user?.avatar" cover />
        </v-avatar>
      </InertiaLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link as InertiaLink } from '@inertiajs/vue3'

const props = defineProps({
  user:    { type: Object, default: null },
  // 'light' for standard pages, 'glass' for the home page
  variant: { type: String, default: 'light' },
})

const profileHref = computed(() => (props.user?.id ? `/u/${props.user.id}` : '/profile'))
</script>

<style scoped>
/* ---------- Base container (shared) ---------- */
.app-header{
  position: sticky; top: 0; z-index: 60;
  height: 64px;
  padding-inline: 16px;                 /* rely on CSS padding; avoids fighting utility classes */
  padding-top: calc(env(safe-area-inset-top, 0px));
}

.header-left,
.header-right { min-height: 48px; }

.username{
  max-width: min(50vw, 220px);          /* never crowd the avatar */
  font-weight: 500;
  letter-spacing: .2px;
  line-height: 1.1;
}

/* focus ring for keyboard users */
.avatar-link{ display:inline-flex; border-radius:9999px; }
.avatar-link:focus-visible{
  outline: 2px solid rgba(29,185,84,.8);
  outline-offset: 2px;
}

/* ---------- Variant: LIGHT (default) ---------- */
.app-header.light{
  background: #ffffff;
  border-bottom: 1px solid rgba(0,0,0,.06);
  box-shadow: 0 2px 10px rgba(0,0,0,.05);
}
.app-header.light .username{ color:#111827; opacity:.9; }

/* ---------- Variant: GLASS (home) ---------- */
/* Rich, legible glass with safe fallbacks. Slightly brighter for better contrast over dark bgs. */
.app-header.glass{
  /* fallback if blur unsupported will still look good */
  background:
    linear-gradient(180deg, rgba(28,28,32,.72), rgba(24,24,28,.62));
  border-bottom: 1px solid rgba(255,255,255,.14);
  box-shadow: 0 10px 24px -18px rgba(0,0,0,.55);
}
.app-header.glass .username{ color:#fff; opacity:.92; }

/* Apply real glass when supported */
@supports ((-webkit-backdrop-filter: none) or (backdrop-filter: none)) {
  .app-header.glass{
    background:
      linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,0) 30%),
      rgba(20,20,22,.66);
    -webkit-backdrop-filter: blur(22px) saturate(140%);
    backdrop-filter: blur(22px) saturate(140%);
  }
}

/* Accessibility: reduce transparency for users who prefer it */
@media (prefers-reduced-transparency: reduce){
  .app-header.glass{
    background:#1b1b1f;
    -webkit-backdrop-filter:none;
    backdrop-filter:none;
  }
}

/* Hover sheen on large screens only */
@media (hover:hover){
  .app-header.glass:hover{
    background:
      linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,0) 30%),
      rgba(22,22,24,.72);
  }
}

/* ---------- Small screen tweaks ---------- */
@media (max-width: 480px){
  .app-header{ height:58px }
  .username{ max-width: 44vw; font-weight: 600; }
}
</style>