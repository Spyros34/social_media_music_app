<template>

       <div class="flex items-center justify-between ">

  </div >
    <div class="flex-1  bg-gray-100 min-h-screen " >
      <!-- Header -->
   
    <main class="mt-6">
        <slot />
      </main>

    <footer class="p-4 bg-gray text-center">
      © {{ new Date().getFullYear() }} MyApp
    </footer>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue'
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
const { props: pageProps } = usePage();
const authUser = pageProps.auth.user;


</script>