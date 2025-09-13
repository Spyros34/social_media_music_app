<template>
  <!-- wrapper keeps overflow / rounded corners -->
  <div class="glass-card">
    <!-- avatar / user + timestamp -->
    <v-list-item class="pt-4 pb-5">
      <template #prepend>
        <v-avatar size="40">
          <v-img v-if="post.user?.avatar" :src="post.user.avatar" />
          <v-icon v-else icon="mdi-account-circle" color="grey-darken-1" size="40" />
        </v-avatar>
      </template>

      <v-list-item-title class="font-medium">
        {{ post.user?.name }}
      </v-list-item-title>
      <v-list-item-subtitle>{{ timeAgo }}</v-list-item-subtitle>

     <template #append>
     <v-menu
  v-if="isOwner"
  v-model="menuOpen"
  location="bottom end"
  offset="6"
  :close-on-content-click="true"
  content-class="glass-surface"
>
  <template #activator="{ props }">
    <v-btn v-bind="props" icon variant="text" class="more-btn" aria-label="More actions">
      <v-icon icon="mdi-dots-horizontal" size="22" />
    </v-btn>
  </template>

  <v-list class="action-menu action-menu--sm" rounded="lg" elevation="6" nav>
    <div class="menu-row destructive" @click.stop="confirmDelete = true">
      <v-icon icon="mdi-trash-can-outline" size="18" />
      <span>Delete Post</span>
    </div>
  </v-list>
</v-menu>
    </template>
    </v-list-item>

    <!-- cover + track title / artist -->
    <div class="px-6 pb-4">
      <div v-if="coverSrc" class="album-holder">
        <v-img
          :src="coverSrc"
          class="album-img cursor-pointer"
          cover
          aspect-ratio="1"
          @click="onCoverClick"
        />
      </div>

      <div class="mt-3 mb-2 text-center">
        <div class="text-subtitle-1 font-semibold leading-tight truncate">
          {{ titleText }}
        </div>
        <div class="text-body-2 text-grey-darken-1 truncate">
          {{ artistText }}
        </div>
      </div>

      <v-progress-linear
        v-if="track.previewUrl"
        :model-value="previewProgress"
        height="4"
        color="#1DB954"
        rounded
      />
    </div>

    <v-divider class="opacity-30" />

    <!-- actions -->
    <div class="px-2 py-2 d-flex justify-center">
      <v-btn
        variant="text"
        :color="post.stats?.liked ? '#1DB954' : undefined"
        @click="$emit('toggle-like', post.id)"
      >
        <v-icon :icon="post.stats?.liked ? 'mdi-heart' : 'mdi-heart-outline'" class="mr-1" />
        {{ post.stats?.likes ?? 0 }}
      </v-btn>
    </div>

    <!-- CONFIRMATION DIALOG -->
    <v-dialog v-model="confirmDelete" max-width="420">
      <v-card>
        <v-card-title class="text-h6">Delete post?</v-card-title>
        <v-card-text>This action cannot be undone.</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="confirmDelete = false">Cancel</v-btn>
          <v-btn color="error" :loading="deleting" @click="onDeleteConfirmed">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router } from '@inertiajs/vue3'   // need router to auto-close on nav

