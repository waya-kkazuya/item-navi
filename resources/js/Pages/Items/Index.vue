<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';
import { stringify } from 'postcss';


const props = defineProps({
  items: Object,
  categories: Array,
  locations: Array,
  search: String,
  sortOrder: String,
  category_id: String,
  location_of_use_id: String,
  storage_location_id: String,
})

// 行表示・タイル表示の切替 セッションにisTableViewを保存する
const isTableView = ref(sessionStorage.getItem('isTableView') !== 'false')
// const isTableView = ref(props.isTableView ?? true)

// 作成日でソート
const sortOrder = ref(props.sortOrder ?? 'asc')

// 検索フォーム
const search = ref(props.search)

// カテゴリプルダウン用(初期値は0)、更新したらその値
// コントローラーをまたいで
const category_id = ref(props.category_id)
const location_of_use_id = ref(props.location_of_use_id ?? 0)
const storage_location_id = ref(props.storage_location_id ?? 0)


// すべてのフィルターをまとめる
const fetchAndFilterItems = () => {
  router.visit(route('items.index', {
    search: search.value,
    sortOrder: sortOrder.value,
    category_id: category_id.value,
    location_of_use_id: location_of_use_id.value,
    storage_location_id: storage_location_id.value,
  }), {
    method: 'get'
  })
}

const resetState = () => {
  //それぞれのリアクティブな値もデフォルトの値に戻して、プルダウンや検索フォームに反映する 
  search.value = ''
  sortOrder.value = 'asc'
  category_id.value = 0
  location_of_use_id.value = 0
  storage_location_id.value = 0

  fetchAndFilterItems()
}

// watchでisTableViewを監視している
watch(isTableView, (newValue) => {
  sessionStorage.setItem('isTableView', newValue)
})

