<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  requestedItemNotifications: Object
});

const localNotifications = ref(Object.values(props.requestedItemNotifications));

// プロパティが変更された場合にローカル変数を更新(※自動では更新されない)
watch(() => props.requestedItemNotifications, (newNotifications) => {
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
      component: 'RequestedItemNotificationsTab.vue markAsRead method',
    });
  }
};
</script>

<template>
  <div>
    <div class="flex justify-around items-center">
      <div class="my-4 px-4 font-medium text-xs md:text-base">備品のリクエストが追加されました</div>
      <Link :href="route('item_requests.index')" class="px-4 text-blue-600 title-font font-medium text-xs md:text-base underline">
        リクエスト一覧へ
      </Link>
    </div>

    <div class="min-w-full overflow-auto flex justify-center">
      <table v-if="requestedItemNotifications.length > 0" class="table-fixed text-left whitespace-no-wrap">
        <thead>
          <tr>
            <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">商品名</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">申請者</th>
            <th class="min-w-48 md:min-w-48 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">申請理由</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="notification in requestedItemNotifications" :key="notification.id" class="border-t-2 border-gray-100">
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
                {{ notification.relative_time }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.item_name }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.requestor }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.remarks_from_requestor }}
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