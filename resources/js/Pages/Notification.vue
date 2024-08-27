<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  notifications: Array,
  lowStockNotifications: Array,
  disposalAndInspectionNotifications: Array,
  requestedItemNotifications: Array,
})

const localNotifications = ref([...props.notifications]);
// プロパティが変更された場合にローカル変数を更新
// 自動では反映してくれないところが注意点
watch(() => props.notifications, (newNotifications) => {
  localNotifications.value = [...newNotifications];
});

onMounted(() => {
  // console.log('props.notifications')
  // console.log(props.notifications)

  localNotifications.value.forEach(notification => {
    if (!notification.read_at) {
      markAsRead(notification.id);
    }
  });
})


const activeTab = ref('tab1')

// 画面を見たら既読にする処理、次回アクセスもしくは更新でオレンジの新着マークが消える
const markAsRead = async id => {
  console.log('notification_id')
  console.log(id)
  try {
    await axios.patch(`/api/notifications/${id}/read`)
    // 通知を既読にした後、Vue側ローカル側の状態を同期する（オレンジ●が消える）　idは引数のid
    // const notification = localNotifications.value.find(n => n.id === id)
    // if (notification) {
    //   notification.read_at = new Date().toISOString() // 日付を入れることで既読にする
    // }
  } catch (error) {
    console.error('APIでの既読処理が失敗しました', error)
  }
}

const handleClick = async (id, routeName, routeParams) => {
  await markAsRead(id)
  router.visit(routeName, { params: routeParams });
}

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
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold text-lg', activeTab === 'tab1' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab1'">
                              消耗品在庫数
                            </a>
                          </li>
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold text-lg', activeTab === 'tab2' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab2'">
                              点検・廃棄予定
                            </a>
                          </li>
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold text-lg', activeTab === 'tab3' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab3'">
                              リクエスト
                            </a>
                          </li>
                        </ul>
                      </div>
                      <div class="mt-4 flex justify-center">
                        <div v-if="activeTab === 'tab1'">
                          <div class="flex justify-around">
                            <div class="mb-4 px-4 font-medium">在庫数が通知在庫数以下になっています</div>
                            <Link class="mb-4 px-4 text-blue-600 title-font font-medium underline" :href="route('consumable_items')">
                              消耗品管理へ
                            </Link>
                          </div>
                          <div class="flex justify-center">
                            <div v-if="lowStockNotifications && lowStockNotifications.length" class="border-t border-gray-300">
                              <div v-for="notification in lowStockNotifications" :key="notification.id" class="border-b border-gray-300 py-4">
                                <div class="flex items-center justify-between space-x-8">
                                  <div v-if="!notification.read_at" class="text-orange-500">●</div>
                                  <!-- <div>{{ notification.id }}</div> -->
                                  <div>{{ notification.relative_time }}</div>
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                    <div class="text-blue-600 title-font font-medium">{{ notification.data.management_id }}</div>
                                  </Link>
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                    <img :src="notification.data.image_path1" alt="画像" class="w-16">
                                  </Link>
                                  <div>{{ notification.data.item_name }}</div>
                                  <div>{{ notification.data.quantity }} / {{ notification.data.minimum_stock }}</div>
                                  <Link :href="route('consumable_items', { item_id: notification.data.id })">
                                    <div class="text-blue-600 title-font font-medium">詳細を見る</div>
                                  </Link>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div v-if="activeTab === 'tab2'">
                          <div class="flex justify-around">
                            <div class="mb-4 px-4 font-medium">毎朝6時更新</div>
                            <Link :href="route('inspection_and_disposal_items')" class="mb-4 px-4 text-blue-600 title-font font-medium underline">
                              点検と廃棄へ
                            </Link>
                          </div>
                          <div class="flex justify-center">
                            <div v-if="disposalAndInspectionNotifications && disposalAndInspectionNotifications.length" class="border-t border-gray-300">
                              <div v-for="notification in disposalAndInspectionNotifications" :key="notification.id" class="border-b border-gray-300 py-4">
                                <div class="flex items-center justify-between space-x-8">
                                  <div v-if="!notification.read_at" class="text-orange-500">●</div>
                                  <div>{{ notification.id }}</div>
                                  <div>{{ notification.relative_time }}</div>
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                    <div class="text-blue-600 title-font font-medium">{{ notification.data.management_id }}</div>
                                  </Link>
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                    <img :src="notification.data.image_path1" alt="画像" class="w-16">
                                  </Link>
                                  <div>{{ notification.data.item_name }}</div>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>
                        <div v-if="activeTab === 'tab3'">
                          <div class="flex justify-around">
                            <div class="mb-4 px-4 font-medium">備品のリクエストが追加されました</div>
                            <Link :href="route('consumable_items')" class="mb-4 px-4 text-blue-600 title-font font-medium underline">
                              リクエスト一覧へ
                            </Link>
                          </div>
                          <div class="flex justify-center">
                            <div v-if="requestedItemNotifications && requestedItemNotifications.length" class="border-t border-gray-300">
                              <div v-for="notification in requestedItemNotifications" :key="notification.id" class="border-b border-gray-300 py-4">
                                <div class="flex items-center justify-between space-x-8">
                                  <div v-if="!notification.read_at" class="text-orange-500">●</div>
                                  <div>{{ notification.id }}</div>
                                  <div>{{ notification.relative_time }}</div>
                                  <div>{{ notification.data.item_name }}</div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
