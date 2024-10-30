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

watch(activeTab, (newValue) => {
  sessionStorage.setItem('activeTab', newValue)
})

onMounted(() => {
  if (sessionStorage.getItem('activeTab') === null) {
    activeTab.value = 'tab1'
    sessionStorage.setItem('activeTab', 'tab1')
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
                <LowStockNotificationsTab v-if="activeTab === 'tab1'" :lowStockNotifications="lowStockNotifications" />
                <DisposalAndInspectionNotificationsTab v-if="activeTab === 'tab2'" :disposalAndInspectionNotifications="disposalAndInspectionNotifications" />
                <RequestedItemNotificationsTab v-if="activeTab === 'tab3'" :requestedItemNotifications="requestedItemNotifications" />
              </div>
            </div>
          </div>
        </div>
    </AuthenticatedLayout>
</template>
