<script setup lang="ts">
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import type { NotificationType } from '@/@types/model';

type Props = {
  inspectionAndDisposalNotifications: NotificationType[];
}

const props = defineProps<Props>();

// inspectionAndDisposalNotificationsはコントローラで加工して配列になっているので注意が必要
const localNotifications: Ref<NotificationType[]> = ref(props.inspectionAndDisposalNotifications);

// プロパティが変更された場合にローカル変数を更新(※自動では更新されない)
watch(() => props.inspectionAndDisposalNotifications, (newNotifications: NotificationType[]) => {
  localNotifications.value = [...newNotifications];
});

onMounted(() => {
  // NotificationControllerで点検と廃棄のデータをまとめているので、他タブとやり方が異なる
  localNotifications.value.forEach((notification: NotificationType) => {
    if (!notification.read_at) {
      markAsRead(notification.id);
    }
  });
});

// 画面を開いたら既読にする処理、次回アクセスもしくは更新でオレンジの新着マークが消える
const markAsRead = async (id: string): Promise<void> => {
  try {
    await axios.patch(`/api/notifications/${id}/read`);
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'inspectionAndDisposalNotificationsTab.vue markAsRead method',
    });
  }
};
</script>

<template>
  <div>
    <div class="flex justify-around items-center">
      <div class="my-4 px-4 font-medium text-xs md:text-base">※毎朝6時更新</div>
      <Link :href="route('inspection_and_disposal_items')" class="px-4 text-blue-600 title-font font-medium text-xs md:text-base underline">
        点検と廃棄へ
      </Link>
    </div>
    
    <div class="min-w-full overflow-auto flex justify-center">
      <table v-if="Object.keys(inspectionAndDisposalNotifications).length > 0" class="table-fixed text-left whitespace-no-wrap">
        <thead>
          <tr>
            <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
            <th class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">管理ID</th>
            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">備品名</th>
            <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">画像</th>
            <th class="min-w-48 md:min-w-48 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">メッセージ</th>
            <th class="min-w-48 md:min-w-48 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">点検予定日</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="notification in inspectionAndDisposalNotifications" :key="notification.id" class="border-t-2 border-gray-100">
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
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.message }}
              </td>
              <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                {{ notification.data.scheduled_date }}
              </td>
            </tr>
        </tbody>
      </table>
      <div v-else class="flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div class="ml-2 text-center text-xs md:text-base py-4">通知はありません</div>
      </div>
    </div>
  </div>
</template>