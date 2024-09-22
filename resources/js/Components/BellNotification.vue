<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// リンクとして使用するか、文字列として使用するか
defineProps({
  isLink: {
    type: Boolean,
    required: true
  },
})

// 中身配列
const notifications = ref([]);
const showModal = ref(false);

onMounted(async () => {
  try {
    await axios.get('/api/notifications_count')
    .then(res => {
      console.log('BellNotification')
      console.log(res.data)
      notifications.value = res.data
    })
  } catch (e) {
    console.error(e);
    console.error('BellNotificationAPI通信失敗');
  }
});
</script>

<template>
  <div class="relative" v-if="isLink">
      <Link as="button" :href="route('notifications.index')" class="flex">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>

        <span class="absolute top-0 right-0 text-xs bg-blue-500 text-white rounded-full w-4 h-4 flex items-center justify-center">
          {{ notifications.length }}
        </span>
      </Link>
  </div>
  <div class="relative" v-else>
    <svg class="w-6 h-6 stroke-current text-gray-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>
    <span class="absolute top-0 right-0 text-xs bg-blue-500 text-white rounded-full w-4 h-4 flex items-center justify-center">
      {{ notifications.length }}
    </span>
  </div>
</template>