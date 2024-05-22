<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// 中身配列
const notifications = ref([]);
const showModal = ref(false);

onMounted(async () => {
  try {
    const response = await axios.get('/api/notifications');
    notifications.value = response.data;
    console.log(notifications.value)
  } catch (error) {
    console.error(error);
  }
});
</script>

<template>
  <div class="relative">

      <button @click="showModal = !showModal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
      <span class="absolute top-0 right-0 text-xs bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
        {{ notifications.length }}
      </span>
      </button>

      <!-- モーダルのCSSが重要 -->
      <div v-if="showModal" class="absolute top-full mt-2 w-64 bg-white rounded shadow-xl overflow-auto max-h-64">
        <div v-for="notification in notifications" :key="notification.id">
            <p>{{ notification.data.item_name }}</p>
            <!-- あとでquantityをstocksに直す -->
            <p>{{ notification.data.quantity }}</p>
            <p>{{ notification.data.message }}</p>
        </div>
      </div>

  </div>
</template>