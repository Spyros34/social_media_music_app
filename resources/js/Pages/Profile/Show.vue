<!-- resources/js/Pages/Profile.vue -->
<template>
  <DefaultLayout>
    <div class="profile-page min-h-screen">
      <div class="mx-auto px-4 pt-6 max-w-5xl">
        <!-- Header (centered, no buttons) -->
        <v-card rounded="xl" class="profile-hero mb-5">
          <div class="hero-inner">
            <v-avatar size="84" class="hero-avatar">
              <v-img v-if="profile.avatar" :src="profile.avatar" alt="Profile avatar" cover />
              <v-icon v-else icon="mdi-account-circle" color="grey-darken-1" size="84" />
            </v-avatar>

            <div class="hero-text">
              <div class="hero-name">{{ profile.name }}</div>
              <div v-if="profile.bio" class="hero-bio">
                {{ profile.bio }}
              </div>
              <div class="hero-stats">
                <span><strong>{{ postsCount }}</strong> Posts</span>
                <span class="dot">•</span>
                <span><strong>{{ likedCount }}</strong> Liked</span>
              </div>
            </div>
          </div>
        </v-card>

        <!-- Segmented control (sticky) -->
        <div class="sticky-tabs sticky top-0 z-30">
          <div role="tablist" aria-label="Profile sections" class="segmented">
            <button
              role="tab"
              :aria-selected="tab==='posts'"
              :tabindex="tab==='posts' ? 0 : -1"
              class="seg-btn"
              :class="{ active: tab==='posts' }"
              @click="tab='posts'"
            >
              Posts
              <span v-if="postsCount" class="count">{{ postsCount }}</span>
            </button>

            <button
              role="tab"
              :aria-selected="tab==='liked'"
              :tabindex="tab==='liked' ? 0 : -1"
              class="seg-btn"
              :class="{ active: tab==='liked' }"
              @click="tab='liked'"
            >
              Liked
              <span v-if="likedCount" class="count">{{ likedCount }}</span>
            </button>

            <!-- animated active pill -->
            <span class="seg-active" :style="segStyle"></span>
          </div>
        </div>

        <!-- Grid -->
        <div
          v-if="visibleList.length"
          class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-16"
          :key="tab"
        >
          <div v-for="(p, i) in visibleList" :key="p.id ?? i" class="card-shell">
            <div class="bg-layer" :style="bgStyle(p)"></div>
            <PostCard :post="p" @toggle-like="toggleLike" class="relative" />
          </div>
        </div>

        <!-- Empty state -->
        <v-card v-else rounded="lg" class="empty-card py-10 text-center">
          <div class="text-body-1 opacity-80 text-neutral-800">
            {{ tab === 'posts' ? 'No posts yet.' : 'No liked posts yet.' }}
          </div>
          <div v-if="profile.isSelf && tab==='posts'" class="opacity-70 mt-1 text-neutral-700">
            Share your first song from the home page.
          </div>
        </v-card>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import PostCard from '@/Components/PostCard.vue'
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  profile: { type: Object, default: () => null },
  profileUser: { type: Object, default: () => null },
  posts: { type: Array, default: () => [] },   // user's own posts
  liked: { type: Array, default: () => [] },   // posts the user liked
})

/* normalize profile */
const defaultProfile = { id: null, name: 'User', avatar: null, bio: null, isSelf: false }
const profile = computed(() => ({ ...defaultProfile, ...(props.profile || props.profileUser || {}) }))

/* normalize posts for PostCard */
function normalize(list) {
  const arr = Array.isArray(list) ? list : []
  return arr.filter(Boolean).map((p, idx) => ({
    id: p.id ?? idx,
    user: p.user ?? {},
    track: p.track ?? {},
    stats: p.stats ?? {},
    createdAt: p.createdAt ?? p.created_at ?? null,
  }))
}

const ownPosts   = computed(() => normalize(props.posts))
const likedPosts = computed(() => normalize(props.liked))

/* counts */
const postsCount = computed(() => ownPosts.value.length)
const likedCount = computed(() => likedPosts.value.length)

/* active tab */
const tab = ref('posts')
const visibleList = computed(() => (tab.value === 'posts' ? ownPosts.value : likedPosts.value))

/* like action */
function toggleLike(id) {
  router.post(`/posts/${id}/like`, {}, { preserveScroll: true })
}

