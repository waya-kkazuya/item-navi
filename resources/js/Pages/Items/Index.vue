<script setup lang="ts">
import {
  ArrowUpIcon,
  ArrowDownIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  Bars3Icon,
  Squares2X2Icon,
  ArrowLeftStartOnRectangleIcon,
  ArrowRightIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EditHistoryModal from '@/Components/EditHistoryModal.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';

import type { Ref } from 'vue';
import type { Paginator } from '@/@types/types';
import type { ItemType, CategoryType, LocationType } from '@/@types/model';

type Props = {
  items: Paginator<ItemType>;
  categories: CategoryType[];
  locations: LocationType[];
  search: string;
  sortOrder: string;
  categoryId: number;
  locationOfUseId: number;
  storageLocationId: number;
  totalCount: number;
  startNumber: number;
  endNumber: number;
  disposal: boolean;
};

const props = defineProps<Props>();

// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localItems: Ref<Paginator<ItemType>> = ref({ ...props.items });
const search: Ref<string> = ref(props.search); // 検索フォーム
const sortOrder: Ref<string> = ref(props.sortOrder ?? 'asc'); // 作成日でソート
const categoryId: Ref<number> = ref(props.categoryId ?? 0); // カテゴリプルダウン(初期値は0)
const locationOfUseId: Ref<number> = ref(props.locationOfUseId ?? 0); // 利用場所プルダウン(初期値は0)
const storageLocationId: Ref<number> = ref(props.storageLocationId ?? 0); // 保管場所プルダウン(初期値は0)
const totalCount: Ref<number> = ref(props.totalCount); // 備品の合計件数
const startNumber: Ref<number> = ref(props.startNumber);
const endNumber: Ref<number> = ref(props.endNumber);
const isDisposal: Ref<boolean> = ref(false); // 廃棄済みの備品との状態切り替え
const isTableView: Ref<boolean> = ref(sessionStorage.getItem('isTableView') !== 'false'); // 行表示・タイル表示の切替 セッションにisTableViewを保存
// watchでisTableViewを監視
watch(isTableView, (newValue: boolean) => {
  sessionStorage.setItem('isTableView', newValue.toString());
});

onMounted(() => {
  if (sessionStorage.getItem('isTableView') === null) {
    isTableView.value = true;
    sessionStorage.setItem('isTableView', 'true');
  }
  isDisposal.value = props.disposal === 'true';
});

// プルダウンや検索フォームの条件を適用して備品情報を再取得
const fetchAndFilterItems = (): void => {
  router.visit(
    route('items.index', {
      search: search.value,
      sortOrder: sortOrder.value,
      categoryId: categoryId.value,
      locationOfUseId: locationOfUseId.value,
      storageLocationId: storageLocationId.value,
      disposal: isDisposal.value ? 'true' : 'false', //パラメータを文字列にしてコントローラーに渡す
    }),
    {
      method: 'get',
    }
  );
};

// プルダウンや検索フォームをすべてリセット
const clearState = (): void => {
  search.value = '';
  sortOrder.value = 'asc';
  categoryId.value = 0;
  locationOfUseId.value = 0;
  storageLocationId.value = 0;
  fetchAndFilterItems();
};

const toggleSortOrder = (): void => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  fetchAndFilterItems();
};

// 備品と廃棄済み備品の表示切り替え
const toggleItems = (): void => {
  isDisposal.value = !isDisposal.value;
  fetchAndFilterItems();
};

// 備品の復元
const restoreItem = async (itemId: number): Promise<void> => {
  try {
    if (confirm('本当に備品を復元をしますか？')) {
      const res = await axios.post(`api/items/${itemId}/restore`);
      // console.log(res.data.status);
      if (res.data.status === 'success') {
        fetchAndFilterItems(); // 成功時にリストを更新
        alert(res.data.message);
      } else {
        alert(res.data.message);
      }
    }
  } catch (error: any) {
    console.error('Items/Index.vue restoreItem method error:', error.message);
  }
};
</script>

