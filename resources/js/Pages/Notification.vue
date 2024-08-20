<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
  notifications: Array
})



// const notifications = ref([])

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
                        You're logged in!
                        <div>{{ notifications.length }}</div>
                        <div v-if="notifications.length">
                          <ul>
                            <li v-for="notification in notifications" :key="notification.id">
                              {{ notification.data.item_name }}
                              {{ notification.data.quantity }}
                              {{ notification.data.message }}
                            </li>
                          </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
