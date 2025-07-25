<script setup lang="ts">
import { ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';
import { InformationCircleIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">通知</h2>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-center">
              <ul class="flex space-x-2 md:space-x-6 max-w-md">
                <li class="flex-grow">
                  <a
                    :class="[
                      'block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg',
                      activeTab === 'schedule'
                        ? 'bg-blue-500 text-white'
                        : 'bg-white text-black border',
                    ]"
                    @click="activeTab = 'schedule'"
                  >
                    予定
                  </a>
                </li>
                <li class="flex-grow">
                  <a
                    :class="[
                      'block text-center px-4 py-2 rounded-full font-bold text-xs md:text-md lg:text-lg',
                      activeTab === 'history'
                        ? 'bg-blue-500 text-white'
                        : 'bg-white text-black border',
                    ]"
                    @click="activeTab = 'history'"
                  >
                    履歴
                  </a>
                </li>
              </ul>
            </div>
            <!-- テーブル -->
            <div v-if="activeTab === 'schedule'">
              <div class="mt-4">
                <div class="flex items-center px-4 font-medium">
                  <ClipboardDocumentCheckIcon class="size-5" />
                  <span class="text-sm md:text-base lg:text-lg">点検予定日</span>
                  <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                </div>
                <div class="mt-4 min-w-full overflow-auto">
                  <table
                    v-if="scheduledInspections.data && scheduledInspections.data.length > 0"
                    class="table-fixed min-w-full text-left whitespace-no-wrap"
                  >
                    <thead>
                      <tr>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          管理ID
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          予定日
                        </th>
                        <th
                          class="min-w-32 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          備品名
                        </th>
                        <th
                          class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          画像
                        </th>
                        <th
                          class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          カテゴリ
                        </th>
                        <th
                          class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          在庫数
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用状況
                        </th>
                        <th
                          class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          使用者
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用場所
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          保管場所
                        </th>
                        <th
                          class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          登録日
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得年月日
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="inspection in scheduledInspections.data" :key="inspection.id">
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <Link
                            class="text-blue-400"
                            :href="route('items.show', { item: inspection.item.id })"
                          >
                            {{ inspection.item.management_id }}
                          </Link>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.inspection_scheduled_date }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"
                        >
                          <img :src="inspection.item.image_path1" alt="画像" class="" />
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.category.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <span>{{ inspection.item.stock }}{{ inspection.item.unit.name }}</span>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.usage_status.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.end_user }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.location_of_use.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.storage_location.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.created_at }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.date_of_acquisition }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else>
                    <div class="flex items-center justify-center">
                      <InformationCircleIcon class="w-6 h-6 text-black" />
                      <div class="ml-2 text-center py-4">予定が見つかりません</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-8">
                <div class="px-4 font-medium flex items-center">
                  <TrashIcon class="size-5" />
                  <span class="text-sm md:text-base lg:text-lg">廃棄予定日</span>
                  <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                </div>
                <div class="mt-4 min-w-full overflow-auto">
                  <table
                    v-if="scheduledDisposals.data && scheduledDisposals.data.length > 0"
                    class="table-fixed min-w-full text-left whitespace-no-wrap"
                  >
                    <thead>
                      <tr>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          管理ID
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          予定日
                        </th>
                        <th
                          class="min-w-32 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          備品名
                        </th>
                        <th
                          class="min-w-24 md:min-w-28 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          画像
                        </th>
                        <th
                          class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          カテゴリ
                        </th>
                        <th
                          class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          在庫数
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用状況
                        </th>
                        <th
                          class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          使用者
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用場所
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          保管場所
                        </th>
                        <th
                          class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          登録日
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得年月日
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="disposal in scheduledDisposals.data" :key="disposal.id" class="">
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <Link
                            class="text-blue-400"
                            :href="route('items.show', { item: disposal.item.id })"
                          >
                            {{ disposal.item.management_id }}
                          </Link>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.disposal_scheduled_date }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"
                        >
                          <img :src="disposal.item.image_path1" alt="画像" class="" />
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.category.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <span>{{ disposal.item.stock }}{{ disposal.item.unit.name }}</span>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.usage_status.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.end_user }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.location_of_use.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.storage_location.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.created_at }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.date_of_acquisition }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else>
                    <div class="flex items-center justify-center">
                      <InformationCircleIcon class="w-6 h-6 text-black" />
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
                  <ClipboardDocumentCheckIcon class="size-5" />
                  <span class="text-sm md:text-base lg:text-lg">点検履歴</span>
                  <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                </div>
                <div class="mt-4 min-w-full overflow-auto">
                  <table
                    v-if="historyInspections.data && historyInspections.data.length > 0"
                    class="table-fixed min-w-full text-left whitespace-no-wrap"
                  >
                    <thead>
                      <tr>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          管理ID
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          予定日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          実施日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          実施者
                        </th>
                        <th
                          class="min-w-60 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          補足情報
                        </th>
                        <th
                          class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          備品名
                        </th>
                        <th
                          class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          画像
                        </th>
                        <th
                          class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          カテゴリ
                        </th>
                        <th
                          class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          在庫数
                        </th>
                        <th
                          class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用状況
                        </th>
                        <th
                          class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          使用者
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用場所
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          保管場所
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          登録日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得年月日
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="inspection in historyInspections.data"
                        :key="inspection.id"
                        class=""
                      >
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <Link
                            class="text-blue-400"
                            :href="route('items.show', { item: inspection.item.id })"
                          >
                            {{ inspection.item.management_id }}
                          </Link>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.scheduled_date ?? 'なし' }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.inspection_date }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.inspection_person }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.details }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"
                        >
                          <img :src="inspection.item.image_path1" alt="画像" class="" />
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.category.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <span>{{ inspection.item.stock }}{{ inspection.item.unit.name }}</span>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.usage_status.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.end_user }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.location_of_use.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.storage_location.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.created_at }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ inspection.item.date_of_acquisition }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else>
                    <div class="flex items-center justify-center">
                      <InformationCircleIcon class="w-6 h-6 text-black" />
                      <div class="ml-2 text-center py-4">履歴が見つかりません</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6">
                <div class="px-4 font-medium flex items-center">
                  <TrashIcon class="size-5" />
                  <span class="text-sm md:text-base lg:text-lg">廃棄履歴</span>
                  <span class="ml-2 text-xs text-gray-500">直近10件まで</span>
                </div>
                <div class="mt-4 min-w-full overflow-auto">
                  <table
                    v-if="historyDisposals.data && historyDisposals.data.length > 0"
                    class="table-fixed min-w-full text-left whitespace-no-wrap"
                  >
                    <thead>
                      <tr>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          管理ID
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          予定日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          実施日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          実施者
                        </th>
                        <th
                          class="min-w-60 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          補足情報
                        </th>
                        <th
                          class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          備品名
                        </th>
                        <th
                          class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          画像
                        </th>
                        <th
                          class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          カテゴリ
                        </th>
                        <th
                          class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          在庫数
                        </th>
                        <th
                          class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用状況
                        </th>
                        <th
                          class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          使用者
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          利用場所
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          保管場所
                        </th>
                        <th
                          class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          登録日
                        </th>
                        <th
                          class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得年月日
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="disposal in historyDisposals.data" :key="disposal.id" class="">
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <Link
                            class="text-blue-400"
                            :href="route('items.show', { item: disposal.item.id })"
                          >
                            {{ disposal.item.management_id }}
                          </Link>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.scheduled_date ?? 'なし' }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.disposal_date }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.disposal_person }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.details }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base max-w-full h-auto"
                        >
                          <img :src="disposal.item.image_path1" alt="画像" class="" />
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.category.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          <span>{{ disposal.item.stock }}{{ disposal.item.unit.name }}</span>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.usage_status.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.end_user }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.location_of_use.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.storage_location.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.created_at }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-3 text-center text-xs md:text-base"
                        >
                          {{ disposal.item.date_of_acquisition }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else>
                    <div class="flex items-center justify-center">
                      <InformationCircleIcon class="w-6 h-6 text-black" />
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
