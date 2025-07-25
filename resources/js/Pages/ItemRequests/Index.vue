<script setup lang="ts">
import axios from 'axios';
import { PlusIcon, ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';

import type { Ref } from 'vue';
import type { ItemRequestType, RequestStatusType } from '@/@types/model';
import type { Paginator } from '@/@types/types';

type Props = {
  itemRequests: Paginator<ItemRequestType>;
  sortOrder: string;
  totalCount: number;
  requestStatuses: RequestStatusType[];
};

const props = defineProps<Props>();

// 作成日でソート
const sortOrder: Ref<string> = ref(props.sortOrder ?? 'desc');
// リクエスト合計件数
const totalCount: Ref<number> = ref(props.totalCount);

// すべてのフィルターをまとめる
const fetchAndFilterItems = (): void => {
  router.visit(
    route('item_requests.index', {
      sortOrder: sortOrder.value,
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

const updateStatus = async (request: ItemRequestType) => {
  try {
    await axios.post(`/api/item-requests/${request.id}/update-status`, {
      requestStatusId: request.request_status_id,
    });
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'ItemRequests/Index.vue updateStatus method',
    });
    // フラッシュメッセージの代わりにアラートを表示する
    alert('ステータスの変更に失敗しました、もう一度お試しください。');
  } finally {
    window.location.reload(); // ページをリロード
  }
};

const loginUserRole: Ref<number | null> = ref(null);
// ログインユーザー情報取得
const getUserRole = async () => {
  try {
    const res = await axios.get('/api/user-role');
    loginUserRole.value = res.data;
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'ItemRequests/Index.vue getUserRole method',
    });
  }
};

onMounted(() => {
  getUserRole();
});

const deleteItemRequest = (request: ItemRequestType) => {
  try {
    if (confirm('本当に削除しますか？')) {
      router.visit(route('item_requests.destroy', request), {
        method: 'delete',
      });
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'ItemRequests/Index.vue deleteItemRequest method',
    });
  }
};
</script>

