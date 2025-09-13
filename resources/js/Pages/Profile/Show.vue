<template>
  <DefaultLayout>
    <div class="profile-page min-h-screen">
      <div class="mx-auto px-4 pt-6 max-w-5xl">
        <!-- Profile header -->
        <v-card rounded="lg" class="profile-card mb-6">
          <v-list-item class="py-4">
            <template #prepend>
              <v-avatar size="64">
                <v-img v-if="profile.avatar" :src="profile.avatar" alt="Profile avatar" cover />
                <v-icon v-else icon="mdi-account-circle" color="grey-darken-1" size="64" />
              </v-avatar>
            </template>

            <v-list-item-title class="text-h6 font-semibold text-neutral-900">
              {{ profile.name }}
            </v-list-item-title>

            <template #append>
              <v-btn
                v-if="profile.isSelf"
                variant="outlined"
                color="#1DB954"
                class="text-none"
                :href="editHref"
              >
                Edit profile
              </v-btn>
            </template>
          </v-list-item>
        </v-card>

        <!-- Posts grid -->
        <div
          v-if="postsSafe.length"
          class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-16"
        >
          <div v-for="(p, i) in postsSafe" :key="p.id ?? i" class="card-shell">
            <div class="bg-layer" :style="bgStyle(p)"></div>
            <PostCard :post="p" @toggle-like="toggleLike" class="relative" />
          </div>
        </div>

        <!-- Empty state -->
        <v-card v-else rounded="lg" class="empty-card py-10 text-center">
          <div class="text-body-1 opacity-80 text-neutral-800">
            No posts yet.
          </div>
          <div v-if="profile.isSelf" class="opacity-70 mt-1 text-neutral-700">
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
import { computed } from 'vue'

const props = defineProps({
  profile: { type: Object, default: () => null },
  profileUser: { type: Object, default: () => null },
  posts: { type: Array, default: () => [] },
})

/* normalize profile input */
const defaultProfile = { id: null, name: 'User', avatar: null, bio: null, isSelf: false }
const profile = computed(() => ({ ...defaultProfile, ...(props.profile || props.profileUser || {}) }))

/* normalize posts for PostCard */
const postsSafe = computed(() => {
  const list = Array.isArray(props.posts) ? props.posts : []
  return list
    .filter(Boolean)
    .map((p, idx) => ({
      id: p.id ?? idx,
      user: p.user ?? {},
      track: p.track ?? {},
      stats: p.stats ?? {},
      createdAt: p.createdAt ?? p.created_at ?? null,
    }))
})

function toggleLike(id) {
  router.post(`/posts/${id}/like`, {}, { preserveScroll: true })
}

/* --- background helpers for each card shell (use the cover the user already provided) --- */
const coverFrom = (post) =>
  post?.track?.coverUrl ||
  post?.track?.album?.images?.[0]?.url ||
  post?.track?.album?.images?.[1]?.url ||
  post?.track?.album?.images?.[2]?.url ||
  null

const proxied = (url) =>
  `https://images.weserv.nl/?url=${encodeURIComponent(url.replace(/^https?:\/\//, ''))}&w=700&h=700&blur=80`

const bgStyle = (post) => {
  const c = coverFrom(post)
  if (!c) return {}
  return { backgroundImage: `url("${proxied(c)}")` }
}

const editHref = computed(() => '/profile')
</script>

<style scoped>
/* Page backdrop: soft neutral gradient so the page isn’t “all white” */
.profile-page {
  position: relative;
  background: linear-gradient(180deg, #f6f7f9 0%, #eff1f5 55%, #e9ecf2 100%);
}

/* very subtle vignette so content pops */
.profile-page::before {
  content: "";
  position: fixed;
  inset: 0;
  pointer-events: none;
  background:
    radial-gradient(1200px 500px at 50% -200px, rgba(0,0,0,0.05), transparent 60%),
    radial-gradient(900px 400px at 50% 120%, rgba(0,0,0,0.06), transparent 55%);
  z-index: 0;
}

/* Header & empty cards keep a soft translucent skin */
.profile-card,
.empty-card {
  position: relative;
  z-index: 0;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(17, 24, 39, 0.08);
  box-shadow: 0 8px 26px rgba(16, 24, 40, 0.06);
  backdrop-filter: blur(6px);
}

/* ---------- Per-post “glass over blurred cover” wrapper ---------- */
.card-shell {
  position: relative;
  z-index: 0;
  overflow: hidden;
  border-radius: 18px;
}

.bg-layer {
  position: absolute;
  inset: 0;
  z-index: 0;
  background-size: cover;
  background-position: center;
  filter: blur(26px) brightness(0.72);
  transform: scale(1.06);
}

.profile-page :deep(.v-divider) { opacity: .6; }
</style>