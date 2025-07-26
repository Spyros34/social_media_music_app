<template>
  <v-card :loading="loading" :elevation="2" rounded="lg" class="post-card">
    <v-list-item class="mt-3">
      <template #prepend>
        <v-avatar size="40">
          <v-img v-if="post.user?.avatar" :src="post.user.avatar" />
          <v-icon v-else icon="mdi-account-circle" color="grey-darken-1" size="40" />
        </v-avatar>
      </template>
      <v-list-item-title class="font-medium">{{ post.user?.name }}</v-list-item-title>
      <v-list-item-subtitle>{{ timeAgo }}</v-list-item-subtitle>
    </v-list-item>

    <div class="px-8 py-2">
       <div v-if="coverSrc" class="album-wrap">
    <v-img
      :src="coverSrc"
      class="album-art cursor-pointer"
      cover
      aspect-ratio="1"
      @click="onCoverClick"
      :aria-label="t.previewUrl ? (isPlaying ? 'Pause preview' : 'Play preview') : 'Open on Spotify'"
      role="button"
      tabindex="0"
      @keydown.enter.prevent="onCoverClick"
      @keydown.space.prevent="onCoverClick"
    />
  </div>

      <div class="mt-4 mb-3 text-center">
        <div class="text-subtitle-1 font-semibold leading-tight truncate">
          {{ titleText }}
        </div>
        <div class="text-body-2 text-grey-darken-1 truncate">
          {{ artistText }}
        </div>
      </div>

    
      <v-progress-linear
        v-if="t.previewUrl"
        :model-value="previewProgress"
        height="4"
        color="#1DB954"
        rounded
        class="mt-2"
      />
    </div>

    <v-divider />
    <div class="px-2 py-1 d-flex align-center">
      <v-btn
        variant="text"
        :color="post.stats?.liked ? '#1DB954' : undefined"
        @click="$emit('toggle-like', post.id)"
      >
        <v-icon :icon="post.stats?.liked ? 'mdi-heart' : 'mdi-heart-outline'" class="mr-1" />
        {{ post.stats?.likes ?? 0 }}
      </v-btn>

      <v-btn variant="text" @click="$emit('focus-comments', post.id)">
        <v-icon icon="mdi-message-outline" class="mr-1" />
        {{ post.stats?.comments ?? 0 }}
      </v-btn>

      <v-btn variant="text" @click="$emit('repost', post.id)">
        <v-icon icon="mdi-repeat-variant" class="mr-1" />
        {{ post.stats?.reposts ?? 0 }}
      </v-btn>

      <v-spacer />

      <v-btn
        icon
        variant="text"
        :color="post.stats?.saved ? '#1DB954' : undefined"
        @click="$emit('toggle-save', post.id)"
      >
        <v-icon :icon="post.stats?.saved ? 'mdi-bookmark' : 'mdi-bookmark-outline'" />
      </v-btn>
    </div>
  </v-card>
</template>

<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
  post: { type: Object, required: true },
  loading: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})

defineEmits([
  'toggle-like',
  'toggle-save',
  'repost',
  'focus-comments',
  'delete',
  'mute-user',
  'report',
])

const t = computed(() => props.post?.track ?? {})

const coverSrc = computed(() => {
  // prefer normalized coverUrl
  if (t.value.coverUrl) return t.value.coverUrl
  // fallback to typical Spotify shape
  return t.value.album?.images?.[0]?.url
      || t.value.album?.images?.[1]?.url
      || t.value.album?.images?.[2]?.url
      || null // <- no placeholder
})

const titleText = computed(() => t.value.title || t.value.name || 'Unknown Title')
const artistText = computed(() => t.value.artist || t.value.artists?.[0]?.name || 'Unknown Artist')
const externalUrl = computed(() => t.value.externalUrl || t.value.external_urls?.spotify || null)

const timeAgo = computed(() => formatTimeAgo(props.post?.createdAt))

const audio = ref(null)
const isPlaying = ref(false)
const previewProgress = ref(0)
const showPreviewControls = computed(() => !!t.value.previewUrl || !!externalUrl.value)

function togglePlay() {
  const url = t.value.previewUrl
  if (!url) return
  if (!audio.value) {
    audio.value = new Audio(url)
    audio.value.addEventListener('timeupdate', () => {
      if (!audio.value?.duration) return
      previewProgress.value = (audio.value.currentTime / audio.value.duration) * 100
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
    audio.value.play().then(() => (isPlaying.value = true)).catch(() => (isPlaying.value = false))
  }
}

function onCoverClick() {
  if (t.value.previewUrl) {
    togglePlay();
  } else if (externalUrl.value) {
    window.open(externalUrl.value, '_blank', 'noopener');
  }
}

onBeforeUnmount(() => {
  if (audio.value) {
    audio.value.pause()
    audio.value.src = ''
    audio.value = null
  }
})

function formatTimeAgo(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  const diff = (Date.now() - d.getTime()) / 1000
  if (diff < 60) return 'just now'
  const mins = Math.floor(diff / 60)
  if (mins < 60) return `${mins}m`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h`
  const days = Math.floor(hrs / 24)
  return `${days}d`
}
</script>

<style scoped>
.post-card { overflow: hidden; }

.album-wrap {
  max-width: 360px;
  margin: 0 auto;
  padding: 16px;
  border-radius: 20px;
  
}

.album-art {
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.18);
}

.gap-2 { gap: .5rem; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>