<template>
  <DefaultLayout>
    <!-- CREATE & SEARCH SECTION -->
    <div class="max-w-xl mx-auto px-4 pt-6">
      <!-- add border-l-4 and pl-4 here -->
      <div class="create-section mb-6 border-l-4 pl-4" style="border-color: #1DB954">
        <!-- Search bar -->
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
           <template #item="{ props, item }">
    <v-list-item v-bind="props" class="flex items-center">
      <!-- album-art on the left -->
      <template #prepend>
        <v-avatar size="45" class="mr-3 ">
          <v-img :src="item.raw.coverUrl" />
        </v-avatar>
      </template>

      <!-- title & artist on the right -->
      <v-list-item-content>
        <v-list-item-title class="truncate">
          {{ item.raw.title }}
        </v-list-item-title>
        <v-list-item-subtitle class="truncate">
          {{ item.raw.artist }}
        </v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
  </template>
        </v-autocomplete>

        <!-- Preview + Post -->
        <div
          v-if="hasTrack"
          class="mt-6 mb-20 flex flex-col items-center"
          id="create-post"
        >
          <div class="album-wrap mb-3">
            <v-img
              :src="form.track.coverUrl"
              cover
              class="album-art"
            />
          </div>
          <div class="text-center px-2 mb-4 w-full max-w-xs">
            <div class="text-base font-semibold truncate">
              {{ form.track.title }}
            </div>
            <div class="text-sm text-gray-600 truncate">
              {{ form.track.artist }}
            </div>
          </div>
       
 <button
      class="post-pill "
      :disabled="!hasTrack"
      @click="submit"
    >
      <v-icon size="20">mdi-send</v-icon>
      <span>Post</span>
    </button>
        </div>
      </div>

      <!-- FEED -->
      <div class="space-y-6 pb-16">
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
import { useForm, router } from '@inertiajs/vue3'

// receive posts
const props = defineProps({
  posts: { type: Array, default: () => [] },
})

// search state
const query = ref('')
const selectedItem = ref(null)
const results = ref([])
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
  form.track = { ...item }
}

// form for posting
const form = useForm({
  track: {
    id: '',
    title: '',
    artist: '',
    coverUrl: '',
    previewUrl: '',
    externalUrl: '',
    durationMs: null,
  },
})

const hasTrack = computed(() => !!form.track.id && !!form.track.title)

function submit() {
  form.post('/posts', { preserveScroll: true })
}

// feed helpers
function avatarFrom(user) {
  const name = user?.name || 'Vibe Wave'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1DB954&color=ffffff&bold=true`
}

function toggleLike(id) {
  router.post(`/posts/${id}/like`, {}, { preserveScroll: true })
}

onBeforeUnmount(() => clearTimeout(searchTimer))
</script>

<style scoped>
/* panel accent */

.search-bar ::v-deep .v-list-item {
  min-height: 72px; /* ensure enough vertical room */
}

.search-bar ::v-deep .v-avatar {
  flex-shrink: 0;
}
.post-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border: 2px solid #1DB954;
  border-radius: 9999px;
  background: rgba(255,255,255,0.85);
  color: #1DB954;
  font-weight: 500;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}
.post-pill:hover:not(:disabled) {
  background: #1DB954;
  color: #fff;
  transform: translateY(-2px);
}
.post-pill:active:not(:disabled) {
  transform: translateY(0);
}
.post-pill:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.album-wrap {
  width: 200px;
  height: 200px;
  border-radius: 16px;
  overflow: hidden;
}
.album-art {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.vw-search-bar {
  width: 100%;
  max-width: 350px;
}
.pb-16 { padding-bottom: 4rem; }
</style>