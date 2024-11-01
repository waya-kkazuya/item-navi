<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';


const props = defineProps({
  lowStockNotifications: Object
});

const localNotifications = ref(Object.values(props.lowStockNotifications));

// プロパティが変更された場合にローカル変数を更新(※自動では更新されない)
watch(() => props.lowStockNotifications, (newNotifications) => {
  localNotifications.value = Object.values(newNotifications);
});

onMounted(() => {
  Object.values(localNotifications.value).forEach(notification => {
    if (!notification.read_at) {
      markAsRead(notification.id);
    }
  });
});

// 画面を開いたら既読にする処理、次回アクセスもしくは更新でオレンジの新着マークが消える
const markAsRead = async id => {
  try {
    await axios.patch(`/api/notifications/${id}/read`);
  } catch (e) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'LowStockNotificationsTab.vue markAsRead method',
    });
  }
};
</script>

<template>
  <div>
    <div class="my-4 flex justify-around items-center">
      <div class="flex justify-center items-center font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 fill-yellow-200">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div class="text-xs md:text-base">在庫数が少なくなっています</div>
      </div>
      <Link class="px-4 text-blue-600 title-font text-xs md:text-base font-medium underline" :href="route('consumable_items')">
        消耗品管理へ
      </Link>
    </div>
    
    <div class="min-w-full overflow-auto flex justify-center">
      <table v-if="lowStockNotifications.length > 0" class="table-fixed text-left whitespace-no-wrap">
        <thead>
          <tr>
            <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
            <th class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">管理ID</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">備品名</th>
            <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">画像</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">在庫数</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">モーダル</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="notification in lowStockNotifications" :key="notification.id" class="border-t-2 border-gray-100">
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
                {{ notification.relative_time }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                <Link :href="route('items.show', { item: notification.data.id })">
                  <span class="text-blue-600 title-font font-medium text-xs md:text-base">
                    {{ notification.data.management_id }}
                  </span>
                </Link>
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.item_name }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                <Link :href="route('items.show', { item: notification.data.id })" class="flex justify-center">
                  <img :src="notification.data.image_path1" alt="画像" class="h-8">
                </Link>
              </td>
              <td class=" border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.quantity }} / {{ notification.data.minimum_stock }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                <Link :href="route('consumable_items', { item_id: notification.data.id })">
                  <span class="text-blue-600 title-font font-medium text-xs md:text-base">
                    開く
                  </span>
                </Link>
              </td>
            </tr>
        </tbody>
      </table>
      <div v-else class="flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div class="ml-2 text-center py-4 text-xs md:text-base">通知はありません</div>
      </div>
    </div>
  </div>
</template>