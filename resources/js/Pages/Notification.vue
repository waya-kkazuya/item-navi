<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
  lowStockNotifications: Array,
  disposalScheduleNotifications: Array,
  inspectionScheduleNotifications: Array,
  requestedItemNotifications: Array,
  notifications: Array
})

const activeTab = ref('tab1')

// 既読にする処理
const markAsRead = async notificationId => {
  try {
    await axios.post(`/notifications/${notificationId}/read`)
    // 通知を既読に更新
    const notification = lowStockNotifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.read_at = new Date().toISOString()
    }
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}



// const fetchNotifications = async () => {
//     try {
//         const res = await axios.get('/api/notifications');
//         console.log('res.data',res.data)
//         notifications.value = res.data
//     } catch (error) {
//         console.error('Error fetching notifications:', error);
//     }
// }

// 画面描画時に通信をする
// onMounted(() => {
  // console.log(notifications)
    // fetchNotifications();
// })

</script>

<template>
    <Head title="Notification" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              通知
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <div class="flex justify-center">
                        <ul class="flex space-x-6 max-w-md">
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold', activeTab === 'tab1' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab1'">
                              消耗品在庫数
                            </a>
                          </li>
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold', activeTab === 'tab2' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab2'">
                              点検・廃棄予定
                            </a>
                          </li>
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold', activeTab === 'tab3' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab3'">
                              リクエスト
                            </a>
                          </li>
                        </ul>
                      </div>
                      <div class="mt-4 flex justify-center">
                        <div v-if="activeTab === 'tab1'">
                          <div class="mb-4 font-medium">在庫数が通知在庫数以下になっています</div>
                          <div class="flex justify-center">
                            <div v-if="lowStockNotifications.length" class="border-t border-gray-300">
                                <div v-for="notification in lowStockNotifications" :key="notification.id" @click="markAsRead(notification.id)" class="border-b border-gray-300 py-4">
                                  <div class="flex items-center justify-between space-x-8">
                                    <div v-if="!notification.read_at" class="text-orange-500">●</div>
                                    <div>{{ notification.relative_time }}</div>
                                    <Link :href="route('items.show', { item: notification.data.id })">
                                      <div class="text-blue-600 title-font font-medium">{{ notification.data.management_id }}</div>
                                    </Link>
                                    <Link :href="route('items.show', { item: notification.data.id })">
                                      <img :src="notification.data.image_path1" alt="画像" class="w-16">
                                    </Link>
                                    <div>{{ notification.data.item_name }}</div>
                                    <div>{{ notification.data.quantity }}</div>
                                    <Link :href="route('consumable_items', { item_id: notification.data.id })">
                                      <div class="text-blue-600 title-font font-medium">詳細を見る</div>
                                    </Link>
                                    <!-- <div>{{ notification.data.message }}</div> -->
                                  </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div v-if="activeTab === 'tab2'">
                          タブ2の内容
                          <div class="mb-4 font-medium">毎朝8時更新</div>
                        </div>
                        <div v-if="activeTab === 'tab3'">
                          タブ3の内容
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
