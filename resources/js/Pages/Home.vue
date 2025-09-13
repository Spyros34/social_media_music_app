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
          @click:clear="clearSelection"
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
            <div class="text-base font-semibold truncate preview-title">
              {{ form.track.title }}
            </div>
            <div class="text-sm text-gray-600 truncate  preview-artist">
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
  <div
    v-for="p in posts"
    :key="p.id"
    class="observer-target"      
    :data-post-id="p.id"
  >
    <PostCard
      :post="{
        ...p,
        user: { ...p.user, avatar: p.user.avatar || avatarFrom(p.user) }
      }"
      @toggle-like="toggleLike"
       @delete-post="deletePost"
    />
  </div>
</div>
</div>
  </DefaultLayout>
</template>

<script setup>
/* ───────── Imports ───────── */
import DefaultLayout    from '@/Layouts/DefaultLayout.vue'
import PostCard         from '@/Components/PostCard.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

/* ───────── Props ───────── */
const props = defineProps({ posts: { type: Array, default: () => [] } })

/* ───────── 1.  Search & “create-post” logic (unchanged) ───────── */
const query        = ref('')
const selectedItem = ref(null)
const results      = ref([])
const searching    = ref(false)
let   timer        = null

async function runSearch (q){
  searching.value = true
  try{
    const r = await fetch(`/spotify/search?q=${encodeURIComponent(q)}`)
    const d = await r.json()
    results.value = (d.items ?? []).map(t => ({ ...t, display:`${t.title} — ${t.artist}` }))
  }finally{ searching.value = false }
}
function onSearchUpdate (v) {
  clearTimeout(timer)
  query.value = v

  // If user clears the field (typing backspace or clicking ✕),
  // also clear the selected item and the post preview.
  if (!v || v.trim().length < 2) {
    results.value = []
    selectedItem.value = null
    form.track = {
      id: '',
      title: '',
      artist: '',
      coverUrl: '',
      previewUrl: '',
      externalUrl: '',
      durationMs: null,
    }
    return
  }

  timer = setTimeout(() => runSearch(v.trim()), 250)
}

const form     = useForm({ track:{ id:'',title:'',artist:'',coverUrl:'',previewUrl:'',externalUrl:'',durationMs:null }})
const hasTrack = computed(() => !!form.track.id)
function onSelect(i){
  if (!i) {               // X clicked or selection cleared
    clearSelection()
    return
  }
  form.track = { ...i }   // normal select
}
function submit (){ hasTrack.value && form.post('/posts',{ preserveScroll:true }) }

function clearSelection(){
  selectedItem.value = null
  query.value = ''
  form.track = { ...form.defaults().track }
}

onBeforeUnmount(()=> clearTimeout(timer))

function avatarFrom(u){
  const n = u?.name || 'Vibe Wave'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(n)}&background=1DB954&color=fff`
}
function toggleLike(id){
  router.post(`/posts/${id}/like`, {}, { preserveScroll:true })
}

/* ───────── 2.  Blurred-cover page background ───────── */

/* proxy helper → returns CORS-safe, down-scaled image */
const proxied = url =>
  `https://images.weserv.nl/?url=${encodeURIComponent(url.replace(/^https?:\/\//,''))}&w=600&h=600`

let activeCover = ''          // last cover URL applied
function setPageBackground(url){
  if(!url || url === activeCover) return
  activeCover = url

  const root = document.documentElement
  root.classList.add('is-fading')                                  // start fade
  root.style.setProperty('--page-bg', `url("${proxied(url)}")`)    // place new img

  /* remove helper class after transition (must match CSS 2.8 s) */
  setTimeout(()=> root.classList.remove('is-fading'), 2800)
}

/* IntersectionObserver – wait until 85 % of a post is visible */
let obs = null, activeId = null
function whenIntersect(entries){
  const best = entries.filter(e=>e.isIntersecting)
                      .sort((a,b)=> b.intersectionRatio - a.intersectionRatio)[0]
  if(!best) return
  const pid = best.target.dataset.postId
  if(!pid || pid === activeId) return
  activeId = pid

  const post = props.posts.find(p => String(p.id) === pid)
  setPageBackground(post?.track?.coverUrl ?? null)
}

function mountObserver(){
  obs?.disconnect()
  obs = new IntersectionObserver(whenIntersect, { threshold:[0.85] }) // ← later trigger
  nextTick(()=> document.querySelectorAll('.observer-target').forEach(el=> obs.observe(el)))
}

function deletePost(id) {
  router.delete(`/posts/${id}`, { preserveScroll: true })
}


/* initialise + re-initialise when feed changes */
onMounted(()=> mountObserver())
watch(()=> props.posts.length, () => setTimeout(mountObserver, 80))
</script>
<style scoped>

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

.preview-title {
  color: rgba(255, 255, 255, 0.768);

}

.preview-artist {
  color: rgba(255, 255, 255, 0.637);

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
  color: #ffffffc3;

}
.pb-16 { padding-bottom: 4rem; }
</style>
<style>
/* the two stacked layers stay the same – we simply slow the fade */
body::before,
body::after{
  content:'';
  position:fixed;
  inset:0;
  z-index:-1;
  background:var(--page-bg,#f5f5f5) center/cover no-repeat;
  filter:blur(40px) brightness(.7);
  transform:scale(1.1);
  transition:opacity 1.2s cubic-bezier(.4,0,.2,1); /* ⬅︎ slower & silkier */
}

body::before{ opacity:1 }
body::after { opacity:0 }

body.is-fading::before{ opacity:0 }
body.is-fading::after { opacity:1 }
</style>