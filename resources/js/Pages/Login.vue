<template>
  <LoginLayout>
    <div 
      class="login-wrap d-flex flex-column align-center justify-center pa-0"
      style="min-height:100vh; "
    >
      <!-- Cover Carousel Card -->
      <v-sheet
        elevation="10"
        rounded="lg"
        class="d-flex flex-column align-center pa-6 mb-8"
        style="width:380px; background: #FFFFFF; border-top: 4px solid #1DB954;"
      >
        <!-- Title -->
        <v-fade-transition mode="in-out">
          <h2
            v-if="show"
            class="text-h4 font-weight-medium mb-2 text-center"
            style="color: #1DB954; font-family: 'Montserrat', sans-serif;"
          >
            Welcome to VibeWave
          </h2>
        </v-fade-transition>

        <!-- Tagline -->
        <v-fade-transition mode="in-out" delay="100">
          <p
            v-if="show"
            class="subtitle-1 text-center mb-6"
            style="color: #424242; max-width: 340px;"
          >
            Share the tracks you’re loving and let the community discover your vibe.
          </p>
        </v-fade-transition>

        <!-- Carousel -->
      <Marquee
        v-if="show && covers.length"
        class="track-carousel mb-6 d-flex"
        :speed="40"          
        :pauseOnHover="true" >
        <div
          v-for="(url, i) in covers"
          :key="i"
          class="track-item mx-2"
        >
          <v-img
            :src="url"
            height="200"
            width="200"
            class="track-art"
            style="border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.15);"
          />
        </div>
      </Marquee>

        <!-- CTA -->
        <v-scale-transition mode="in-out" delay="200">
          <v-btn
            v-if="show"
            large
            block
            color="#1DB954"
            class="white--text"
            @click="goSpotify"
            style="min-width:220px;"
          >
            <v-icon left>mdi-spotify</v-icon>
            Continue with Spotify
          </v-btn>
        </v-scale-transition>
      </v-sheet>

      <!-- Features Card (Separate) -->
      <v-sheet
        elevation="2"
        rounded="lg"
        class="d-flex flex-column pa-4"
        style="width:380px; background: #FFFFFF;"
      >
        <v-row dense justify="space-around">
          <v-col cols="4" class="text-center">
            <v-icon size="36" color="#1DB954">mdi-playlist-music</v-icon>
            <p class="subtitle-2 font-weight-medium mt-2">Post Tracks</p>
          </v-col>
          <v-col cols="4" class="text-center">
            <v-icon size="36" color="#1DB954">mdi-thumb-up-outline</v-icon>
            <p class="subtitle-2 font-weight-medium mt-2">Like & React</p>
          </v-col>
          <v-col cols="4" class="text-center">
            <v-icon size="36" color="#1DB954">mdi-comment-outline</v-icon>
            <p class="subtitle-2 font-weight-medium mt-2">Comment</p>
          </v-col>
        </v-row>
        <p
          class="caption text-center mt-4"
          style="color:#555; max-width: 360px; margin: 0 auto;"
        >
          Post your favorite Spotify songs, react with likes, and spark conversations through comments as you explore a vibrant community of music lovers.
        </p>
      </v-sheet>
    </div>
  </LoginLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import LoginLayout from '@/Layouts/LoginLayout.vue'
import { Vue3Marquee as Marquee } from 'vue3-marquee'


let splide = null

const { props } = usePage()
const covers = props.covers || []
const show = ref(false)
const carousel = ref(null)

onMounted(async () => {
  await new Promise(r => setTimeout(r, 300))
  show.value = true

  await nextTick()
  if (carousel.value && covers.length > 1) {
    const items = carousel.value.querySelectorAll('.track-item')
    const second = items[1]
    if (second) {
      const w = carousel.value.clientWidth
      const scrollX = second.offsetLeft - (w - second.clientWidth) / 2
      carousel.value.scrollTo({ left: scrollX, behavior: 'smooth' })
    }
  }
})

function goSpotify() {
  window.location.href = route('login.spotify')
}
</script>

<style scoped>
.track-carousel {
  overflow-x: auto;
  display: flex;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scroll-behavior: smooth;
  touch-action: pan-x;
  cursor: grab;
  padding-bottom: 8px;
  width: 100%;
}
.track-carousel:active { cursor: grabbing; }
.track-item { flex: 0 0 auto; scroll-snap-align: center; }
.track-art { user-select: none; pointer-events: none; }
.track-carousel::-webkit-scrollbar { display: none; }
.track-carousel { -ms-overflow-style: none; scrollbar-width: none; }
</style>