/* ------------------------------------------------------------------
   Props and emitted events
-------------------------------------------------------------------*/
const props = defineProps({
  post   : { type: Object, required: true },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits([
  'toggle-like',
  'toggle-save',
  'repost',
  'focus-comments',
  'delete-post'
])

/* ------------------------------------------------------------------
   Auth → owner check
-------------------------------------------------------------------*/
const page = usePage()
const authUser = computed(() => page.props.auth?.user || null)
const isOwner  = computed(() => {
  const uid = authUser.value?.id
  const pid = props.post.user_id ?? props.post.user?.id
  return uid && pid && uid === pid
})

/* ------------------------------------------------------------------
   v-menu control (close on scroll / nav / resize)
-------------------------------------------------------------------*/
const menuOpen = ref(false)

function closeMenu() {
  if (menuOpen.value) menuOpen.value = false
}

function onScrollLikeEvents() {
  // any page movement should close the menu
  closeMenu()
}
// keep a reference to unsubscribe
let unbindInertiaStart = null
onMounted(() => {
  // Inertia router: subscribe, keep the unbind fn (if returned)
  if (typeof router.on === 'function') {
    const maybeUnbind = router.on('start', closeMenu)
    if (typeof maybeUnbind === 'function') unbindInertiaStart = maybeUnbind
  }

  // also close on scroll/resize/touch
  window.addEventListener('scroll', closeMenu, { passive: true })
  window.addEventListener('touchmove', closeMenu, { passive: true })
  window.addEventListener('wheel', closeMenu, { passive: true })
  window.addEventListener('resize', closeMenu, { passive: true })

  // fallback for older adapters: DOM event
  document.addEventListener('inertia:start', closeMenu)
})
onBeforeUnmount(() => {
  // unsubscribe if we got a function back
  unbindInertiaStart?.()

  window.removeEventListener('scroll', closeMenu)
  window.removeEventListener('touchmove', closeMenu)
  window.removeEventListener('wheel', closeMenu)
  window.removeEventListener('resize', closeMenu)

  document.removeEventListener('inertia:start', closeMenu)
})

/* ------------------------------------------------------------------
   Delete dialog state + action
-------------------------------------------------------------------*/
const confirmDelete = ref(false)
const deleting = ref(false)

function onDeleteConfirmed () {
  deleting.value = true
  emit('delete-post', props.post.id)
  confirmDelete.value = false
  deleting.value = false
}

function openDeleteDialog() {
  // Called from the menu item
  menuOpen.value = false
  confirmDelete.value = true
}

/* ------------------------------------------------------------------
   Quick access helpers (track, texts, time)
-------------------------------------------------------------------*/
const track = computed(() => props.post.track ?? {})

const coverSrc = computed(() =>
  track.value.coverUrl
  || track.value.album?.images?.[0]?.url
  || null
)

const titleText  = computed(() =>
  track.value.title
  || track.value.name
  || 'Unknown Title'
)

const artistText = computed(() =>
  track.value.artist
  || track.value.artists?.[0]?.name
  || 'Unknown Artist'
)

function formatTimeAgo (iso) {
  if (!iso) return ''
  const secs = (Date.now() - new Date(iso)) / 1000
  if (secs < 60) return 'just now'
  const mins = Math.floor(secs / 60) ; if (mins < 60) return `${mins}m`
  const hrs  = Math.floor(mins / 60) ; if (hrs  < 24) return `${hrs}h`
  return `${Math.floor(hrs / 24)}d`
}
// Support both created_at (snake) and createdAt (camel)
const timeAgo = computed(() => formatTimeAgo(props.post.created_at ?? props.post.createdAt))

/* ------------------------------------------------------------------
   Preview-audio player
-------------------------------------------------------------------*/
const audio           = ref(null)
const isPlaying       = ref(false)
const previewProgress = ref(0)

function togglePreview () {
  const url = track.value.previewUrl
  if (!url) return

  if (!audio.value) {
    audio.value = new Audio(url)

    audio.value.addEventListener('timeupdate', () => {
      if (!audio.value.duration) return
      previewProgress.value =
        (audio.value.currentTime / audio.value.duration) * 100
    })

    audio.value.addEventListener('ended', () => {
      isPlaying.value = false
      previewProgress.value = 0
    })
  }

  if (isPlaying.value) {
    audio.value.pause()
    isPlaying.value = false
  } else {
    audio.value.currentTime = 0
    audio.value
      .play()
      .then(() => (isPlaying.value = true))
      .catch(() => (isPlaying.value = false))
  }
}

/* ------------------------------------------------------------------
   Click on cover: preview OR open on Spotify
-------------------------------------------------------------------*/
const externalUrl = computed(() =>
  track.value.externalUrl
  || track.value.external_urls?.spotify
  || null
)

function onCoverClick () {
  if (track.value.previewUrl) {
    togglePreview()
  } else if (externalUrl.value) {
    window.open(externalUrl.value, '_blank', 'noopener')
  }
}

/* ------------------------------------------------------------------
   Clean-up
-------------------------------------------------------------------*/
onBeforeUnmount(() => {
  if (audio.value) {
    audio.value.pause()
    audio.value = null
  }
})
</script>

<style scoped>
/* ——— glassmorphism card ——— */
.glass-card{
  border-radius: 18px;
  overflow: hidden;
  backdrop-filter: blur(80px) saturate(70%);
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.25);
  box-shadow: 0 12px 28px -8px rgba(0,0,0,0.25);
  transition: background 0.4s ease, box-shadow 0.4s ease;
}
.glass-card *{
  color:#fff !important;
  text-shadow:0 1px 3px rgba(0,0,0,.35);
}
.glass-card:hover{
  background: rgba(255,255,255,0.22);
  box-shadow: 0 18px 38px -12px rgba(0,0,0,0.30);
}

.album-holder{ max-width: 360px; margin:0 auto; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.35);}
.album-img   { border-radius: 14px; }

