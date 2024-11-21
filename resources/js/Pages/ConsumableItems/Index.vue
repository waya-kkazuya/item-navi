<script setup lang="ts">
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';
import StockTransactionModal from '@/Components/StockTransactionModal.vue';
import UpdateStockModal from '@/Components/UpdateStockModal.vue';
import QrCodeReader from '@/Components/QrCodeReader.vue';
import { isMobile } from '@/utils/device';
import type { Ref } from 'vue'
import type { Paginator } from '@/@types/types';
import type { ItemType, CategoryType, LocationType } from '@/@types/model';
import type { ValidationErrors } from '@/@types/types';

type Props = {
  consumableItems: Paginator<ItemType>;
  locations: LocationType[];
  search: string;
  sortOrder: string;
  locationOfUseId: number;
  storageLocationId: number;
  totalCount: number;
  userName: string;
  linkedItem: ItemType; // 指定した備品のモーダルを開くために使用
  errors: ValidationErrors;
}

const props = defineProps<Props>();

// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localConsumableItems: Ref<Paginator<ItemType>> = ref({...props.consumableItems});
// 合計件数
const totalCount: Ref<number> = ref(props.totalCount);
// 検索フォーム
const search: Ref<string> = ref(props.search);
// 作成日でソート
const sortOrder: Ref<string> = ref(props.sortOrder ?? 'asc');
// カテゴリプルダウン用(初期値は0)、更新したらその値
const locationOfUseId: Ref<number> = ref(props.locationOfUseId ?? 0);
const storageLocationId: Ref<number> = ref(props.storageLocationId ?? 0);

// デバイスがPCかスマホ・タブレットか判定する
const isMobileDevice: Ref<boolean> = ref(false);

onMounted(() => {
  isMobileDevice.value = isMobile();

  // 通知画面の消耗品在庫数タブのリンクからの画面遷移、linkedItemの可否でモーダルウィンドウを開く
  if (props.linkedItem) {
    openUpdateStockModal(props.linkedItem);
  }
});

watch(() => props.linkedItem, (newVal: ItemType) => {
  if (newVal) {
    openStockTransactionModal(newVal);
  }
});

// 入出庫履歴モーダル
const isStockTransactionModalOpen: Ref<boolean> = ref(false);
const selectedStockTransactionItem: Ref<ItemType | null> = ref(null);

const openStockTransactionModal = (item: ItemType): void => {
  selectedStockTransactionItem.value = item;
  isStockTransactionModalOpen.value = true;
};
const closeStockTransactionModal = (): void => {
  isStockTransactionModalOpen.value = false;
};


// 入出庫モーダル
const isUpdateStockModalOpen: Ref<boolean> = ref(false);
const selectedUpdateStockItem: Ref<ItemType | null> = ref(null);

const openUpdateStockModal = (item: ItemType): void => {
  selectedUpdateStockItem.value = item;
  isUpdateStockModalOpen.value = true;
};
const closeUpdateStockModal = (): void => {
  isUpdateStockModalOpen.value = false;
  fetchConsumableItems();
};


// プルダウンや検索フォームを反映
const fetchAndFilterItems = (): void => {
  router.visit(route('consumable_items', {
    search: search.value,
    sortOrder: sortOrder.value,
    locationOfUseId: locationOfUseId.value,
    storageLocationId: storageLocationId.value,
  }), {
    method: 'get'
  });
};

const toggleSortOrder = (): void => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  fetchAndFilterItems();
};

//プルダウンや検索フォームをリセット
const resetState = (): void => {
  search.value = '';
  sortOrder.value = 'asc';
  locationOfUseId.value = 0;
  storageLocationId.value = 0;
  fetchAndFilterItems();
};

// 入出庫処理をした後に画面の情報を更新する
const fetchConsumableItems = async (): Promise<void> => {
  try {
    const res = await axios.get('/api/consumable_items?reload=true');
    localConsumableItems.value = res.data.items;
    totalCount.value = res.data.total_count;
  } catch (e: any){
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'ConsumableItems/Index.vue fetchConsumableItems method',
    });
  }
};
</script>

