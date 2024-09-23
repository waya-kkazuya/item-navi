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
  })
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

// const handleClick = async (id, routeName, routeParams) => {
//   await markAsRead(id)
//   router.visit(routeName, { params: routeParams });
// }

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
                  <ul class="flex space-x-2 md:space-x-6 max-w-md">
                    <li class="flex-grow">
                      <a :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab1' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab1'">
                        消耗品在庫数
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab2' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab2'">
                        点検・廃棄予定
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab3' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'tab3'">
                        リクエスト
                      </a>
                    </li>
                  </ul>
                </div>
                <div class="mt-4 flex justify-center">
                  <div v-if="activeTab === 'tab1'">
                    <div class="flex justify-around items-end">
                      <Link class="mb-4 px-4 text-blue-600 title-font font-medium underline" :href="route('consumable_items')">
                        消耗品管理へ
                      </Link>
                    </div>
                    <!-- <div>{{ lowStockNotifications }}</div> -->
                    <div>{{ requestedItemNotifications }}</div>
                    <div class="min-w-full overflow-auto">
                      <table v-if="lowStockNotifications" class="table-fixed min-w-full text-left whitespace-no-wrap">
                        <thead>
                          <tr>
                            <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
                            <th class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">管理ID</th>
                            <th class="min-w-48 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">商品名</th>
                            <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">画像</th>
                            <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">在庫数</th>
                            <!-- <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100"></th> -->
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="notification in lowStockNotifications" :key="notification.id" class="border-t-2 border-gray-100">
                              <td class="min-w-16 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
                                {{ notification.relative_time }}
                              </td>
                              <td class="min-w-32 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                <Link :href="route('items.show', { item: notification.data.id })">
                                <span class="text-blue-600 title-font font-medium text-xs md:text-base">
                                  {{ notification.data.management_id }}
                                </span>
                              </Link>
                              </td>
                              <td class="min-w-32 md:min-w-44 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                {{ notification.data.item_name }}
                              </td>
                              <td class="min-w-32 md:min-w-24 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                <Link :href="route('items.show', { item: notification.data.id })" class="flex justify-center">
                                  <img :src="notification.data.image_path1" alt="画像" class="h-8">
                                </Link>
                              </td>
                              <td class=" min-w-32 md:min-w-20 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                {{ notification.data.quantity }} / {{ notification.data.minimum_stock }}
                              </td>
                              <!-- <td class="border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                <Link :href="route('consumable_items', { item_id: notification.data.id })">
                                <div class="text-blue-600 title-font text-xs md:text-base font-medium">詳細を見る</div>
                              </Link>
                              </td> -->
                            </tr>
                        </tbody>
                      </table>
                      <div v-else class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <div class="ml-2 text-center py-4">通知はありません</div>
                      </div>
                    </div>

                    <div v-if="activeTab === 'tab2'">
                      <div class="flex justify-around">
                        <div class="mb-4 px-4 font-medium">毎朝6時更新</div>
                        <Link :href="route('inspection_and_disposal_items')" class="mb-4 px-4 text-blue-600 title-font font-medium underline">
                          点検と廃棄へ
                        </Link>
                      </div>
                      <div class="min-w-full overflow-auto">
                        <table v-if="disposalAndInspectionNotifications" class="table-fixed min-w-full text-left whitespace-no-wrap">
                          <thead>
                            <tr>
                              <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
                              <th class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">管理ID</th>
                              <th class="min-w-48 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">商品名</th>
                              <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">画像</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="notification in disposalAndInspectionNotifications" :key="notification.id" class="border-t-2 border-gray-100">
                                <td class="min-w-16 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
                                  {{ notification.relative_time }}
                                </td>
                                <td class="min-w-32 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                  <span class="text-blue-600 title-font font-medium text-xs md:text-base">
                                    {{ notification.data.management_id }}
                                  </span>
                                </Link>
                                </td>
                                <td class="min-w-32 md:min-w-44 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  {{ notification.data.item_name }}
                                </td>
                                <td class="min-w-32 md:min-w-24 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <Link :href="route('items.show', { item: notification.data.id })" class="flex justify-center">
                                    <img :src="notification.data.image_path1" alt="画像" class="h-8">
                                  </Link>
                                </td>
                              </tr>
                          </tbody>
                        </table>
                        <div v-else class="flex items-center justify-center">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                          <div class="ml-2 text-center py-4">通知はありません</div>
                        </div>
                      </div>
                    </div>
                    
                    <div v-if="activeTab === 'tab3'">
                      <div class="flex justify-around">
                        <div class="mb-4 px-4 font-medium">備品のリクエストが追加されました</div>
                        <Link :href="route('item_requests.index')" class="mb-4 px-4 text-blue-600 title-font font-medium underline">
                          リクエスト一覧へ
                        </Link>
                      </div>
                      <div>{{ requestedItemNotifications }}</div>
                      <div class="min-w-full overflow-auto">
                        <table v-if="requestedItemNotifications" class="table-fixed min-w-full text-left whitespace-no-wrap">
                          <thead>
                            <tr>
                              <th class="min-w-32 md:min-w-26 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">通知</th>
                              <th class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">管理ID</th>
                              <th class="min-w-48 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">商品名</th>
                              <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-gray-900 text-xs md:text-base bg-gray-100">画像</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="notification in requestedItemNotifications" :key="notification.id" class="border-t-2 border-gray-100">
                                <td class="min-w-16 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <span v-if="!notification.read_at" class="text-orange-500 text-xs">●</span>
                                  {{ notification.relative_time }}
                                </td>
                                <td class="min-w-32 md:min-w-28 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <Link :href="route('items.show', { item: notification.data.id })">
                                  <span class="text-blue-600 title-font font-medium text-xs md:text-base">
                                    {{ notification.data.management_id }}
                                  </span>
                                </Link>
                                </td>
                                <td class="min-w-32 md:min-w-44 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  {{ notification.data.item_name }}
                                </td>
                                <td class="min-w-32 md:min-w-24 border-b-2 border-gray-100 px-4 py-3 text-center text-xs md:text-base">
                                  <Link :href="route('items.show', { item: notification.data.id })" class="flex justify-center">
                                    <img :src="notification.data.image_path1" alt="画像" class="h-8">
                                  </Link>
                                </td>
                              </tr>
                          </tbody>
                        </table>
                        <div v-else class="flex items-center justify-center">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                          <div class="ml-2 text-center py-4">通知はありません</div>
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