/* CIRCULAR BUTTON + RIGHT MARGIN */
.delete-btn {
  width: 40px;                 /* circle size */
  height: 40px;
  min-width: 40px;             /* Vuetify sometimes enforces min-width */
  border-radius: 9999px !important;
  margin-right: 10px;
  padding: 0;                  /* keep icon centered */
  display: inline-flex;
  align-items: center;
  justify-content: center;

  /* subtle red backplate */
  background-color: rgba(245, 245, 245, 0.43) !important;
}

/* keep the icon red, override your glass-card global white */
.delete-btn :deep(.v-icon) {
  color: white !important;
}

/* hover polish */
.delete-btn:hover {
  background-color: white !important;
}



.gap-2    { gap:.5rem }
.truncate { overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
</style>

<style>
/* FORCE YOUR GLASS BACKGROUND ON THE MENU SURFACE */
.v-overlay__content.glass-surface {
  background: rgba(86, 86, 86, 0.415) !important;   /* YOUR BG */
  backdrop-filter: blur(60px) saturate(80%);
  -webkit-backdrop-filter: blur(60px) saturate(80%);
  border: 1px solid rgba(255,255,255,0.18);
  box-shadow: 0 14px 32px -10px rgba(0,0,0,0.45);
  border-radius: 12px;
  overflow: hidden;
}

/* NUKE INNER WHITE SURFACES */
.v-overlay__content.glass-surface .v-sheet,
.v-overlay__content.glass-surface .v-card,
.v-overlay__content.glass-surface .v-list {
  background: transparent !important;
  box-shadow: none !important;
}

/* FORCE WHITE TYPOGRAPHY INSIDE THE MENU */
.v-overlay__content.glass-surface,
.v-overlay__content.glass-surface .v-list,
.v-overlay__content.glass-surface .v-list * {
  color: #fff !important;                      /* YOUR TEXT COLOR */
}

/* YOUR CUSTOM ROW — KEEP WHITE + CENTERED */
.v-overlay__content.glass-surface .menu-row{
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 8px;
  min-height: 32px;
  line-height: 18px;                           /* lock with icon */
  color: #fff !important;                      /* belt-and-suspenders */
}

.v-overlay__content.glass-surface .menu-row .v-icon{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px; height: 18px;
  font-size: 18px; line-height: 18px;
  color: #fff !important;                      /* keep icon white */
}

.v-overlay__content.glass-surface .menu-row span{
  display: inline-flex;
  align-items: center;
  line-height: 18px;
}

/* HOVER (UNCHANGED LOOK) */
.v-overlay__content.glass-surface .menu-row.destructive:hover {
  background: rgba(255,255,255,0.08) !important;
  border-radius: 8px;
}
.v-overlay__content.glass-surface .menu-row {
  cursor: pointer;               /* show hand */
}
.v-overlay__content.glass-surface .menu-row:hover {
  cursor: pointer;               /* ensure on hover, too */
}
</style>