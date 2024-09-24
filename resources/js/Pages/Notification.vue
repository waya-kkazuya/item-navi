<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import LowStockNotificationsTab from '@/Components/LowStockNotificationsTab.vue';
import DisposalAndInspectionNotificationsTab from '@/Components/DisposalAndInspectionNotificationsTab.vue';
import RequestedItemNotificationsTab from '@/Components/RequestedItemNotificationsTab.vue';

const props = defineProps({
  notifications: Object,
  lowStockNotifications: Object,
  disposalAndInspectionNotifications: Object,
  requestedItemNotifications: Object,
})


const activeTab = ref(sessionStorage.getItem('activeTab') ?? 'tab1')

// watchでisTableViewを監視している
watch(activeTab, (newValue) => {
  sessionStorage.setItem('activeTab', newValue)
})

onMounted(() => {
  if (sessionStorage.getItem('activeTab') === null) {
    activeTab.value = 'tab1'
    sessionStorage.setItem('activeTab', 'tab1')
  }
})

// const localNotifications = ref([...props.notifications]);
// // プロパティが変更された場合にローカル変数を更新
// // 自動では反映してくれないところが注意点
// watch(() => props.notifications, (newNotifications) => {
//   localNotifications.value = [...newNotifications]
// });

// onMounted(() => {
//   // console.log('props.notifications')
//   // console.log(props.notifications)

//   localNotifications.value.forEach(notification => {
//     if (!notification.read_at) {
//       markAsRead(notification.id)
//     }
//   })
// })


// // 画面を見たら既読にする処理、次回アクセスもしくは更新でオレンジの新着マークが消える
// const markAsRead = async id => {
//   console.log('notification_id')
//   console.log(id)
//   try {
//     await axios.patch(`/api/notifications/${id}/read`)
//     // 通知を既読にした後、Vue側ローカル側の状態を同期する（オレンジ●が消える）　idは引数のid
//     // const notification = localNotifications.value.find(n => n.id === id)
//     // if (notification) {
//     //   notification.read_at = new Date().toISOString() // 日付を入れることで既読にする
//     // }
//   } catch (error) {
//     console.error('APIでの既読処理が失敗しました', error)
//   }
// }

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
                      <a @click="activeTab = 'tab1'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab1' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        消耗品在庫数
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a @click="activeTab = 'tab2'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab2' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        点検・廃棄予定
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a @click="activeTab = 'tab3'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'tab3' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        リクエスト
                      </a>
                    </li>
                  </ul>
                </div>
                <!-- <div class="mt-4 flex justify-center"> -->
                  <LowStockNotificationsTab v-if="activeTab === 'tab1'" :lowStockNotifications="lowStockNotifications" />
                  <DisposalAndInspectionNotificationsTab v-if="activeTab === 'tab2'" :disposalAndInspectionNotifications="disposalAndInspectionNotifications" />
                  <RequestedItemNotificationsTab v-if="activeTab === 'tab3'" :requestedItemNotifications="requestedItemNotifications" />
                <!-- </div> -->
              </div>
            </div>
          </div>
        </div>
    </AuthenticatedLayout>
</template>
