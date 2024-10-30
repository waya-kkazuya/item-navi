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

const activeTab = ref(sessionStorage.getItem('activeTab') ?? 'consumableItems')

watch(activeTab, (newValue) => {
  sessionStorage.setItem('activeTab', newValue)
})

onMounted(() => {
  if (sessionStorage.getItem('activeTab') === null) {
    activeTab.value = 'consumableItems'
    sessionStorage.setItem('activeTab', 'consumableItems')
  }
})
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
                      <a @click="activeTab = 'consumableItems'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'consumableItems' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        消耗品在庫数
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a @click="activeTab = 'inspectionAndDisposalSchedule'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'inspectionAndDisposalSchedule' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        点検・廃棄予定
                      </a>
                    </li>
                    <li class="flex-grow">
                      <a @click="activeTab = 'request'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'request' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                        リクエスト
                      </a>
                    </li>
                  </ul>
                </div>
                <LowStockNotificationsTab v-if="activeTab === 'consumableItems'" :lowStockNotifications="lowStockNotifications" />
                <DisposalAndInspectionNotificationsTab v-if="activeTab === 'inspectionAndDisposalSchedule'" :disposalAndInspectionNotifications="disposalAndInspectionNotifications" />
                <RequestedItemNotificationsTab v-if="activeTab === 'request'" :requestedItemNotifications="requestedItemNotifications" />
              </div>
            </div>
          </div>
        </div>
    </AuthenticatedLayout>
</template>
