<script setup lang="ts">
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';
import EditHistoryModal from '@/Components/EditHistoryModal.vue';
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
};

const props = defineProps<Props>();

// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localItems: Ref<Paginator<ItemType>> = ref({...props.items});

// 検索フォーム
const search: Ref<string> = ref(props.search);
// 作成日でソート
const sortOrder: Ref<string> = ref(props.sortOrder ?? 'asc');
// カテゴリプルダウン用(初期値は0)
const categoryId: Ref<number> = ref(props.categoryId);
const locationOfUseId: Ref<number> = ref(props.locationOfUseId ?? 0);
const storageLocationId: Ref<number> = ref(props.storageLocationId ?? 0);
// 備品の合計件数
const totalCount: Ref<number> = ref(props.totalCount);

// プルダウンや検索フォームの条件を適用して備品情報を再取得
const fetchAndFilterItems = (): void => {
  router.visit(route('items.index', {
    search: search.value,
    sortOrder: sortOrder.value,
    categoryId: categoryId.value,
    locationOfUseId: locationOfUseId.value,
    storageLocationId: storageLocationId.value,
  }), {
    method: 'get'
  });
};

// プルダウンや検索フォームをすべてリセット
const resetState = (): void => {
  search.value = '';
  sortOrder.value = 'asc';
  categoryId.value = 0;
  locationOfUseId.value = 0;
  storageLocationId.value = 0;
  fetchAndFilterItems();
};

// 行表示・タイル表示の切替 セッションにisTableViewを保存
const isTableView: Ref<boolean> = ref(sessionStorage.getItem('isTableView') !== 'false');
// watchでisTableViewを監視
watch(isTableView, (newValue: boolean) => {
  sessionStorage.setItem('isTableView', newValue.toString());
});
onMounted(() => {
  if (sessionStorage.getItem('isTableView') === null) {
    isTableView.value = true;
    sessionStorage.setItem('isTableView', 'true');
  }
});

const toggleSortOrder = (): void => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  fetchAndFilterItems();
};

// 備品と廃棄済み備品の表示切り替え
const showDisposal: Ref<boolean> = ref(false);
const toggleItems = async (): Promise<void> => {
  const url = showDisposal.value ? 'api/items?disposal=true' : 'api/items?disposal=false';
  try {
    const res = await axios.get(url);
    localItems.value = res.data.items;
    totalCount.value = res.data.total_count;
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'Items/Index.vue toggleItems method',
    });
  }
};

const restoreItem = (id: number) => {
  try {
    if (confirm('本当に備品を復元をしますか？')) {
      router.post(`/items/${id}/restore`);
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'Items/Index.vue restoreItem method',
    });
  }
  showDisposal.value = false;
};
</script>

