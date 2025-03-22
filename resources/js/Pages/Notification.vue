<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InspectionAndDisposalNotificationsTab from '@/Components/InspectionAndDisposalNotificationsTab.vue';
import LowStockNotificationsTab from '@/Components/LowStockNotificationsTab.vue';
import RequestedItemNotificationsTab from '@/Components/RequestedItemNotificationsTab.vue';

import type { Ref } from 'vue';
import type { NotificationType } from '@/@types/model';

type Props = {
  notifications: NotificationType[];
  lowStockNotifications: NotificationType[];
  inspectionAndDisposalNotifications: NotificationType[];
  requestedItemNotifications: NotificationType[];
  unreadLowStockNotifications: number;
  unreadInspectionAndDisposalNotifications: number;
  unreadRequestedItemNotifications: number;
}

const props = defineProps<Props>();

const activeTab: Ref<string> = ref(sessionStorage.getItem('activeTab') ?? 'consumableItems');

watch(activeTab, (newValue: string) => {
  sessionStorage.setItem('activeTab', newValue);
});

onMounted(() => {
  if (sessionStorage.getItem('activeTab') === null) {
    activeTab.value = 'consumableItems';
    sessionStorage.setItem('activeTab', 'consumableItems');
  }
});
</script>

<template>
  <Head title="Notification" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        通知
      </h2>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-center">
              <ul class="flex space-x-2 md:space-x-6 max-w-md">
                <li class="relative flex-grow">
                  <a @click="activeTab = 'consumableItems'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'consumableItems' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                    消耗品在庫数
                    <span v-if="unreadLowStockNotifications > 0" class="absolute top-0 right-0 text-orange-500 bg-orange-500 rounded-full w-2 h-2 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </a>
                </li>
                <li class="relative flex-grow">
                  <a @click="activeTab = 'inspectionAndDisposalSchedule'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'inspectionAndDisposalSchedule' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                    点検・廃棄予定
                    <span v-if="unreadInspectionAndDisposalNotifications > 0" class="absolute top-0 right-0 text-orange-500 bg-orange-500 rounded-full w-2 h-2 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </a>
                </li>
                <li class="relative flex-grow">
                  <a @click="activeTab = 'request'" :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'request' ? 'bg-blue-500 text-white' : 'bg-white text-black border']">
                    リクエスト
                    <span v-if="unreadRequestedItemNotifications > 0" class="absolute top-0 right-0 text-orange-500 bg-orange-500 rounded-full w-2 h-2 flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </a>
                </li>
              </ul>
            </div>
            <LowStockNotificationsTab v-if="activeTab === 'consumableItems'" :lowStockNotifications="lowStockNotifications" />
            <InspectionAndDisposalNotificationsTab v-if="activeTab === 'inspectionAndDisposalSchedule'" :inspectionAndDisposalNotifications="inspectionAndDisposalNotifications" />
            <RequestedItemNotificationsTab v-if="activeTab === 'request'" :requestedItemNotifications="requestedItemNotifications" />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
