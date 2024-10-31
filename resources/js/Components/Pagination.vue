<script setup>
import { Link } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';

defineProps({ links: Array })

// サニタイズ関数
const sanitizedHTML = (label) => DOMPurify.sanitize(label);
</script>

<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap -mb-1">
      <template v-for="(link, index) in links" :key="index">
        <div v-if="link.url === null" class="mr-1 mb-1 px-2 py-3 md:px-4 md:py-3 text-xs md:text-sm leading-4 text-gray-400 border rounded">
          <span v-html="sanitizedHTML(link.label)"></span>
        </div>
        <Link v-else
          class="mr-1 mb-1 px-2 py-3 md:px-4 md:py-3 text-xs md:text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
          :class="{ 'bg-blue-700 text-white': link.active }" :href="link.url">
          <span v-html="sanitizedHTML(link.label)"></span>
        </Link>
      </template>
    </div>
  </div>  
</template>
