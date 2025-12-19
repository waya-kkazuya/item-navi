<script setup lang="ts">
import {
  ArrowDownTrayIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  MagnifyingGlassIcon,
  ArrowTrendingDownIcon,
  ArchiveBoxIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { BellAlertIcon, BellSlashIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import QrCodeReader from '@/Components/QrCodeReader.vue';
import StockTransactionModal from '@/Components/StockTransactionModal.vue';
import UpdateStockModal from '@/Components/UpdateStockModal.vue';

import { isMobile } from '@/utils/device';

import type { Ref } from 'vue';
import type { ItemType, CategoryType, LocationType } from '@/@types/model';
import type { Paginator } from '@/@types/types';
import type { ValidationErrors } from '@/@types/types';

type Props = {
  consumableItems: Paginator<ItemType>;
  locations: LocationType[];
  search: string;
  sortOrder: string;
  locationOfUseId: number;
  storageLocationId: number;
  totalCount: number;
  startNumber: number;
  endNumber: number;
  userName: string;
  linkedItem: ItemType; // 指定した備品のモーダルを開くために使用
  errors: ValidationErrors;
};

const props = defineProps<Props>();

// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localConsumableItems: Ref<Paginator<ItemType>> = ref({ ...props.consumableItems });
// 合計件数
const totalCount: Ref<number> = ref(props.totalCount);
const startNumber: Ref<number> = ref(props.startNumber);
const endNumber: Ref<number> = ref(props.endNumber);
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

watch(
  () => props.linkedItem,
  (newVal: ItemType) => {
    if (newVal) {
      openStockTransactionModal(newVal);
    }
  }
);

// 同期処理でプルダウンや検索フォームを反映
const fetchAndFilterItems = (): void => {
  router.visit(
    route('consumable_items', {
      search: search.value,
      sortOrder: sortOrder.value,
      locationOfUseId: locationOfUseId.value,
      storageLocationId: storageLocationId.value,
    }),
    {
      method: 'get',
    }
  );
};

const toggleSortOrder = (): void => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  fetchAndFilterItems();
};

//プルダウンや検索フォームをリセット
const clearState = (): void => {
  search.value = '';
  sortOrder.value = 'asc';
  locationOfUseId.value = 0;
  storageLocationId.value = 0;
  fetchAndFilterItems();
};

// 入出庫履歴モーダル
const isStockTransactionModalOpen: Ref<boolean> = ref(false);
const selectedStockTransactionItem: Ref<ItemType | null> = ref(null);

const openStockTransactionModal = (item: ItemType): void => {
  selectedStockTransactionItem.value = item;
  isStockTransactionModalOpen.value = true;
};
const closeStockTransactionModal = (): void => {
  selectedStockTransactionItem.value = null;
  isStockTransactionModalOpen.value = false;
};

// 入出庫処理モーダル
const isUpdateStockModalOpen: Ref<boolean> = ref(false);
const selectedUpdateStockItem: Ref<ItemType | null> = ref(null);

const openUpdateStockModal = (item: ItemType): void => {
  selectedUpdateStockItem.value = item;
  isUpdateStockModalOpen.value = true;
};
const closeUpdateStockModal = (itemId: number): void => {
  selectedUpdateStockItem.value = null;
  isUpdateStockModalOpen.value = false;
  fetchStock(itemId);
};

// 入出庫処理をした後に非同期で在庫数を更新する
const fetchStock = async (itemId: number): Promise<void> => {
  try {
    const res = await axios.get(`/api/consumable_items/${itemId}/stock`);
    const updatedItem = localConsumableItems.value.data.find((item) => item.id === itemId);
    if (updatedItem) {
      updatedItem.stock = res.data.stock;
    }
  } catch (error: any) {
    console.error('ConsumableItems/Index.vue fetchStock method error:', error.message);
  }
};
</script>

<template>
  <Head title="消耗品管理" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">消耗品管理</h2>

        <!-- 消耗品QRコードボタン -->
        <a
          :href="route('generate_pdf')"
          target="_blank"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow"
        >
          <ArrowDownTrayIcon class="size-4 ml-1" />
          消耗品QRコ―ドをダウンロード
        </a>
      </div>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <FlashMessage />
            <section class="mt-2 text-gray-600 body-font">
              <!-- ツールバーカード -->
              <div class="mb-8 bg-gray-50 rounded-lg border border-gray-200 p-4">
                <div
                  class="flex flex-col lg:flex-row lg:gap-4 space-y-4 lg:space-y-0 lg:max-w-4xl lg:mx-auto"
                >
                  <!-- 検索フォームとクリアボタングループ -->
                  <div
                    class="w-full flex justify-around md:justify-center lg:justify-start space-x-1 md:space-x-0 self-center order-1 lg:order-2"
                  >
                    <!-- 検索フォーム -->
                    <div class="flex items-center flex-1 md:flex-none relative">
                      <input
                        type="text"
                        id="search"
                        name="search"
                        v-model="search"
                        placeholder="備品名で検索"
                        @keyup.enter="fetchAndFilterItems"
                        class="h-9 w-full md:w-96 lg:w-60 text-sm md:text-base placeholder-text-xs md:placeholder-text-base"
                      />
                      <!-- スマホ・タブレットならカメラを使用してQRコードを読み取り可能 -->
                      <div v-if="isMobileDevice" class="absolute right-10 md:right-11">
                        <QrCodeReader />
                      </div>
                      <button
                        id="searchButton"
                        class="h-9 w-9 md:w-10 bg-blue-300 text-white py-2 px-2 flex justify-center items-center border border-gray-300"
                        @click="fetchAndFilterItems"
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

                  <!-- 作成日ソートボタンとカテゴリプルダウングループ -->
                  <div
                    class="flex justify-center lg:justify-end items-center space-x-4 order-2 lg:order-1"
                  >
                    <!-- 作成日でソート -->
                    <button
                      @click="toggleSortOrder"
                      class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
                    >
                      <component
                        :is="sortOrder === 'asc' ? ArrowUpIcon : ArrowDownIcon"
                        class="size-4"
                      />
                      <span class="hidden sm:inline">{{
                        sortOrder === 'asc' ? '古い順' : '新しい順'
                      }}</span>
                    </button>

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
              </div>

              <div class="mt-8 mb-4 flex flex-col items-center space-y-3">
                <Pagination :links="localConsumableItems.links" />
                <div class="font-medium text-xs md:text-sm">
                  {{ totalCount }}件中 {{ startNumber }}件目～{{ endNumber }}件目
                </div>
              </div>

              <!-- 消耗品一覧タイル表示 -->
              <div class="mt-4">
                <div
                  v-if="localConsumableItems.data.length > 0"
                  class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-0"
                >
                  <template v-for="item in localConsumableItems.data" :key="item.id">
                    <div
                      class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-shadow overflow-hidden"
                    >
                      <Link
                        :href="route('items.show', { item: item.id })"
                        class="block relative h-48 bg-gray-100"
                      >
                        <img
                          :src="item.image_path1"
                          alt="消耗品の画像"
                          class="w-full h-full object-cover hover:opacity-90 transition-opacity"
                        />

                        <!-- 在庫ステータスバッジ(新規追加) -->
                        <div class="absolute top-2 right-2">
                          <span
                            v-if="item.stock <= item.minimum_stock"
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                          >
                            <ExclamationTriangleIcon class="size-3" />
                            在庫少
                          </span>
                          <span
                            v-else
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full"
                          >
                            <CheckCircleIcon class="size-3" />
                            在庫あり
                          </span>
                        </div>
                      </Link>

                      <!-- コンテンツ -->
                      <div class="p-4 space-y-3">
                        <div>
                          <Link
                            :href="route('items.show', { item: item.id })"
                            class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors line-clamp-2"
                          >
                            {{ item.name }}
                          </Link>
                        </div>

                        <div class="flex items-center gap-2">
                          <span class="text-xs text-gray-500">ID:</span>
                          <Link
                            :href="route('items.show', { item: item.id })"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700"
                          >
                            {{ item.management_id }}
                          </Link>
                        </div>

                        <div class="bg-gray-50 rounded-md p-3 space-y-2">
                          <!-- 在庫数 -->
                          <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">現在在庫</span>
                            <span class="text-lg font-bold text-gray-900">
                              {{ item.stock
                              }}<span class="text-sm font-normal text-gray-500 ml-1">{{
                                item.unit.name
                              }}</span>
                            </span>
                          </div>

                          <!-- 通知在庫数 -->
                          <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">通知在庫数</span>
                            <span class="text-sm font-semibold text-gray-700">
                              {{ item.minimum_stock
                              }}<span class="text-xs font-normal text-gray-500 ml-1">{{
                                item.unit.name
                              }}</span>
                            </span>
                          </div>
                        </div>

                        <div class="flex items-center gap-2">
                          <template v-if="item.notification">
                            <BellAlertIcon class="size-4 text-blue-600" />
                            <span class="text-xs font-medium text-blue-600">通知オン</span>
                          </template>
                          <template v-else>
                            <BellSlashIcon class="size-4 text-gray-400" />
                            <span class="text-xs text-gray-400">通知オフ</span>
                          </template>
                        </div>

                        <div class="flex gap-2 pt-2">
                          <button
                            @click="openStockTransactionModal(item)"
                            type="button"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                          >
                            <ArrowTrendingDownIcon class="size-4" />
                            在庫履歴
                          </button>
                          <button
                            @click="openUpdateStockModal(item)"
                            type="button"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                          >
                            <ArchiveBoxIcon class="size-4" />
                            入出庫
                          </button>
                        </div>
                      </div>
                    </div>
                  </template>
                  <StockTransactionModal
                    v-if="selectedStockTransactionItem && isStockTransactionModalOpen"
                    :item="selectedStockTransactionItem"
                    @stockTransactionModalClosed="closeStockTransactionModal"
                  />
                  <UpdateStockModal
                    v-if="selectedUpdateStockItem && isUpdateStockModalOpen"
                    :item="selectedUpdateStockItem"
                    :userName="userName"
                    :errors="errors"
                    @updateStockModalClosed="closeUpdateStockModal"
                  />
                </div>
                <div v-else>
                  <div class="flex items-center justify-center">
                    <InformationCircleIcon class="w-6 h-6 text-black" />
                    <div class="ml-2 text-center py-4">備品が見つかりません</div>
                  </div>
                </div>
              </div>

              <div class="mt-6 mb-4 flex flex-col items-center space-y-3">
                <Pagination :links="localConsumableItems.links" />
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
