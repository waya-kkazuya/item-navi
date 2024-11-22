<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Ref } from 'vue';
import type { InspectionType, DisposalType } from '@/@types/model';


defineProps<{
  scheduledInspections: InspectionType[];
  scheduledDisposals: DisposalType[];
  historyInspections: InspectionType[];
  historyDisposals: DisposalType[];
}>();

const activeTab: Ref<string> = ref('schedule');
</script>

<template>
    <Head title="Notification" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              通知
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <div class="flex justify-center">
                        <ul class="flex space-x-2 md:space-x-6 max-w-md">
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'schedule' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'schedule'">
                              予定
                            </a>
                          </li>
                          <li class="flex-grow">
                            <a :class="['block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg', activeTab === 'history' ? 'bg-blue-500 text-white' : 'bg-white text-black border']" @click="activeTab = 'history'">
                              履歴
                            </a>
                          </li>
                        </ul>
                      </div>
                      <!-- テーブル -->
                      <div v-if="activeTab === 'schedule'">
                        <div class="mt-4">
                          <div class="flex items-center px-4 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                            </svg>
                            <span class="text-sm md:text-base lg:text-lg ">点検予定日</span>
                            <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                          </div>
                          <div class="mt-4 min-w-full overflow-auto">
                            <table v-if="scheduledInspections.data && scheduledInspections.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                              <thead>
                                <tr>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">管理ID</th>
                                  <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">予定日</th>
                                  <th class="min-w-32 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備品名</th>
                                  <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">画像</th>
                                  <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">カテゴリ</th>
                                  <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">在庫数</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用状況</th>
                                  <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">使用者</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用場所</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">保管場所</th>
                                  <th class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">登録日</th>
                                  <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得年月日</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="inspection in scheduledInspections.data" :key="inspection.id">
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">
                                    <Link class="text-blue-400" :href="route('items.show', { item: inspection.item.id })">
                                      {{ inspection.item.management_id }}
                                    </Link>
                                  </td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.inspection_scheduled_date }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"><img :src="inspection.item.image_path1" alt="画像" class=""></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.category.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"><span>{{ inspection.item.stock }}{{ inspection.item.unit.name }}</span></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.usage_status.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.end_user }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.location_of_use.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.storage_location.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.created_at }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.date_of_acquisition }}</td>
                                </tr>
                              </tbody>
                            </table>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div class="ml-2 text-center py-4">予定が見つかりません</div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="mt-8">
                          <div class="px-4 font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span class="text-sm md:text-base lg:text-lg">廃棄予定日</span>
                            <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                          </div>
                          <div class="mt-4 min-w-full overflow-auto">
                            <table v-if="scheduledDisposals.data && scheduledDisposals.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                              <thead>
                                <tr>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">管理ID</th>
                                  <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">予定日</th>
                                  <th class="min-w-32 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備品名</th>
                                  <th class="min-w-24 md:min-w-28 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">画像</th>
                                  <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">カテゴリ</th>
                                  <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">在庫数</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用状況</th>
                                  <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">使用者</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用場所</th>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">保管場所</th>
                                  <th class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">登録日</th>
                                  <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得年月日</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="disposal in scheduledDisposals.data" :key="disposal.id" class="">
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">
                                    <Link class="text-blue-400" :href="route('items.show', { item: disposal.item.id })">
                                      {{ disposal.item.management_id }}
                                    </Link>
                                  </td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.disposal_scheduled_date }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"><img :src="disposal.item.image_path1" alt="画像" class=""></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.category.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"><span>{{ disposal.item.stock }}{{ disposal.item.unit.name }}</span></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.usage_status.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.end_user }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.location_of_use.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.storage_location.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.created_at }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.date_of_acquisition }}</td>
                                </tr>
                              </tbody>
                            </table>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div class="ml-2 text-center py-4">予定が見つかりません</div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    
                      <!-- ここから履歴 -->
                      <div v-if="activeTab === 'history'">
                        <div class="mt-4">
                          <div class="flex items-center px-4 font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                            </svg>
                            <span class="text-sm md:text-base lg:text-lg">点検履歴</span>
                            <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                          </div>
                          <div class="mt-4 min-w-full overflow-auto">
                            <table v-if="historyInspections.data && historyInspections.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                              <thead>
                                <tr>
                                  <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">管理ID</th>
                                  <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">予定日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">実施日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">実施者</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">補足情報</th>
                                  <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備品名</th>
                                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">画像</th>
                                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">カテゴリ</th>
                                  <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">在庫数</th>
                                  <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用状況</th>
                                  <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">使用者</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用場所</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">保管場所</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">登録日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得年月日</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="inspection in historyInspections.data" :key="inspection.id" class="">
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">
                                    <Link class="text-blue-400" :href="route('items.show', { item: inspection.item.id })">
                                      {{ inspection.item.management_id }}
                                    </Link>
                                  </td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.scheduled_date ?? 'なし'}}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.inspection_date }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.inspection_person }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.details }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"><img :src="inspection.item.image_path1" alt="画像" class=""></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.category.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"><span>{{ inspection.item.stock }}{{ inspection.item.unit.name }}</span></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.usage_status.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.end_user }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.location_of_use.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.storage_location.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.created_at }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ inspection.item.date_of_acquisition }}</td>
                                </tr>
                              </tbody>
                            </table>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div class="ml-2 text-center py-4">履歴が見つかりません</div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="mt-6">
                          <div class="px-4 font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span class="text-sm md:text-base lg:text-lg">廃棄履歴</span>
                            <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                          </div>
                          <div class="mt-4 min-w-full overflow-auto">
                            <table v-if="historyDisposals.data && historyDisposals.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                              <thead>
                                <tr>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">管理ID</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">予定日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">実施日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">実施者</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">補足情報</th>
                                  <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備品名</th>
                                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">画像</th>
                                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">カテゴリ</th>
                                  <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">在庫数</th>
                                  <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用状況</th>
                                  <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">使用者</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用場所</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">保管場所</th>
                                  <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">登録日</th>
                                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得年月日</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="disposal in historyDisposals.data" :key="disposal.id" class="">
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">
                                    <Link class="text-blue-400" :href="route('items.show', { item: disposal.item.id })">
                                      {{ disposal.item.management_id }}
                                    </Link>
                                  </td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.scheduled_date　?? 'なし' }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.disposal_date }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.disposal_person}}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.details }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"><img :src="disposal.item.image_path1" alt="画像" class=""></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.category.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"><span>{{ disposal.item.stock }}{{ disposal.item.unit.name }}</span></td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.usage_status.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.end_user }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.location_of_use.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.storage_location.name }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.created_at }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base">{{ disposal.item.date_of_acquisition }}</td>
                                </tr>
                              </tbody>
                            </table>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div class="ml-2 text-center py-4">履歴が見つかりません</div>
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