<template>
  <Head title="リクエスト一覧" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">リクエスト一覧</h2>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <FlashMessage />
            <section class="mt-2 text-gray-600 body-font">
              <div class="container md:px-5 mx-auto">
                <div class="flex items-center justify-around space-x-4">
                  <Link
                    as="button"
                    :href="route('item_requests.create')"
                    id="createItemRequest"
                    class="flex items-center text-white text-sm bg-gray-400 border-0 py-2 px-6 focus:outline-none hover:bg-gray-300 rounded"
                  >
                    <PlusIcon class="size-6" />
                    備品をリクエストする
                  </Link>
                </div>
              </div>

              <div class="py-4 mb-4">
                <div class="md:w-24 ml-4">
                  <button @click="toggleSortOrder" class="flex w-full text-sm">
                    <div>
                      <div
                        v-if="sortOrder == 'asc'"
                        class="w-full flex justify-center items-center whitespace-nowrap"
                      >
                        日付古い順
                        <ArrowUpIcon class="size-5 ml-1" />
                      </div>
                      <div v-else class="w-full flex justify-center items-center whitespace-nowrap">
                        日付新しい順
                        <ArrowDownIcon class="size-5 ml-1" />
                      </div>
                    </div>
                  </button>
                </div>

                <div class="flex justify-end items-end space-x-2 mr-4">
                  <div class="font-medium text-xs md:text-sm">
                    リクエスト合計 {{ totalCount }}件
                  </div>
                  <Pagination class="ml-4" :links="itemRequests.links"></Pagination>
                </div>
              </div>

              <!-- 行表示 -->
              <div class="min-w-full overflow-auto">
                <table
                  v-if="itemRequests.data && itemRequests.data.length > 0"
                  class="table-fixed min-w-full text-left whitespace-no-wrap"
                >
                  <thead>
                    <tr>
                      <th
                        v-if="loginUserRole !== null && loginUserRole <= 5"
                        class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        登録
                      </th>
                      <th
                        class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        ステータス
                      </th>
                      <th
                        class="min-w-48 md:min-w-48 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        登録日
                      </th>
                      <th
                        class="min-w-28 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        商品名
                      </th>
                      <th
                        class="min-w-32 md:min-w-40 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        カテゴリ
                      </th>
                      <th
                        class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        利用場所
                      </th>
                      <th
                        class="min-w-32 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        メーカー
                      </th>
                      <th
                        class="min-w-28 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        参考サイト
                      </th>
                      <th
                        class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        価格
                      </th>
                      <th
                        class="min-w-24 md:min-w-32 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        申請者
                      </th>
                      <th
                        class="min-w-48 md:min-w-48 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      >
                        申請理由
                      </th>
                      <th
                        v-if="loginUserRole !== null && loginUserRole <= 5"
                        class="min-w-36 md:min-w-36 px-4 py-3 title-font tracking-wider font-medium text-center text-white text-xs md:text-base bg-sky-700"
                      ></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="request in itemRequests.data" :key="request.id">
                      <td
                        v-if="loginUserRole !== null && loginUserRole <= 5"
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-3"
                      >
                        <Link
                          as="button"
                          :href="
                            route('items.create', {
                              name: request.name,
                              category_id: request.category_id,
                              location_of_use_id: request.location_of_use_id,
                              manufacturer: request.manufacturer,
                              price: request.price,
                            })
                          "
                          class="w-28 flex justify-center items-center text-white text-xs bg-green-500 border-0 py-1 px-0 focus:outline-none hover:bg-green-600 rounded"
                        >
                          <PlusIcon class="size-6" />
                          新規登録
                        </Link>
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-3"
                      >
                        <template v-if="loginUserRole !== null && loginUserRole <= 5">
                          <select
                            name="reqeustStatusId"
                            id="reqeustStatusId"
                            v-model="request.request_status_id"
                            @change="updateStatus(request)"
                            :class="[
                              'w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-0 md:py-1 md:px-3 leading-8 transition-colors duration-200 ease-in-out',
                              {
                                'bg-gray-200': request.request_status_id == 1,
                                'bg-yellow-200': request.request_status_id == 2,
                                'bg-green-200': request.request_status_id == 3,
                                'bg-pink-200': request.request_status_id == 4,
                              },
                            ]"
                          >
                            <option
                              v-for="status in requestStatuses"
                              :key="status.id"
                              :value="status.id"
                            >
                              {{ status.status_name }}
                            </option>
                          </select>
                        </template>
                        <template v-else>
                          <div
                            :class="[
                              'w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-0 md:py-1 md:px-3 leading-8 transition-colors duration-200 ease-in-out',
                              {
                                'bg-gray-200': request.request_status_id == 1,
                                'bg-yellow-200': request.request_status_id == 2,
                                'bg-green-200': request.request_status_id == 3,
                                'bg-pink-200': request.request_status_id == 4,
                              },
                            ]"
                          >
                            {{ request.request_status.status_name }}
                          </div>
                        </template>
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.formatted_created_at }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.name }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.category.name }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.location_of_use.name }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.manufacturer }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.reference }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.price }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.requestor }}
                      </td>
                      <td
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        {{ request.remarks_from_requestor ?? '' }}
                      </td>
                      <td
                        v-if="loginUserRole !== null && loginUserRole <= 5"
                        class="border-b-2 border-gray-200 text-center text-xs md:text-base px-4 py-2"
                      >
                        <button
                          @click="deleteItemRequest(request)"
                          class="w-28 flex justify-center items-center text-white text-xs bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded"
                        >
                          削除
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-else>
                  <div class="flex items-center justify-center">
                    <InformationCircleIcon class="w-6 h-6 text-black" />
                    <div class="ml-2 text-center py-4">リクエストが見つかりません</div>
                  </div>
                </div>
              </div>

              <div class="mb-4 flex justify-end">
                <Pagination class="mt-6" :links="itemRequests.links"></Pagination>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
