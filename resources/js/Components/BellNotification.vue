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
        🔔ベルマーク
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