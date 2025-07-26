<template>
  <LoginLayout>
    <div 
      class="login-wrap d-flex flex-column align-center justify-center pa-0"
      style="min-height:100vh; "
    >
      <!-- Cover Carousel Card -->
     
        <!-- Title -->
      <v-fade-transition mode="in-out">
  <div class="flex items-center">
      <!-- 1. Fixed-size wrapper -->
      <div class="mr-2 h-32 w-32 flex-shrink-0">
        <!-- 2. Fill the wrapper -->
        <img
          src="/logo.svg"
          alt="Logo"
          class="h-full w-full object-contain"
        />
      </div>
    </div>
</v-fade-transition>

<v-fade-transition mode="in-out" delay="100">
  <div v-if="show" class="tagline-container text-center mb-6">
    <p class="merriweather-tagline">
      Share the tracks you’re loving and let the community discover your vibe.
    </p>
    <p class="with-line">
      With <span class="vibewave-word">VibeWave</span>
    </p>
  </div>
</v-fade-transition>
 <v-sheet
     rounded="md"
  class="d-flex flex-column align-center pa-6 mb-8 mx-auto"
  style="width: 100%; max-width: 480px; background: #FFFFFF; "
>
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
    class="btn-spotify-circle mx-auto"
    @click="goSpotify"
    elevation="0"
  >
    Continue with <span class="spotify-word">Spotify</span>
  </v-btn>
</v-scale-transition>
      </v-sheet>
 <div class="features-dropcap max-w-lg mx-auto my-12 space-y-8">
      <div class="feature-row">
        <span class="dropcap">P</span>
        <div>
          <h4 class="feature-title">Vibe Post</h4>
          <p class="feature-desc">
            Share your favorite tracks—upload a Spotify link, add a caption, and let everyone catch your vibe.
          </p>
        </div>
      </div>

      <div class="feature-row">
        <span class="dropcap">L</span>
        <div>
          <h4 class="feature-title">Vibe Like</h4>
          <p class="feature-desc">
            React to posts you love—tap the heart to show appreciation and spread positive vibes.
          </p>
        </div>
      </div>

      <div class="feature-row">
        <span class="dropcap">C</span>
        <div>
          <h4 class="feature-title">Vibe Chat</h4>
          <p class="feature-desc">
            Join the conversation—leave comments and connect with fellow music lovers in real time.
          </p>
        </div>
      </div>
    </div>
   
    </div>
  </LoginLayout>
</template>

<script setup>
import { ref, onMounted, nextTick , onBeforeUnmount} from 'vue'
import { usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import LoginLayout from '@/Layouts/LoginLayout.vue'
import { Vue3Marquee as Marquee } from 'vue3-marquee'

const step = ref(1)


let lines = []


let splide = null

const { props } = usePage()
const covers = props.covers || []
const show = ref(false)
const carousel = ref(null)
const isMobile = ref(false)

// your three definitions
const entries = [
  {
    term: 'Vibe Post',
    pronunciation: '/vaɪb poʊst/',
    definition:
      'Share your favorite tracks—upload a Spotify link, add a caption & let everyone catch your vibe.',
  },
  {
    term: 'Vibe Like',
    pronunciation: '/vaɪb laɪk/',
    definition:
      'React to posts you love—tap the heart to show appreciation and spread good vibes.',
  },
  {
    term: 'Vibe Chat',
    pronunciation: '/vaɪb tʃæt/',
    definition:
      'Start a conversation—leave comments, talk about the track, and connect with fellow music lovers.',
  },
]

// track flipped state
const flipped = ref(entries.map(() => false))


onMounted(async () => {
  isMobile.value = window.innerWidth < 600
  window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 600
  })
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

.logo-title {
  font-family: 'Dancing Script', cursive;
  font-size: 3rem;
  color: #333333;           /* charcoal text */
  position: relative;
  display: inline-block;
  text-align: center;
  margin-bottom: 1.5rem;
}

/* SVG wave underline styling */
.logo-wave-svg {
  position: absolute;
  left: 0;
  bottom: -8px;
  width: 100%;
  height: 6px;
  overflow: visible;
}

.logo-wave-svg path {
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation: drawWave 1s ease-out 0.3s forwards;
}

@keyframes drawWave {
  to { stroke-dashoffset: 0; }
}

/* Optional subtle hover lift */
.logo-title:hover {
  transform: translateY(-2px) scale(1.02);
  transition: transform 0.3s ease;
}

.italic-tagline {
  max-width: 340px;
  margin: 0 auto 1.5rem;
  color: #424242;
  font-family: 'Playfair Display', serif;
  font-style: italic;
  font-size: 1.4rem;   /* ~18px */
  line-height: 1.4;
  letter-spacing: 0.5px;
}

.tagline-container {
  max-width: 340px;
  margin: 0 auto 1.5rem;
}

/* existing tagline style */
.merriweather-tagline {
  color: #424242;
  font-family: 'Merriweather', serif;
  font-style: italic;
  font-weight: 400;
  font-size: 1.375rem;
  line-height: 1.3;
  letter-spacing: 0.5px;
  margin: 0;
}

/* new “With VibeWave” line */
.vibewave-subtitle {
  color: #424242;
  font-family: 'Merriweather', serif;
  font-style: normal;
  font-weight: 700;
  font-size: 1.25rem;
  line-height: 1.2;
  letter-spacing: 0.5px;
  margin-top: 0.75rem;
}

.with-line {
  margin-top: 0.75rem;
  color: #424242;
  font-family: 'Merriweather', serif;
  font-style: normal;
  font-weight: 400;
  font-size: 1.25rem;   /* 20px */
  line-height: 1.3;
}

.with-line .vibewave-word {
  font-style: italic;
  color: #1DB954;
  /* if you have Playfair Display loaded, you could swap to that: */
  /* font-family: 'Playfair Display', serif; */
}

.btn-spotify-circle {
  border: 2px solid #3c3c3c!important;
  background-color: transparent !important;
  color: #000 !important;            /* all text black */
  border-radius: 9999px !important;
  padding: 0.5rem 1.5rem !important;
  box-shadow: none !important;
  font-family: 'Poppins', sans-serif !important;
  font-weight: 500 !important;
  text-transform: none !important;
  min-width: auto !important;
}



/* gentle hover tint */
.btn-spotify-circle:hover {
  background-color: rgba(134, 134, 134, 0.08) !important;
  transform: translateY(-1px);
}
.features-dropcap {
  padding: 0 1rem;
}

/* Each feature = one row */
.feature-row {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

/* Drop-cap initial */
.dropcap {
  font-family: 'Playfair Display', serif;
  font-size: 4rem;
  line-height: 1;
  color: #e0e0e0;
  flex-shrink: 0;
}

/* Title */
.feature-title {
 color: #424242;
  font-family: 'Merriweather', serif;
  font-style: normal;
  font-weight: 500;
  font-size: 1.35rem;   /* 20px */
  line-height: 1.3;
}

.feature-desc {
  font-family: 'Playfair Display', serif;  /* same elegant serif as the drop-cap/title */
  font-size: 1.14rem;
  line-height: 1.5;
  color: #555;
  margin: 0.25rem 0 0;
  font-style: italic;                     /* optional flourish */
}
/* Responsive tweak: on very narrow screens stack under */
@media (max-width: 400px) {
  .feature-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .dropcap {
    font-size: 3rem;
  }
}
</style>