<template>
    <Head title="消耗品管理" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              消耗品管理
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <FlashMessage />
                      <section class="mt-2 text-gray-600 body-font">
                          <div class="container px-5 mx-auto">
                            <!-- ボタンはコンテナ直下 -->
                            <div class="flex justify-center">
                              <a :href="route('generate_pdf')" target="_blank" class="flex items-center text-white text-sm bg-green-500 border-0 py-2 px-6 focus:outline-none hover:bg-green-600 rounded">
                                消耗品QRコ―ドをダウンロード
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                              </a>
                            </div>
                            

                            <div class="flex flex-col md:space-x-4 lg:space-x-12 lg:flex-row justify-center items-center pl-4 mt-4 lg:mt-6 w-full">
                              <!-- 作成日とプルダウンをdivタグで囲む -->
                              <div class="flex justify-center items-center space-x-4">
                                <!-- 作成日でソート -->
                                <div class="w-full sm:w-1/3 md:w-auto ml-0 md:ml-0">
                                  <button @click="toggleSortOrder" class="flex w-full text-xs md:text-sm">
                                    <div v-if="sortOrder == 'asc'" class="w-full flex justify-center items-center whitespace-nowrap">
                                      作成日昇順
                                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                        <path fill-rule="evenodd" d="M10 18a.75.75 0 0 1-.75-.75V4.66L7.3 6.76a.75.75 0 0 1-1.1-1.02l3.25-3.5a.75.75 0 0 1 1.1 0l3.25 3.5a.75.75 0 1 1-1.1 1.02l-1.95-2.1v12.59A.75.75 0 0 1 10 18Z" clip-rule="evenodd" />
                                      </svg>
                                    </div>
                                    <div v-else class="w-full flex justify-center items-center whitespace-nowrap">
                                      作成日降順
                                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                        <path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v12.59l1.95-2.1a.75.75 0 1 1 1.1 1.02l-3.25 3.5a.75.75 0 0 1-1.1 0l-3.25-3.5a.75.75 0 1 1 1.1-1.02l1.95 2.1V2.75A.75.75 0 0 1 10 2Z" clip-rule="evenodd" />
                                      </svg>
                                    </div>
                                  </button>
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

                              <!-- 検索フォームとリセットボタンをdivタグで囲む -->
                              <div class="w-full mt-4 lg:mt-0 md:w-1/2 flex justify-center lg:justify-start space-x-4 md:space-x-0 self-center">
                                <!-- 検索フォーム -->
                                <div class="flex items-center relative">
                                  <input type="text" name="search" v-model="search" placeholder="備品名で検索" @keyup.enter="fetchAndFilterItems" class="h-9 md:w-60 text-sm md:text-base placeholder-text-xs md:placeholder-text-base">
                                  <div v-if="isMobileDevice" class="absolute right-10 md:right-11">
                                    <!-- <ToolTip /> -->
                                    <QrCodeReader />
                                  </div>
                                  <button class="h-9 w-9 md:w-10 bg-blue-300 text-white py-2 px-2 flex justify-center items-center border border-gray-300" @click="fetchAndFilterItems">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
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
                          
                          <div class="my-4 flex justify-end items-end space-x-2">
                            <div class="font-medium text-xs md:text-sm">備品合計 {{ totalCount }}件</div>
                            <Pagination class="" :links="localConsumableItems.links"></Pagination>
                          </div>

                          <!-- タイル表示 -->
                          <div class="mt-4">
                            <div v-if="localConsumableItems.data.length > 0" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-0">
                              <template v-for="item in localConsumableItems.data" :key="item.id">
                                <div class="w-full p-2 border">
                                  <div>
                                    <a class="mb-2 block relative h-48">
                                      <Link :href="route('items.show', { item: item.id })">
                                        <img alt="消耗品の画像" class="object-cover object-center w-full h-full block border border-gray-300" :src="item.image_path1">
                                      </Link>
                                    </a>
                                    
                                    <div class="ml-4">
                                      <span class="mr-2 text-base font-medium">備品名</span>
                                      <span class="text-gray-900 title-font text-base">{{ item.name }}</span>
                                    </div>

                                    <div class="ml-4">
                                      <span class="mr-2 text-xs lg:text-sm font-medium">管理ID</span>
                                      <Link :href="route('items.show', { item: item.id })">
                                        <span class="text-blue-600 title-font text-xs lg:text-sm">{{ item.management_id }}</span>
                                      </Link>
                                    </div>
                                    <div class="ml-4 mt-1">
                                      <span class="text-sm lg:text-base">在庫数 {{ item.stock }} / 通知在庫数 {{ item.minimum_stock }} ({{ item.unit.name }})</span>
                                    </div>
                                    <div class="ml-4">
                                      <!-- {{ item.notification ? 'オン' : 'オフ'}} -->
                                      <div v-if="item.notification" class="flex justify-start items-center text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                          <path d="M5.85 3.5a.75.75 0 0 0-1.117-1 9.719 9.719 0 0 0-2.348 4.876.75.75 0 0 0 1.479.248A8.219 8.219 0 0 1 5.85 3.5ZM19.267 2.5a.75.75 0 1 0-1.118 1 8.22 8.22 0 0 1 1.987 4.124.75.75 0 0 0 1.48-.248A9.72 9.72 0 0 0 19.266 2.5Z" />
                                          <path fill-rule="evenodd" d="M12 2.25A6.75 6.75 0 0 0 5.25 9v.75a8.217 8.217 0 0 1-2.119 5.52.75.75 0 0 0 .298 1.206c1.544.57 3.16.99 4.831 1.243a3.75 3.75 0 1 0 7.48 0 24.583 24.583 0 0 0 4.83-1.244.75.75 0 0 0 .298-1.205 8.217 8.217 0 0 1-2.118-5.52V9A6.75 6.75 0 0 0 12 2.25ZM9.75 18c0-.034 0-.067.002-.1a25.05 25.05 0 0 0 4.496 0l.002.1a2.25 2.25 0 1 1-4.5 0Z" clip-rule="evenodd" />
                                        </svg>
                                        <div>通知オン</div>
                                      </div>
                                      <div v-else class="flex justify-start items-center text-xs text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                          <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM20.57 16.476c-.223.082-.448.161-.674.238L7.319 4.137A6.75 6.75 0 0 1 18.75 9v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206Z" />
                                          <path fill-rule="evenodd" d="M5.25 9c0-.184.007-.366.022-.546l10.384 10.384a3.751 3.751 0 0 1-7.396-1.119 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 1 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z" clip-rule="evenodd" />
                                        </svg>
                                        <div >通知オフ</div>
                                      </div>
                                    </div>
                                    <div class="mt-2 flex justify-center space-x-4 md:space-x-1 lg:space-x-2 items-center max-h-20 ">
                                      <!-- 親コンポーネントからモーダルを開くボタン -->
                                      <button @click="openStockTransactionModal(item)" type="button" data-micromodal-trigger="modal-1" class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 focus:outline-none hover:bg-gray-600 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" />
                                        </svg>
                                        在庫履歴
                                      </button>
                                      <button @click="openUpdateStockModal(item)" type="button" data-micromodal-trigger="modal-1" class="flex items-center text-white text-sm bg-sky-500 border-0 py-2 px-4 focus:outline-none hover:bg-sky-600 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                        入出庫
                                      </button>
                                    </div>
                                  </div>
                                </div>
                              </template>
                              <StockTransactionModal v-if="selectedStockTransactionItem" v-show="isStockTransactionModalOpen" :item="selectedStockTransactionItem" @close="closeStockTransactionModal" />
                              <UpdateStockModal v-if="selectedUpdateStockItem" v-show="isUpdateStockModalOpen" :item="selectedUpdateStockItem" :userName="userName" :errors="errors" @close="closeUpdateStockModal" />
                            </div>
                            <div v-else>
                              <div class="flex items-center justify-center">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                  <div class="ml-2 text-center py-4">備品が見つかりません</div>
                                </div>
                            </div>
                          </div>

                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="localConsumableItems.links"></Pagination>
                          </div>
                      </section>
                    </div>
                </div>
              </div>
            </div>



    </AuthenticatedLayout>
</template>


<style scoped>
.modal-content {
  animation: slide-up 0.3s ease-out;
}
.modal-content-leave-active {
  animation: slide-down 0.3s ease-in;
}
@keyframes slide-up {
  0% {
    transform: translateY(100%);
  }
  100% {
    transform: translateY(0);
  }
}
@keyframes slide-down {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(100%);
  }
}
</style>