<template>
    <Head title="備品一覧" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              備品一覧
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6 text-gray-900">
                      <FlashMessage />
                      <section class="mt-2 text-gray-600 body-font">
                          <div class="container md:px-5 mx-auto">
                            <!-- ボタンはコンテナ直下 -->
                            <div class="flex justify-center">
                                <Link as="button" id="create" :href="route('items.create')" class="flex items-center text-white text-sm bg-green-500 border-0 py-2 px-6 focus:outline-none hover:bg-green-600 rounded">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                  </svg>
                                  備品を登録する
                                </Link>
                            </div>

                            <div class="flex flex-col justify-around md:space-x-4 lg:flex-row">
                              <!-- 上段2段をひとかたまりにする -->
                              <div class="flex flex-col md:flex-row md:space-x-4">
                                <!-- 表示切り替えボタンとソート機能 -->
                                <div class="flex justify-between md:justify-around items-center mt-4 w-full mx-auto">
                                  <!-- 行表示・タイル表示の切り替えボタン -->
                                  <div class="mr-4 flex space-x-0">
                                    <div @click="isTableView = true" class="h-10 rounded-l-full" :class="{ 'bg-gray-300': isTableView, 'bg-white': !isTableView }">
                                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded-l-full" style="border: 1px solid black;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                      </svg>
                                    </div>
                                    <div @click="isTableView = false" class="h-10 rounded-r-full" :class="{ 'bg-gray-300': !isTableView, 'bg-white': isTableView }">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded-r-full" style="border: 1px solid black;">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                        </svg>
                                    </div>
                                  </div>

                                  <!-- 作成日でソート -->
                                  <div class="md:w-24 ml-4 md:ml-0">
                                    <button @click="toggleSortOrder" class="flex w-full text-sm">
                                      <div v-if="sortOrder == 'asc'" class="w-full flex justify-center">
                                        作成日昇順
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                          <path fill-rule="evenodd" d="M10 18a.75.75 0 0 1-.75-.75V4.66L7.3 6.76a.75.75 0 0 1-1.1-1.02l3.25-3.5a.75.75 0 0 1 1.1 0l3.25 3.5a.75.75 0 1 1-1.1 1.02l-1.95-2.1v12.59A.75.75 0 0 1 10 18Z" clip-rule="evenodd" />
                                        </svg>
                                      </div>
                                      <div v-else class="w-full flex justify-center">
                                        作成日降順
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                          <path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v12.59l1.95-2.1a.75.75 0 1 1 1.1 1.02l-3.25 3.5a.75.75 0 0 1-1.1 0l-3.25-3.5a.75.75 0 1 1 1.1-1.02l1.95 2.1V2.75A.75.75 0 0 1 10 2Z" clip-rule="evenodd" />
                                        </svg>
                                      </div>
                                    </button>
                                  </div>
                                </div>

                                <!-- プルダウングループ -->
                                <div class="w-full mt-4 flex justify-around md:justify-center items-center space-x-2 md:space-x-4">
                                  <!-- 備品カテゴリプルダウン -->
                                  <div class="w-full sm:w-1/3 md:w-auto">
                                    <select v-model="categoryId" @change="fetchAndFilterItems" class="h-9 w-24 md:w-40 text-xs md:text-base">
                                      <option :value="0">カテゴリ
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class=" size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                      </option>  
                                      <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>  
                                      </option>
                                    </select>
                                  </div>

                                  <!-- 利用場所のプルダウン -->
                                  <div class="w-full sm:w-1/3 md:w-auto">
                                    <select v-model="locationOfUseId" @change="fetchAndFilterItems" class="h-9 w-26 md:w-40 text-xs md:text-base">
                                      <option :value="0">利用場所
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                      </option> 
                                      <option v-for="location in locations" :value="location.id" :key="location.id">{{ location.name }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>  
                                      </option>
                                    </select>
                                  </div>

                                  <!-- 保管場所のプルダウン -->
                                  <div class="w-full sm:w-1/3 md:w-auto">
                                    <select v-model="storageLocationId" @change="fetchAndFilterItems" class="h-9 w-26 md:w-40 text-xs md:text-base">
                                      <option :value="0">保管場所
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>
                                      </option>  
                                      <option v-for="location in locations" :value="location.id" :key="location.id">{{ location.name }}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        </svg>  
                                      </option>
                                    </select>
                                  </div>
                                </div>
                              </div>


                              <!-- 検索フォームとリセットボタングループ -->
                              <div class="w-full mt-4 md:w-1/2 flex justify-center space-x-4 md:space-x-0 self-center">
                                <!-- 検索フォーム -->
                                <div class="flex items-center">
                                  <input type="text" name="search" v-model="search" placeholder="備品名で検索" @keyup.enter="fetchAndFilterItems" class="h-9 md:w-60 text-sm md:text-base placeholder-text-xs md:placeholder-text-base">
                                  <button class="h-9 w-9 md:w-10 bg-blue-300 text-white py-2 px-2 flex justify-center items-center border border-gray-300" @click="fetchAndFilterItems">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class=" size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>                              
                                  </button>
                                </div>

                                <!-- 条件をすべてリセットするボタン -->
                                <div>
                                  <button @click="resetState" class="flex justify-center items-center w-24 md:w-32 h-9 p-2 md:ml-4 text-white text-sm bg-indigo-500 border-0 focus:outline-none hover:bg-indigo-600 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    <div class="ml-2 text-xs md:text-base">リセット</div>
                                  </button>
                                </div>
                              </div>

                            </div>
                          </div>

                          <div class="my-4">
                            <!-- チェックボックスを使用したトグルボタン -->
                            <label for="toggle" class="flex items-center cursor-pointer">
                              <div class="relative">
                                  <!-- Input -->
                                  <input id="toggle" type="checkbox" v-model="showDisposal" @change="toggleItems" class="sr-only">
                                  <!-- 背景 -->
                                  <div class="block bg-gray-300 w-10 h-6 md:w-14 md:h-8 rounded-full "></div>
                                  <!-- 丸 -->
                                  <div class="dot absolute left-1 top-1 bg-white w-4 h-4 md:w-6 md:h-6 rounded-full transition"></div>
                              </div>
                              <!-- Label -->
                              <div class="ml-3 text-gray-700 font-medium text-sm">
                                  廃棄済みの備品を表示する
                              </div>
                            </label>
                          </div>

                          
                          <div class="mb-4 flex justify-end items-end space-x-2">
                            <div class="font-medium text-xs md:text-sm">備品合計 {{ totalCount }}件</div>
                            <Pagination class="" :links="localItems.links" />
                          </div>

                         
                          <!-- 行表示 -->
                          <div v-if="isTableView">
                            <div class="min-w-full overflow-auto">
                              <table v-if="localItems.data && localItems.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                                <thead>
                                  <tr>
                                    <th v-if="showDisposal" class="min-w-16 md:min-w-24 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">復元</th>
                                    <th class="min-w-16 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">履歴</th>
                                    <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">管理ID</th>
                                    <th class="min-w-28 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">登録日</th>
                                    <th class="min-w-32 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備品名</th>
                                    <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">画像</th>
                                    <th class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">カテゴリ</th>
                                    <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">在庫数</th>
                                    <th class="min-w-24 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用状況</th>
                                    <th class="min-w-20 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">使用者</th>
                                    <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">利用場所</th>
                                    <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">保管場所</th>
                                    <th class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得区分</th>
                                    <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得先</th>
                                    <th class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得価額</th>
                                    <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">取得年月日</th>
                                    <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">点検予定日</th>
                                    <th class="min-w-32 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">廃棄予定日</th>
                                    <th class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">メーカー</th>
                                    <th class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">製品番号</th>
                                    <th class="min-w-36 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700">備考</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="item in localItems.data" :key="item.id" class="">
                                    <td v-if="showDisposal" class="border-b-2 border-gray-200 px-4 py-3 " :class="showDisposal ? 'bg-red-100' : ''">
                                      <button type="button" @click="restoreItem(item.id)" class="text-blue-400 flex justify-center" >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                        </svg>
                                        復元
                                      </button>
                                    </td>
                                    <!-- マイクロモーダル -->
                                    <td class="border-b-2 border-gray-200 px-4 py-2" :class="showDisposal ? 'bg-red-100' : ''">
                                      <div class="flex justify-center items-center">
                                        <EditHistoryModal :item="item" :isTableView="isTableView" />
                                      </div>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">
                                      <template v-if="!showDisposal">
                                        <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                          {{ item.management_id }}
                                        </Link>
                                      </template>
                                      <template v-else>
                                        {{ item.management_id }}
                                      </template>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.created_at }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base max-w-full h-auto" :class="showDisposal ? 'bg-red-100' : ''">
                                      <div class="flex justify-center">
                                        <img :src="item.image_path1" alt="画像" class="h-8">
                                      </div>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.category.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''"><span>{{ item.stock }}{{ item.unit.name }}</span></td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.usage_status.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.end_user }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.location_of_use.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.storage_location.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.acquisition_method ? item.acquisition_method.name : '' }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.acquisition_source }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.price }}円</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.date_of_acquisition }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.inspection_scheduled_date ?? '予定なし'  }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.disposal ? item.disposal.disposal_scheduled_date : '予定なし' }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.manufacturer }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.product_number }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-2 text-center text-xs md:text-base" :class="showDisposal ? 'bg-red-100' : ''">{{ item.remarks ?? '' }}</td>
                                  </tr>
                                </tbody>
                              </table>
                              <div v-else>
                                <div class="flex items-center justify-center">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                  <div class="ml-2 text-center py-4">備品が見つかりません</div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- タイル表示 -->
                          <div v-else>
                            <div v-if="localItems.data && localItems.data.length > 0" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-0">
                              <template v-for="item in localItems.data" :key="item.id">
                                <div class="w-full p-2 border" :class="showDisposal ? 'bg-red-100' : ''">
                                  
                                    <a class="mb-2 block relative h-48">
                                      <Link :href="route('items.show', { item: item.id })">
                                        <img alt="備品の画像" :src="item.image_path1" class="object-cover object-center w-full h-full block border border-gray-300" >
                                      </Link>
                                    </a>

                                    <!-- <hr class="w-1/2 mx-auto my-2 border-t border-gray-300"> -->

                                    <!-- <span class="ml-4 mb-1 text-gray-400 text-sm tracking-widest title-font">{{ item.category.name }}</span> -->
                                    <span class="ml-4 bg-gray-300 text-white text-sm tracking-widest title-font py-1 px-2 rounded-md">{{ item.category.name }}</span>

                                    <div class="ml-4 mt-1">
                                      <span class="mr-2 text-base font-medium">備品名</span>
                                      <span class="text-gray-900 title-font text-base">{{ item.name }}</span>
                                    </div>
                                    <div class="ml-4">
                                      <span class="mr-2 text-xs lg:text-sm font-medium">管理ID</span>
                                      <Link :href="route('items.show', { item: item.id })">
                                        <span class="text-blue-600 title-font text-xs lg:text-sm">{{ item.management_id }}</span>
                                      </Link>
                                    </div>
                                    <div class="ml-4">
                                      <span class="mr-2 text-xs lg:text-sm font-medium">利用場所</span>
                                      <span class="text-gray-900 title-font text-xs lg:text-sm">{{ item.location_of_use.name }}</span>
                                    </div>
                                    <div class="ml-4">
                                      <span class="mr-2 text-xs lg:text-sm font-medium">保管場所</span>
                                      <span class="text-gray-900 title-font text-xs lg:text-sm">{{ item.storage_location.name }}</span>
                                    </div>

                                    <div class="flex justify-center space-x-4 md:space-x-1 lg:space-x-2 mt-2 w-full">
                                      <div class="flex-shrink-0">
                                        <EditHistoryModal v-bind:item="item" :isTableView="isTableView" />
                                      </div>
                                      <Link v-if="!showDisposal" as="button" :href="route('items.show', { item: item.id })" class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-4 focus:outline-none hover:bg-blue-900 rounded flex-shrink-0">
                                        詳細を見る
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M12 5l7 7-7 7"/></svg>
                                      </Link>
                                      <button v-else type="button" @click="restoreItem(item.id)" class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-4 focus:outline-none hover:bg-blue-900 rounded flex-shrink-0">
                                        <svg class="w-5 h-5 size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                        </svg>
                                        復元する
                                      </button>
                                    </div>

                                </div>
                              </template>
                            </div>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                  <div class="ml-2 text-center py-4">備品が見つかりません</div>
                                </div>
                            </div>
                          </div>

                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="localItems.links" />
                          </div>
                      </section>
                    </div>
                </div>
              </div>
            </div>
    </AuthenticatedLayout>
</template>

<style>
    input:checked ~ .dot {
        transform: translateX(100%);
    }
    input:checked ~ .block {
        background-color: red;
    }
    /* プレースホルダーの文字サイズを調整 */
    .placeholder-text-xs::placeholder {
      font-size: 0.75rem; /* 12px */
    }
    .placeholder-text-base::placeholder {
      font-size: 1rem; /* 16px */
  }
</style>