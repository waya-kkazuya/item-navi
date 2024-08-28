<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  itemRequests: Object,
  sortOrder: String,
  totalCount: Number,
  requestStatuses: Array
})

// 作成日でソート
const sortOrder = ref(props.sortOrder ?? 'asc')

const totalCount = ref(props.totalCount)

// すべてのフィルターをまとめる
const fetchAndFilterItems = () => {
  router.visit(route('item_requests.index', {
    sortOrder: sortOrder.value,
  }), {
    method: 'get'
  })
}

const toggleSortOrder = () => {
  // 昇順降順の切り替え
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  fetchAndFilterItems()
};

const updateStatus = async request => {
  console.log(request.id)
  try {
    const res = await axios.post(`/api/item-requests/${request.id}/update-status`, { requestStatusId: request.request_status_id });
    console.log('Status updated successfully', res.data)
    window.location.reload() // ページをリロード
  } catch (error) {
    console.error('Error updating status:', error)
  }
}

</script>

<template>
  <Head title="リクエスト一覧" />

  <AuthenticatedLayout>
      <template #header>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            リクエスト一覧
          </h2>
      </template>

      <div class="py-12">
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
              <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                  <div class="p-6 text-gray-900">
                    <FlashMessage />
                    <section class="text-gray-600 body-font">
                        <div class="container px-5 py-8 mx-auto">
                          <div class="flex items-center justify-around space-x-4">


                              <Link as="button" :href="route('item_requests.create')" class="flex items-center text-white text-sm bg-green-500 border-0 py-2 px-6 focus:outline-none hover:bg-green-600 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                備品をリクエストする
                              </Link>
                          </div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-4">

                          <div class="ml-4">
                            <button @click="toggleSortOrder" class="flex w-24">
                              <div class="text-sm">登録日</div>
                              <div>
                                <div v-if="sortOrder == 'asc'">
                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd" d="M10 18a.75.75 0 0 1-.75-.75V4.66L7.3 6.76a.75.75 0 0 1-1.1-1.02l3.25-3.5a.75.75 0 0 1 1.1 0l3.25 3.5a.75.75 0 1 1-1.1 1.02l-1.95-2.1v12.59A.75.75 0 0 1 10 18Z" clip-rule="evenodd" />
                                  </svg>
                                </div>
                                <div v-else>
                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v12.59l1.95-2.1a.75.75 0 1 1 1.1 1.02l-3.25 3.5a.75.75 0 0 1-1.1 0l-3.25-3.5a.75.75 0 1 1 1.1-1.02l1.95 2.1V2.75A.75.75 0 0 1 10 2Z" clip-rule="evenodd" />
                                  </svg>
                                </div>
                              </div>
                            </button>
                          </div>
                          <div class="">
                            <div class="font-medium">リクエスト合計 {{ totalCount }}件</div>
                            <Pagination class="ml-4" :links="itemRequests.links"></Pagination>
                          </div>
                        </div>

                       
                        <!-- 行表示 -->
                        <div class="min-w-full overflow-auto">
                          <table v-if="itemRequests.data && itemRequests.data.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
                            <thead>
                              <tr>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">ステータス</th>
                                <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">登録日</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">商品名</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">カテゴリ</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">利用場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">メーカー</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">参考サイト</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">価格</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">申請者</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">申請理由</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="request in itemRequests.data" :key="request.id" class="">

                                <td class="border-b-2 border-gray-200 px-4 py-3">
                                  {{ request.request_status.status_name }}
                                  <select 
                                  name="reqeustStatusId" 
                                  id="reqeustStatusId" 
                                  v-model="request.request_status_id" 
                                  @change="updateStatus(request)" 
                                  :class="[
                                    'w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out',
                                    {
                                      'bg-gray-200': request.request_status_id == 1,
                                      'bg-yellow-200': request.request_status_id == 2,
                                      'bg-green-200': request.request_status_id == 3,
                                      'bg-pink-200': request.request_status_id == 4
                                    }
                                  ]"
                                  >
                                      <option v-for="status in requestStatuses" :key="status.id" :value="status.id">{{ status.status_name }}</option>
                                  </select>
                                </td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.formatted_created_at }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.category.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.location_of_use.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.manufacturer }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.reference }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.price }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.requestor }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ request.remarks_from_requestor ?? '' }}</td>
                              </tr>
                            </tbody>
                          </table>
                          <div v-else>
                            <div class="flex items-center justify-center">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
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