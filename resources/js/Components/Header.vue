<template>
  <!-- same structure, new class names -->
  <div class="glass-header w-full flex items-center justify-between">
    <!-- LEFT: logo -->
    <InertiaLink href="/" class="flex items-center">
      <div class="mr-2 h-16 w-16 flex-shrink-0">
        <img src="/logo.svg" alt="Logo" class="h-full w-full object-contain" />
      </div>
 
    </InertiaLink>

    <!-- RIGHT: user -->
    <div class="flex items-center">
      <div class="mr-4 username">{{ user?.name ?? 'Guest' }}</div>
      <v-avatar :size="40">
        <v-img :src="user?.avatar" cover />
      </v-avatar>
    </div>
  </div>
</template>

<script setup>
import { usePage, Link as InertiaLink } from '@inertiajs/inertia-vue3'
defineProps({ user: { type: Object, default: null } })
usePage() // (kept; even if unused)
</script>

<style scoped>
/* Full-width sticky glass bar */
.glass-header{
  position: sticky; top: 0; z-index: 50;
  height: 64px;
  padding: 0 16px;
  /* glass surface */
  background: linear-gradient(180deg, rgba(24,24,26,.38), rgba(24,24,26,.22));
  backdrop-filter: blur(26px) saturate(140%);
  -webkit-backdrop-filter: blur(26px) saturate(140%);
  border-bottom: 1px solid rgba(255,255,255,0.14);
  box-shadow: 0 8px 28px -16px rgba(0,0,0,0.5);
}

/* text colors to match your cards */
.brand-name{ color:#fff; font-weight:800; letter-spacing:.2px; margin-left:4px; }
.username{ color:#fff; opacity:.9; }

/* small polish */
@media (hover:hover){
  .glass-header:hover{
    background: linear-gradient(180deg, rgba(24,24,26,.44), rgba(24,24,26,.26));
  }
}
@media (max-width:480px){
  .glass-header{ height:58px }
  .brand-name{ display:none; } /* tighter on phones */
}
</style>