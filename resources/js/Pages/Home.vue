<template>
  <DefaultLayout>
    <!-- Create Post -->
    <div class="mx-auto w-full max-w-2xl px-4 sm:px-6">
    <v-card rounded="md" class="max-w-xl mx-5 mb-8">
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
        class="vw-search-bar mb-6 mx-auto"
        style="width:100%; max-width:350px;"
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
<!-- Preview: cover + centered text/buttons -->
<div v-if="hasTrack" class="album-wrap mb-6 flex flex-col items-center text-center">
 <v-img
    :src="form.track.coverUrl"
    class="album-art"
    cover
  />
  <div class="mt-4 text-center w-full px-2">
    <div class="text-subtitle-1 font-semibold truncate">
      {{ form.track.title }}
    </div>
    <div class="text-body-2 text-grey-darken-1 truncate">
      {{ form.track.artist }}
    </div>
  </div>

  <div class="d-flex align-center gap-3">
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
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <!-- Replace your “Post” button with this: -->
        <div class="text-right mt-6">
          <v-btn
            :disabled="!hasTrack"
            class="vw-post-underline-btn"
            @click="submit"
          >
            <v-icon left size="20">mdi-send</v-icon>
            <span>Post</span>
          </v-btn>
        </div>
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
.vw-post-underline-btn {
  background: transparent !important;
  color: #1DB954 !important;
  font-family: 'Poppins', sans-serif;
  font-weight: 500;
  text-transform: none !important;
  padding: 0 !important;
  min-width: 0 !important;
  position: relative;
  overflow: visible;
}

/* base underline */
.vw-post-underline-btn::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -2px;
  width: 100%;
  height: 2px;
  background: #1DB954;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

/* on hover (only when enabled) */
.vw-post-underline-btn:hover::after {
  transform: scaleX(1);
}

/* icon and text spacing */
.vw-post-underline-btn .v-icon {
  margin-right: 0.25rem;
}

/* disabled state */
.vw-post-underline-btn:disabled {
  color: #aaa !important;
}
.vw-post-underline-btn:disabled::after {
  background: #aaa;
}

.album-wrap {
  width: 100%;             /* make it fill its parent */
  max-width: 360px;        /* same as your PostCard */
  margin: 0 auto;
  padding: 20px;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.album-art {
  width: 100%;             /* fill the wrapper */
  aspect-ratio: 1 / 1;     /* keep it square */
  border-radius: 16px;
  object-fit: cover;
}
</style>