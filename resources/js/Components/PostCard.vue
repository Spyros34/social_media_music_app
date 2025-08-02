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
  </div>
</template>

<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'

const props = defineProps({
  post:    { type: Object, required: true },
  loading: { type: Boolean, default: false },
})

defineEmits(['toggle-like','toggle-save','repost','focus-comments'])

/* ---------- derived helpers ---------- */
const track      = computed(() => props.post.track || {})
const coverSrc   = computed(() => track.value.coverUrl || track.value.album?.images?.[0]?.url || null)
const titleText  = computed(() => track.value.title  || track.value.name || 'Unknown Title')
const artistText = computed(() => track.value.artist || track.value.artists?.[0]?.name || 'Unknown Artist')

function formatTimeAgo(iso){
  if(!iso) return ''
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if(diff < 60) return 'just now'
  const mins = Math.floor(diff/60)
  if(mins < 60) return `${mins}m`
  const hrs  = Math.floor(mins/60)
  if(hrs  < 24) return `${hrs}h`
  return `${Math.floor(hrs/24)}d`
}
const timeAgo = computed(() => formatTimeAgo(props.post.createdAt))

/* preview-audio */
const audio          = ref(null)
const isPlaying      = ref(false)
const previewProgress= ref(0)

function togglePlay(){
  if(!track.value.previewUrl) return
  if(!audio.value){
    audio.value = new Audio(track.value.previewUrl)
    audio.value.addEventListener('timeupdate',()=> {
      if(!audio.value?.duration) return
      previewProgress.value = (audio.value.currentTime / audio.value.duration) * 100
    })
    audio.value.addEventListener('ended',()=>{ isPlaying.value=false; previewProgress.value=0 })
  }
  if(isPlaying.value){ audio.value.pause(); isPlaying.value=false }
  else{
    audio.value.currentTime=0
    audio.value.play().then(()=> isPlaying.value=true).catch(()=> isPlaying.value=false)
  }
}
function onCoverClick(){ togglePlay() }
onBeforeUnmount(()=>{ audio.value?.pause(); audio.value=null })
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
  color:#fff !important;              /* pure white */
  text-shadow:0 1px 3px rgba(0,0,0,.35);
}
.glass-card:hover{
  background: rgba(255,255,255,0.22);
  box-shadow: 0 18px 38px -12px rgba(0,0,0,0.30);
}

.album-holder{ max-width: 360px; margin:0 auto; 
color:#fff ;              /* pure white */
  text-shadow:0 1px 3px rgba(0,0,0,.35);}
.album-img   { border-radius: 14px; }

.gap-2    { gap:.5rem }
.truncate { overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
</style>