<template>
  <Head title="備品一覧" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">備品一覧</h2>
          <p class="mt-1 text-sm text-gray-600">登録されている備品を管理</p>
        </div>

        <Link
          :href="route('items.create')"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow"
        >
          <PlusIcon class="size-4" />
          備品を登録
        </Link>
      </div>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-4 md:p-6 text-gray-900">
            <FlashMessage />
            <section class="mt-2 text-gray-600 body-font">
              <!-- ツールバー全体をカードで囲む -->
              <div class="mb-8 bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div class="mt-4 mb-2 space-y-4 md:max-w-4xl md:mx-auto">
                  <!-- 検索フォームとクリアボタングループ -->
                  <div
                    class="flex flex-col lg:flex-row lg:gap-4 space-y-4 lg:space-y-0 lg:max-w-4xl lg:mx-auto"
                  >
                    <div
                      class="w-full flex justify-around md:justify-center lg:justify-start items-center space-x-1 md:space-x-0 order-1 lg:order-2"
                    >
                      <!-- 検索フォーム -->
                      <div class="flex items-center flex-1 md:flex-none">
                        <input
                          type="text"
                          name="search"
                          id="search"
                          v-model="search"
                          placeholder="備品名で検索"
                          @keyup.enter="fetchAndFilterItems"
                          class="h-9 w-full md:w-96 lg:w-60 text-sm md:text-base placeholder-text-xs md:placeholder-text-base"
                        />
                        <button
                          @click="fetchAndFilterItems"
                          id="searchButton"
                          class="h-9 w-9 md:w-10 bg-blue-300 text-white py-2 px-2 flex justify-center items-center border border-gray-300"
                        >
                          <MagnifyingGlassIcon class="size-6" />
                        </button>
                      </div>

                      <!-- 条件をすべてクリアするボタン -->
                      <div>
                        <button
                          @click="clearState"
                          class="text-xs md:text-base flex justify-center items-center w-16 h-9 p-2 md:ml-4 bg-gray-100 hover:bg-gray-200 text-gray-500 border border-gray-300 focus:outline-none rounded"
                        >
                          クリア
                        </button>
                      </div>
                    </div>

                    <!-- プルダウングループ -->
                    <div
                      class="w-full mt-4 flex justify-around md:justify-center lg:justify-end items-center space-x-1 md:space-x-4 order-2 lg:order-1"
                    >
                      <!-- 備品カテゴリプルダウン -->
                      <div class="w-full sm:w-1/3 md:w-auto">
                        <select
                          v-model="categoryId"
                          @change="fetchAndFilterItems"
                          class="h-9 w-[6.5rem] md:w-40 text-xs md:text-sm"
                        >
                          <option :value="0">カテゴリ</option>
                          <option
                            v-for="category in categories"
                            :value="category.id"
                            :key="category.id"
                          >
                            {{ category.name }}
                          </option>
                        </select>
                      </div>

                      <!-- 利用場所のプルダウン -->
                      <div class="w-full sm:w-1/3 md:w-auto">
                        <select
                          v-model="locationOfUseId"
                          @change="fetchAndFilterItems"
                          class="h-9 w-[6.5rem] md:w-40 text-xs md:text-sm"
                        >
                          <option :value="0">利用場所</option>
                          <option
                            v-for="location in locations"
                            :value="location.id"
                            :key="location.id"
                          >
                            {{ location.name }}
                          </option>
                        </select>
                      </div>

                      <!-- 保管場所のプルダウン -->
                      <div class="w-full sm:w-1/3 md:w-auto">
                        <select
                          v-model="storageLocationId"
                          @change="fetchAndFilterItems"
                          class="h-9 w-[6.5rem] md:w-40 text-xs md:text-sm"
                        >
                          <option :value="0">保管場所</option>
                          <option
                            v-for="location in locations"
                            :value="location.id"
                            :key="location.id"
                          >
                            {{ location.name }}
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- 区切り線 -->
                  <div class="border-t border-gray-300 my-4"></div>

                  <!-- 表示切り替えボタンとソート機能のグループ -->
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <!-- 行表示・タイル表示の切り替えボタン -->
                      <div class="inline-flex rounded-lg border border-gray-300 bg-white p-0.5">
                        <button
                          @click="isTableView = true"
                          :class="[
                            'inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-all whitespace-nowrap',
                            isTableView
                              ? 'bg-gray-200 text-gray-900'
                              : 'text-gray-600 hover:bg-gray-100',
                          ]"
                        >
                          <Bars3Icon class="size-4 mr-1.5" />
                          <span class="hidden sm:inline">リスト</span>
                        </button>
                        <button
                          @click="isTableView = false"
                          :class="[
                            'inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-all whitespace-nowrap',
                            !isTableView
                              ? 'bg-gray-200 text-gray-900'
                              : 'text-gray-600 hover:bg-gray-100',
                          ]"
                        >
                          <Squares2X2Icon class="size-4 mr-1.5" />
                          <span class="hidden sm:inline">グリッド</span>
                        </button>
                      </div>

                      <!-- 作成日でソートボタン -->
                      <button
                        @click="toggleSortOrder"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
                      >
                        <component
                          :is="sortOrder === 'asc' ? ArrowUpIcon : ArrowDownIcon"
                          class="size-4"
                        />
                        <span>{{ sortOrder === 'asc' ? '古い順' : '新しい順' }}</span>
                      </button>
                    </div>

                    <!-- 空の中央部 -->
                    <div class="w-full sm:w-1/3 md:w-auto"></div>

                    <!-- 廃棄済み備品に切り替えのトグルボタン -->
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                      <div class="relative">
                        <input
                          id="toggle"
                          type="checkbox"
                          v-model="isDisposal"
                          @input="toggleItems"
                          class="sr-only peer"
                        />
                        <div
                          class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-red-500 transition-colors"
                        ></div>
                        <div
                          class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform peer-checked:translate-x-5"
                        ></div>
                      </div>
                      <span
                        class="text-sm font-medium transition-colors whitespace-nowrap"
                        :class="isDisposal ? 'text-red-600' : 'text-gray-700'"
                      >
                        <span class="sm:hidden">{{ isDisposal ? '廃棄済み' : '使用中' }}</span>
                        <span class="hidden sm:inline">{{
                          isDisposal ? '廃棄済みを表示中' : '使用中のみ表示中'
                        }}</span>
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-4 flex flex-col items-center justify-end space-y-3">
                <Pagination :links="localItems.links" />
                <div class="font-medium text-xs md:text-sm">
                  {{ totalCount }}件中 {{ startNumber }}件目～{{ endNumber }}件目
                </div>
              </div>

              <!-- 行表示 -->
              <div v-if="isTableView">
                <div class="min-w-full overflow-auto">
                  <table
                    v-if="localItems.data && localItems.data.length > 0"
                    class="table-fixed min-w-full text-left whitespace-no-wrap"
                  >
                    <thead>
                      <tr>
                        <th
                          v-if="isDisposal"
                          class="min-w-16 md:min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          復元
                        </th>
                        <th
                          class="min-w-16 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          履歴
                        </th>
                        <th
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          管理ID
                        </th>
                        <th
                          class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          登録日
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
                          class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
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
                          class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得区分
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得先
                        </th>
                        <th
                          class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得価額
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          取得年月日
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          点検予定日
                        </th>
                        <th
                          class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          廃棄予定日
                        </th>
                        <th
                          class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          メーカー
                        </th>
                        <th
                          class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          製品番号
                        </th>
                        <th
                          class="min-w-56 md:min-w-56 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                        >
                          備考
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in localItems.data" :key="item.id" class="">
                        <td
                          v-if="isDisposal"
                          class="border-b-2 border-gray-200 px-4 py-3"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          <button
                            type="button"
                            @click="restoreItem(item.id)"
                            class="text-blue-400 flex justify-center"
                          >
                            <ArrowLeftStartOnRectangleIcon class="size-5" />
                            復元
                          </button>
                        </td>
                        <!-- マイクロモーダル -->
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          <div class="flex justify-center items-center">
                            <EditHistoryModal :item="item" :isTableView="isTableView" />
                          </div>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          <template v-if="!isDisposal">
                            <Link
                              class="text-blue-400"
                              :href="route('items.show', { item: item.id })"
                              id="managementId"
                            >
                              {{ item.management_id }}
                            </Link>
                          </template>
                          <template v-else>
                            {{ item.management_id }}
                          </template>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.created_at }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base max-w-full h-auto"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          <div class="flex justify-center">
                            <img :src="item.image_path1" alt="画像" class="h-8" />
                          </div>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.category.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          <span>{{ item.stock }}{{ item.unit.name }}</span>
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.usage_status.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.end_user }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.location_of_use.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.storage_location.name }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.acquisition_method ? item.acquisition_method.name : '' }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.acquisition_source }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.price }}円
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.date_of_acquisition }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.inspection_scheduled_date ?? '予定なし' }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.disposal ? item.disposal.disposal_scheduled_date : '予定なし' }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.manufacturer }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.product_number }}
                        </td>
                        <td
                          class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base"
                          :class="isDisposal ? 'bg-red-100' : ''"
                        >
                          {{ item.remarks ?? '' }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-else>
                    <div class="flex items-center justify-center">
                      <InformationCircleIcon class="w-6 h-6 text-black" />
                      <div class="ml-2 text-center py-4">備品が見つかりません</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- タイル表示 -->
              <div v-else>
                <div
                  v-if="localItems.data && localItems.data.length > 0"
                  class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-0"
                >
                  <template v-for="item in localItems.data" :key="item.id">
                    <div class="w-full p-2 border" :class="isDisposal ? 'bg-red-100' : ''">
                      <a class="mb-2 block relative h-48">
                        <Link :href="route('items.show', { item: item.id })">
                          <img
                            alt="備品の画像"
                            :src="item.image_path1"
                            class="object-cover object-center w-full h-full block border border-gray-300"
                          />
                        </Link>
                      </a>

                      <span
                        class="ml-4 bg-gray-400 text-white text-sm tracking-widest title-font py-1 px-2 rounded-md"
                        >{{ item.category.name }}</span
                      >

                      <div class="ml-4 mt-1">
                        <span class="mr-2 text-base font-medium">備品名</span>
                        <span class="text-gray-900 title-font text-base">{{ item.name }}</span>
                      </div>
                      <div class="ml-4">
                        <span class="mr-2 text-xs lg:text-sm font-medium">管理ID</span>
                        <Link :href="route('items.show', { item: item.id })">
                          <span class="text-blue-600 title-font text-xs lg:text-sm">{{
                            item.management_id
                          }}</span>
                        </Link>
                      </div>
                      <div class="ml-4">
                        <span class="mr-2 text-xs lg:text-sm font-medium">利用場所</span>
                        <span class="text-gray-900 title-font text-xs lg:text-sm">{{
                          item.location_of_use.name
                        }}</span>
                      </div>
                      <div class="ml-4">
                        <span class="mr-2 text-xs lg:text-sm font-medium">保管場所</span>
                        <span class="text-gray-900 title-font text-xs lg:text-sm">{{
                          item.storage_location.name
                        }}</span>
                      </div>

                      <div
                        class="flex justify-center space-x-4 md:space-x-1 lg:space-x-2 mt-2 w-full"
                      >
                        <div class="flex-shrink-0">
                          <EditHistoryModal v-bind:item="item" :isTableView="isTableView" />
                        </div>
                        <Link
                          v-if="!isDisposal"
                          as="button"
                          :href="route('items.show', { item: item.id })"
                          class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-4 focus:outline-none hover:bg-blue-900 rounded flex-shrink-0"
                        >
                          <ArrowRightIcon class="w-5 h-5 mr-1 transform -translate-y-px" />
                          詳細を見る
                        </Link>
                        <button
                          v-else
                          type="button"
                          @click="restoreItem(item.id)"
                          class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-4 focus:outline-none hover:bg-blue-900 rounded flex-shrink-0"
                        >
                          <ArrowLeftStartOnRectangleIcon
                            class="size-5 mr-1 transform -translate-y-px"
                          />
                          復元する
                        </button>
                      </div>
                    </div>
                  </template>
                </div>
                <div v-else>
                  <div class="flex items-center justify-center">
                    <InformationCircleIcon class="w-6 h-6 text-black" />
                    <div class="ml-2 text-center py-4">備品が見つかりません</div>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex flex-col items-center justify-end space-y-3">
                <Pagination :links="localItems.links" />
                <div class="font-medium text-xs md:text-sm">
                  {{ totalCount }}件中 {{ startNumber }}件目～{{ endNumber }}件目
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
/* プレースホルダーの文字サイズを調整 */
.placeholder-text-xs::placeholder {
  font-size: 0.75rem; /* 12px */
}
.placeholder-text-base::placeholder {
  font-size: 1rem; /* 16px */
}
</style>
