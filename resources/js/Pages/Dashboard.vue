<script setup lang="ts">
import {
  MinusCircleIcon,
  PlusCircleIcon,
  ChevronRightIcon,
  CalendarIcon,
  ClockIcon,
  InformationCircleIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import type { Ref } from 'vue';
import type { ItemType, EditHistoryType } from '@/@types/model';

type CategoryGroupedItems = {
  category_id: number;
  items: ItemType[];
};

type LocationGroupedItems = {
  location_of_use_id: number;
  items: ItemType[];
};

type ItemsByType = Record<string, CategoryGroupedItems | LocationGroupedItems>;

type GroupedEditHistoriesType = Record<string, EditHistoryType[]>;

type Props = {
  allItems: ItemType[];
  itemsByType: ItemsByType;
  groupedEdithistories: GroupedEditHistoriesType;
  type: string;
};

const props = defineProps<Props>();

// 登録件数をプルダウンで切り替え
const type: Ref<string> = ref(props.type ?? 'category');

const switchViewMode = (): void => {
  router.visit(
    route('dashboard', {
      type: type.value,
    }),
    {
      method: 'get',
    }
  );
};

// groupedEdithistoriesをローカル用にコピー
const localGroupedEdithistories: Ref<GroupedEditHistoriesType> = ref({});

onMounted(() => {
  Object.keys(props.groupedEdithistories).forEach((date) => {
    localGroupedEdithistories.value[date] = props.groupedEdithistories[date].map((history) => ({
      ...history,
      showDetails: false, // 初期状態では非表示
    }));
  });
});

// 編集履歴のアコーディオンパネルの開閉を切り替える関数
const toggleDetails = (history: EditHistoryType) => {
  history.showDetails = !history.showDetails;
};
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ダッシュボード</h2>
      </div>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="md:flex w-full p-4">
            <div class="md:w-2/5 md:border-r border-gray-300 p-4">
              <!-- 左側のテーブル -->
              <table class="w-full border border-gray-200 text-sm">
                <thead>
                  <tr>
                    <th
                      class="w-2/3 border-b-2 border-gray-200 px-4 py-3 text-left text-white bg-sky-700"
                    >
                      備品の登録件数
                    </th>
                    <th class="w-1/3 border-b-2 border-gray-200 px-4 py-3 text-right bg-sky-700">
                      <select v-model="type" @change="switchViewMode" class="h-9 text-sm">
                        <option value="category">カテゴリ</option>
                        <option value="locationOfUse">使用場所</option>
                      </select>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="type == 'category'">
                    <tr>
                      <td class="border-b-2 border-gray-200 px-4 py-3">
                        <span class="text-sm font-medium text-gray-700">全体</span>
                      </td>
                      <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                        <Link
                          :href="route('items.index')"
                          class="inline-flex items-center justify-center gap-2 text-blue-600 hover:text-blue-700 transition-colors group"
                        >
                          <span class="text-lg font-bold text-blue-600 hover:text-blue-700">{{
                            allItems.length
                          }}</span>
                          <span class="text-sm text-gray-500">件</span>
                          <ChevronRightIcon
                            class="size-4 text-gray-400 group-hover:text-gray-600"
                          />
                        </Link>
                      </td>
                    </tr>
                    <tr v-for="(group, categoryName) in itemsByType" :key="categoryName">
                      <td class="border-b-2 border-gray-200 px-4 py-3">
                        {{ categoryName }}
                      </td>
                      <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                        <Link
                          v-if="'category_id' in group"
                          :href="route('items.index', { categoryId: group.category_id })"
                          class="inline-flex items-center justify-center gap-2 text-blue-600 hover:text-blue-700 transition-colors group"
                        >
                          <span class="text-base font-semibold">{{ group.items.length }}</span>
                          <span class="text-sm text-gray-500">件</span>
                          <ChevronRightIcon
                            class="size-4 text-gray-400 group-hover:text-gray-600"
                          />
                        </Link>
                      </td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr>
                      <td class="border-b-2 border-gray-200 px-4 py-3">
                        <span class="text-sm font-medium text-gray-700">全体</span>
                      </td>
                      <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                        <Link
                          :href="route('items.index')"
                          class="inline-flex items-center justify-center gap-2 text-blue-600 hover:text-blue-700 transition-colors group"
                        >
                          <span class="text-lg font-bold text-blue-600 hover:text-blue-700">{{
                            allItems.length
                          }}</span>
                          <span class="text-sm text-gray-500">件</span>
                          <ChevronRightIcon
                            class="size-4 text-gray-400 group-hover:text-gray-600"
                          />
                        </Link>
                      </td>
                    </tr>
                    <tr v-for="(group, locationOfUseName) in itemsByType" :key="locationOfUseName">
                      <td class="border-b-2 border-gray-200 px-4 py-3">
                        <span class="text-sm text-gray-700">{{ locationOfUseName }}</span>
                      </td>
                      <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                        <Link
                          v-if="'location_of_use_id' in group"
                          :href="
                            route('items.index', { locationOfUseId: group.location_of_use_id })
                          "
                          class="inline-flex items-center justify-center gap-2 text-blue-600 hover:text-blue-700 transition-colors group"
                        >
                          <span class="text-base font-semibold text-blue-600 hover:text-blue-700">{{
                            group.items.length
                          }}</span>
                          <span class="text-sm text-gray-500">件</span>
                          <ChevronRightIcon
                            class="size-4 text-gray-400 group-hover:text-gray-600"
                          />
                        </Link>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <!-- 右側のテーブル -->
            <div class="w-full md:w-3/5 p-4">
              <table class="w-full border border-gray-200 text-sm">
                <thead>
                  <tr>
                    <th
                      class="border-b-2 border-gray-200 px-4 py-3 text-left text-white bg-sky-700"
                      colspan="2"
                    >
                      備品の編集履歴
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(histories, date) in localGroupedEdithistories" :key="date">
                    <tr>
                      <td class="border-b-2 p-2 border-gray-200 text-left bg-gray-200" colspan="2">
                        <div class="flex items-center gap-2">
                          <CalendarIcon class="size-4 text-gray-500" />
                          <span>{{ date }} ({{ histories[0].day_of_week }})</span>
                        </div>
                      </td>
                    </tr>
                    <template v-for="history in histories" :key="history.id">
                      <tr>
                        <td class="border-b-2 p-2 align-top">
                          <div class="flex items-center gap-1">
                            <ClockIcon class="size-3" />
                            <span>{{ history.time }}</span>
                          </div>
                        </td>
                        <td class="border-b-2 p-2">
                          <div class="flex items-center">
                            <button
                              v-if="
                                history.operation_type == 'update' && history.edit_reason.reason
                              "
                              @click="toggleDetails(history)"
                              class="text-sm text-gray-500 ml-2"
                            >
                              <MinusCircleIcon v-if="history.showDetails" class="size-5" />
                              <PlusCircleIcon v-else class="size-5" />
                            </button>
                            <div>
                              <span class="">{{ history.edit_user }}</span
                              >が
                              <template v-if="history.operation_type === 'soft_delete'">
                                <span class="underline"
                                  >【{{ history.item.management_id }}】{{ history.item.name }}</span
                                >
                              </template>
                              <template v-else>
                                <Link
                                  as="button"
                                  :href="
                                    route('items.show', {
                                      item: history.item_id,
                                    })
                                  "
                                  class="text-blue-500 hover:text-blue-700 underline"
                                >
                                  【{{ history.item.management_id }}】{{ history.item.name }}
                                </Link>
                              </template>
                              <span v-if="history.operation_type == 'update'"
                                >の{{ history.edited_field_for_display }}</span
                              >
                              {{ history.operation_description }}
                            </div>
                          </div>
                          <!-- アコーディオンパネル部分 -->
                          <div
                            v-if="
                              history.showDetails &&
                              history.operation_type == 'update' &&
                              history.edit_reason.reason
                            "
                            class="mt-3 bg-blue-50 rounded-lg p-3 space-y-2 border border-blue-100"
                          >
                            <div class="flex items-start gap-2">
                              <InformationCircleIcon
                                class="size-4 text-blue-600 flex-shrink-0 mt-0.5"
                              />
                              <div class="text-sm">
                                <span class="font-semibold text-blue-900">編集理由:</span>
                                <span class="text-blue-800 ml-1">{{
                                  history.edit_reason.reason
                                }}</span>
                                <span v-if="history.edit_reason_text" class="text-blue-700 ml-2">{{
                                  history.edit_reason_text
                                }}</span>
                              </div>
                            </div>

                            <div class="flex items-start gap-2">
                              <ArrowPathIcon class="size-4 text-gray-600 flex-shrink-0 mt-0.5" />
                              <div class="text-sm">
                                <div class="text-gray-700">
                                  <span class="font-semibold">変更後:</span>
                                  <span class="ml-1">{{ history.new_value }}</span>
                                </div>
                                <div class="text-gray-500 mt-1">
                                  <span class="font-semibold">変更前:</span>
                                  <span class="ml-1">{{ history.old_value }}</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.arrow-up {
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 5px solid #eef2ff; /* Tailwind's indigo-50 */
  position: absolute;
  top: -5px;
  left: 10px; /* Adjust as needed */
}
</style>