onMounted(() => {
  console.log(props.location_of_use_id)
  if (sessionStorage.getItem('isTableView') === null) {
    isTableView.value = true
    sessionStorage.setItem('isTableView', 'true')
  }
})

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  fetchAndFilterItems()
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
                    <div class="p-6 text-gray-900">
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <FlashMessage />

                            <div class="flex justify-end space-x-4">
                              <div class="">
                                  <Link as="button" :href="route('items.create')" class="flex items-center text-white text-sm bg-green-500 border-0 py-2 px-6 focus:outline-none hover:bg-green-600 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    備品を登録する
                                  </Link>
                              </div>
                            </div>

                            <div class="flex justify-center items-center pl-4 mt-4 lg:w-2/3 w-full mx-auto">
                              <!-- 行表示・タイル表示の切り替えボタン -->
                              <div class="mr-4 flex">
                                <button @click="isTableView = true" class="h-10" :class="{ 'selected': isTableView }">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded" style="border: 1px solid black;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                  </svg>
                                </button>
                                <button @click="isTableView = false" class="h-10" :class="{ 'selected': !isTableView }">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded" style="border: 1px solid black;">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                </button>
                              </div>


                              <!-- 作成日でソート -->
                              <div class="ml-4">
                                <button @click="toggleSortOrder" class="flex w-24">
                                  <div class="text-sm">作成日</div>
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

                              <div>
                                <select v-model="category_id" @change="fetchAndFilterItems" class="h-9 text-sm">
                                  <option :value="0">カテゴリ
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
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

                              
                              <div>
                                <select v-model="location_of_use_id" @change="fetchAndFilterItems" class="ml-4 h-9 text-sm">
                                  <option :value="0">利用場所すべて
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

                              <div>
                                <select v-model="storage_location_id" @change="fetchAndFilterItems" class="ml-4 h-9 text-sm">
                                  <option :value="0">保管場所すべて
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

                              <!-- 検索フォーム -->
                              <div class="ml-8 flex items-center">
                                <input type="text" name="search" v-model="search" placeholder="備品名で検索" @keyup.enter="fetchAndFilterItems" class="w-60 h-9 text-sm">
                                <button class="w-10 bg-blue-300 text-white py-2 px-2" @click="fetchAndFilterItems">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                  </svg>                              
                                </button>
                              </div>

                              <!-- 条件をすべてリセットするボタン -->
                              <div>
                                <button @click="resetState" class="flex justify-center items-center w-32 h-9 p-2 ml-4 text-white bg-indigo-500 border-0 p-2 focus:outline-none hover:bg-indigo-600 rounded text-sm">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                  </svg>
                                  <div class="ml-2">リセット</div>
                                </button>
                              </div>

                            </div>
                          </div>


                          <!-- <div v-if="items.data.length === 0">
                            <p>該当の備品は見つかりませんでした</p>
                            ここにイラストを表示
                          </div> -->

                          
                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="items.links"></Pagination>
                          </div>

                          <!-- 行表示 -->
                          <div v-if="isTableView">
                            <div class="min-w-full overflow-auto">
                              <table class="table-fixed min-w-full text-left whitespace-no-wrap">
                                <thead>
                                  <tr>
                                    <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700 rounded-tl rounded-bl">管理ID</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">登録日</th>
                                    <!-- <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">画像</th> -->
                                    <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">備品名</th>
                                    <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">カテゴリ</th>
                                    <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">在庫数</th>
                                    <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">利用状況</th>
                                    <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">使用者</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">利用場所</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">保管場所</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">取得区分</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">購入先</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">取得価額</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">取得年月日</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">点検予定日</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">廃棄予定日</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">メーカー</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">製品番号</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">備考</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(item, index) in items.data" :key="item.id" class="item">
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`id-${index}`">
                                      <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                        {{ item.id }}
                                      </Link>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`created_at-${index}`">{{ item.created_at }}</td>
                                    <!-- <td class="h-24 border-b-2 border-gray-200 px-4 py-3"><img :src="item.image_path1" alt="" class="h-full w-full"></td> -->
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`name-${index}`">{{ item.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`category-${index}`">{{ item.category.name }}</td>
                                    <td class="text-right border-b-2 border-gray-200 px-4 py-3" :class="`stocks-${index}`"><span>{{ item.stocks }}</span></td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`usage_status-${index}`">{{ item.usage_status }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`end_user-${index}`">{{ item.end_user }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`location_of_use-${index}`">{{ item.location_of_use.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`storage_location-${index}`">{{ item.storage_location.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`acquisition_category-${index}`">{{ item.acquisition_category }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`where_to_buy-${index}`">{{ item.where_to_buy }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`price-${index}`">{{ item.price }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`date_of_acquisition-${index}`">{{ item.date_of_acquisition }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`inspection_schedule-${index}`">{{ item.inspection_schedule }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`disposal_schedule-${index}`">{{ item.disposal_schedule }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`manufacturer-${index}`">{{ item.manufacturer }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="`product_number-${index}`">{{ item.product_number }}</td>
                                    <td class="overflow-hidden whitespace-nowrap border-b-2 border-gray-200 px-4 py-3" :class="`remarks-${index}`">{{ item.remarks ?? '' }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>

                          <!-- タイル表示 -->
                          <div v-else>
                            <div class="flex flex-wrap -mx-4">
                              <template v-for="item in items.data" :key="item.id">
                                <div class="lg:w-1/5 w-1/2 p-4 border">
                                  <div class="">
                                    <div class="flex items-end">
                                      <h2 class="text-gray-900 title-font text-lg font-medium">{{ item.name }}</h2>
                                      <h3 class="ml-2 text-gray-500 text-xs tracking-widest title-font mb-1">{{ item.category.name }}</h3>
                                    </div>
                                    <a class="mb-4 block relative h-48">
                                      <img alt="ecommerce" class="object-cover object-center w-full h-full block" :src="item.image_path1">
                                    </a>
                                    <div class="mt-4">
                                      <span class="mt-4">利用場所: {{ item.location_of_use.name }}</span><br>
                                      <span class="mt-4">保管場所: {{ item.storage_location.name }}</span><br>
                                      <span class="ml-4">在庫: {{ item.stocks }}個</span>
                                    </div>
                                  </div>
                                </div>
                              </template>
                            </div>
                          </div>
                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="items.links"></Pagination>
                          </div>

                          
                        
                      </section>
                    </div>
                </div>
              </div>
            </div>
    </AuthenticatedLayout>
</template>
