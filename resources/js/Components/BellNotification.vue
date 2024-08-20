<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

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
  }
});
</script>

<template>
  <div class="relative">
      <!-- <button @click="showModal = !showModal"> -->
      <Link as="button" :href="route('notifications.index')" class="flex">
        <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>

        <span class="absolute top-0 right-0 text-xs bg-blue-500 text-white rounded-full w-4 h-4 flex items-center justify-center">
          {{ notifications.length }}
        </span>
      </Link>
      <!-- </button> -->

      <!-- モーダルのCSSが重要 -->
      <!-- <div v-if="showModal" class="absolute top-full mt-2 w-64 bg-white rounded shadow-xl overflow-auto max-h-64">
        <div v-for="notification in notifications" :key="notification.id">
            <p>{{ notification.data.item_name }}</p> -->
            <!-- あとでquantityをstocksに直す -->
            <!-- <p>{{ notification.data.quantity }}</p>
            <p>{{ notification.data.message }}</p>
        </div>
      </div> -->

  </div>
</template>