/* blurred background for each card shell */
const coverFrom = (post) =>
  post?.track?.coverUrl ||
  post?.track?.album?.images?.[0]?.url ||
  post?.track?.album?.images?.[1]?.url ||
  post?.track?.album?.images?.[2]?.url || null

const proxied = (url) =>
  `https://images.weserv.nl/?url=${encodeURIComponent(url.replace(/^https?:\/\//, ''))}&w=700&h=700&blur=80`

const bgStyle = (post) => {
  const c = coverFrom(post)
  return c ? { backgroundImage: `url("${proxied(c)}")` } : {}
}

/* segmented active pill movement */
const segStyle = computed(() => ({
  transform: `translateX(${tab.value === 'posts' ? '0%' : '100%'})`,
}))
</script>

<style scoped>
/* Page backdrop (soft neutral → fits your feed glass) */
.profile-page {
  position: relative;
  background: linear-gradient(180deg, #f6f7f9 0%, #eff1f5 55%, #e9ecf2 100%);
}
.profile-page::before {
  content: "";
  position: fixed; inset: 0; pointer-events: none; z-index: 0;
  background:
    radial-gradient(1200px 500px at 50% -200px, rgba(0,0,0,0.05), transparent 60%),
    radial-gradient(900px 400px at 50% 120%, rgba(0,0,0,0.06), transparent 55%);
}

/* ---------- HERO (no actions) ---------- */
.profile-hero{
  background: rgba(255,255,255,.78);
  border: 1px solid rgba(17,24,39,.08);
  box-shadow: 0 8px 26px rgba(16,24,40,.06);
  backdrop-filter: blur(6px);
  padding: 16px 14px;
}
.hero-inner{
  display:flex; align-items:center; gap:14px;
}
.hero-avatar{
  box-shadow: 0 6px 16px rgba(16,24,40,.10);
}
.hero-text{
  display:flex; flex-direction:column; gap:6px;
}
.hero-name{
  font-weight: 700; font-size: 20px; color: #0f172a;
}
.hero-bio{
  font-size: 13px; color: #374151; line-height: 1.25;
}
.hero-stats{
  display:flex; align-items:center; gap:8px;
  font-size: 12px; color: #4b5563;
}
.hero-stats .dot{ opacity:.5; }

/* ---------- Sticky segmented control ---------- */
/* Sticky segmented control */
.sticky-tabs{
  position: sticky;
  top: 0.25rem;
  padding: 4px 0;
  z-index: 20;
}

/* mobile: full width look */
.segmented{
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
  padding: 6px;
  border-radius: 14px;
  background: rgba(255,255,255,.65);
  border: 1px solid rgba(17,24,39,.08);
  backdrop-filter: blur(8px);
  box-shadow: 0 6px 18px rgba(16,24,40,.06);
  width: 100%;
}

/* desktop/tablet: center the pill, constrain width */
@media (min-width: 768px){
  .segmented{
    max-width: 360px;
    margin-left: auto;
    margin-right: auto; /* centers it */
  }
}
.seg-btn{
  position: relative; z-index: 1;
  height: 40px; border-radius: 10px;
  font-size: 14px; font-weight: 600;
  color: rgba(17,24,39,.7);
  display:flex; align-items:center; justify-content:center; gap:6px;
  background: transparent; border: 0;
}
.seg-btn.active{ color: #0f172a; }
.seg-btn .count{ font-size: 12px; font-weight: 700; color: rgba(17,24,39,.6); }

/* animated pill */
.seg-active{
  position: absolute; top: 6px; left: 6px; width: calc(50% - 6px); height: 40px;
  border-radius: 10px;
  background: rgba(255,255,255,.95);
  box-shadow: 0 6px 18px rgba(16,24,40,.08), inset 0 0 0 1px rgba(17,24,39,.06);
  transition: transform .22s cubic-bezier(.2,.8,.2,1);
}

/* ---------- Post shells (glass over blurred cover) ---------- */
.card-shell { position: relative; z-index: 0; overflow: hidden; border-radius: 18px; }
.bg-layer {
  position: absolute; inset: 0; z-index: 0;
  background-size: cover; background-position: center;
  filter: blur(26px) brightness(0.72);
  transform: scale(1.06);
}

/* Vuetify polish */
.profile-page :deep(.v-divider) { opacity: .6; }


</style>