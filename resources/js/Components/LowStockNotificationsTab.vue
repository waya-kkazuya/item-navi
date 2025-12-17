<script setup lang="ts">
import { ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { ref, onMounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

import type { Ref } from 'vue';
import type { NotificationType } from '@/@types/model';

type Props = {
  lowStockNotifications: NotificationType[];
};

const props = defineProps<Props>();

const localNotifications: Ref<NotificationType[]> = ref(props.lowStockNotifications);

// プロパティが変更された場合にローカル変数を更新(※自動では更新されない)
watch(
  () => props.lowStockNotifications,
  (newNotifications: NotificationType[]) => {
    localNotifications.value = newNotifications;
  }
);

onMounted(() => {
  Object.values(localNotifications.value).forEach((notification: NotificationType) => {
    if (!notification.read_at) {
      markAsRead(notification.id);
    }
  });
});

// 画面を開いたら既読にする処理、次回アクセスもしくは更新でオレンジの新着マークが消える
const markAsRead = async (id: string): Promise<void> => {
  try {
    await axios.patch(`/api/notifications/${id}/read`);
  } catch (error: any) {
    console.error('LowStockNotificationsTab.vue markAsRead method error:', error.message);
  }
};
</script>

<template>
  <div>
    <div class="my-4 flex justify-around items-center">
      <div class="flex justify-center items-center font-medium">
        <ExclamationTriangleIcon class="size-6 fill-yellow-200" />
        <div class="text-xs md:text-base">在庫数が少なくなっています</div>
      </div>
      <Link
        class="px-4 text-blue-600 title-font text-xs md:text-base font-medium underline"
        :href="route('consumable_items')"
      >
        消耗品管理へ
      </Link>
    </div>

    <div class="min-w-full overflow-auto flex justify-center">
      <table
        v-if="Object.keys(lowStockNotifications).length > 0"
        class="table-fixed text-left whitespace-no-wrap"
      >
        <thead>
          <tr>
            <th
              class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              通知
            </th>
            <th
              class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              管理ID
            </th>
            <th
              class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              備品名
            </th>
            <th
              class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              画像
            </th>
            <th
              class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              在庫数
            </th>
            <th
              class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-sm bg-gray-100"
            >
              モーダル
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="notification in lowStockNotifications"
            :key="notification.id"
            class="border-t-2 border-gray-100"
          >
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
              {{ notification.relative_time }}
            </td>
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              <Link :href="route('items.show', { item: notification.data.id })">
                <span class="text-blue-600 title-font font-medium text-xs md:text-sm">
                  {{ notification.data.management_id }}
                </span>
              </Link>
            </td>
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              {{ notification.data.item_name }}
            </td>
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              <Link
                :href="route('items.show', { item: notification.data.id })"
                class="flex justify-center"
              >
                <img :src="notification.data.image_path1" alt="画像" class="h-8" />
              </Link>
            </td>
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              {{ notification.data.quantity }} / {{ notification.data.minimum_stock }}
            </td>
            <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-sm">
              <Link :href="route('consumable_items', { item_id: notification.data.id })">
                <span class="text-blue-600 title-font font-medium text-xs md:text-sm"> 開く </span>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="flex items-center justify-center">
        <InformationCircleIcon class="w-6 h-6 text-black" />
        <div class="ml-2 text-center py-4 text-xs md:text-base">通知はありません</div>
      </div>
    </div>
  </div>
</template>
