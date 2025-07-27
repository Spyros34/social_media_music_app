<template>
  <DefaultLayout>
    <!-- Create Post -->
    <div class="mx-auto w-full max-w-2xl px-4 sm:px-6">
    <v-card elevation="2" rounded="lg" class="max-w-xl mx-5 mb-8">
      <v-card-title class="text-subtitle-1 font-semibold">
        Create a post
      </v-card-title>

      <v-card-text class="space-y-4">
       <v-autocomplete
  v-model="selectedItem"
  v-model:search="query"
  :items="results"
  :loading="searching"       
  item-title="display"
  item-value="id"
  :return-object="true"
  clearable
  hide-details
  variant="outlined"           
  flat                         
  rounded
  density="comfortable"      
  placeholder="Search songs..."
  prepend-inner-icon="mdi-magnify"
  menu-icon=""               
  append-inner-icon=""        
  class="vw-search-bar mb-6"
  style="max-width:350px;"
  @update:search="onSearchUpdate"
  @update:modelValue="onSelect"
>
          <!-- custom row -->
          <template #item="{ props, item }">
            <v-list-item v-bind="props">
              <template #prepend>
                <v-avatar size="45" class="mr-3">
                  <v-img :src="item.raw.coverUrl" />
                </v-avatar>
              </template>
              <v-list-item-title class="truncate">
                {{ item.raw.title }}
              </v-list-item-title>
              <v-list-item-subtitle class="truncate">
                {{ item.raw.artist }}
              </v-list-item-subtitle>
            </v-list-item>
          </template>
        </v-autocomplete>

        

        <!-- Preview -->
<div v-if="hasTrack" class="d-flex align-start gap-4">
  <v-img
    v-if="form.track.coverUrl"
    :src="form.track.coverUrl"
    height="120"
    width="120"
    class="cover-img flex-shrink-0"
    cover
  />
  <div class="flex-1 min-w-0">
    <div class="text-subtitle-1 font-semibold truncate">
      {{ form.track.title }}
    </div>
    <div class="text-body-2 text-grey-darken-1 truncate">
      {{ form.track.artist }}
    </div>

    <div class="mt-2 d-flex align-center gap-2">
      <v-btn
        v-if="form.track.externalUrl"
        variant="tonal"
        color="#1DB954"
        class="text-black"
        :href="form.track.externalUrl"
        target="_blank"
        size="small"
      >
        <v-icon start icon="mdi-spotify" />
        Open
      </v-btn>

      <v-btn
        v-if="form.track.previewUrl"
        variant="text"
        size="small"
        @click="togglePreview"
        :prepend-icon="isPlaying ? 'mdi-pause' : 'mdi-play'"
      >
        Preview
      </v-btn>
    </div>
  </div>
</div>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn
          :loading="form.processing"
          :disabled="!hasTrack"
          color="#1DB954"
          class="text-black"
          @click="submit"
        >
          Post
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Feed -->
    <div class="max-w-xl mx-5 space-y-6">
     <PostCard
  v-for="p in posts"
  :key="p.id"
  :post="{
    ...p,
    user: { ...p.user, avatar: p.user.avatar || avatarFrom(p.user) }
  }"
  @toggle-like="toggleLike"
/>
    </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import PostCard from '@/Components/PostCard.vue'
import { ref, computed, onBeforeUnmount } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  posts: { type: Array, default: () => [] },
})

/* ---------- Search ---------- */
const query = ref('')
const selectedItem = ref(null)
const results = ref([]) // [{ id, title, artist, coverUrl, previewUrl, externalUrl, durationMs, display }]
const searching = ref(false)
let searchTimer

async function runSearch(q) {
  searching.value = true
  try {
    const res = await fetch(`/spotify/search?q=${encodeURIComponent(q)}`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    })
    const data = await res.json()
    results.value = (data.items || []).map(t => ({
      ...t,
      display: `${t.title} — ${t.artist}`,
    }))
  } finally {
    searching.value = false
  }
}

function onSearchUpdate(val) {
  clearTimeout(searchTimer)
  query.value = val
  if (!val || val.trim().length < 2) {
    results.value = []
    return
  }
  searchTimer = setTimeout(() => runSearch(val.trim()), 250)
}

function onSelect(item) {
  if (!item) return
  selectedItem.value = item
  form.track = {
    ...form.track,
    ...item,
  }
}

/* ---------- Create Post (hidden form) ---------- */
const form = useForm({
  track: {
    id: '',
    title: '',
    artist: '',
    coverUrl: '',
    previewUrl: '',
    externalUrl: '',
    durationMs: null,
    caption: '',
  },
})

const hasTrack = computed(() =>
  !!form.track.id && !!form.track.title
)

function submit() {
  form.post('/posts', { preserveScroll: true })
}

/* optional 30s preview */
const audio = ref(null)
const isPlaying = ref(false)
function togglePreview() {
  if (!form.track.previewUrl) return
  if (!audio.value) {
    audio.value = new Audio(form.track.previewUrl)
    audio.value.addEventListener('ended', () => { isPlaying.value = false })
  }
  if (isPlaying.value) {
    audio.value.pause()
    isPlaying.value = false
  } else {
    audio.value.currentTime = 0
    audio.value.play()
      .then(() => { isPlaying.value = true })
      .catch(() => { isPlaying.value = false })
  }
}
onBeforeUnmount(() => {
  if (audio.value) {
    audio.value.pause()
    audio.value.src = ''
    audio.value = null
  }
})

/* ---------- Feed helpers ---------- */
function avatarFrom(user) {
  const name = user?.name || 'Vibe Wave'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1DB954&color=ffffff&bold=true`
}
function toggleLike(id) {
  router.post(`/posts/${id}/like`, {}, { preserveScroll: true })
}


</script>

<style scoped>
.space-y-4 > * + * { margin-top: 1rem; }
.gap-4 { gap: 1rem; }
.truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

</style>