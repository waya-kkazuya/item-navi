<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted, watch } from 'vue';
import { stringify } from 'postcss';
import MicroModal from '@/Components/Micromodal.vue';


const props = defineProps({
  items: Object,
  categories: Array,
  locations: Array,
  search: String,
  sortOrder: String,
  categoryId: Number,
  locationOfUseId: Number,
  storageLocationId: Number,
  totalCount: Number,
})

// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localItems = ref({...props.items});

// 作成日でソート
const sortOrder = ref(props.sortOrder ?? 'asc')

// 検索フォーム
const search = ref(props.search)

// カテゴリプルダウン用(初期値は0)、更新したらその値
// コントローラーをまたいで
const categoryId = ref(props.categoryId)
const locationOfUseId = ref(props.locationOfUseId ?? 0)
const storageLocationId = ref(props.storageLocationId ?? 0)


// すべてのフィルターをまとめる
const fetchAndFilterItems = () => {
  router.visit(route('items.index', {
    search: search.value,
    sortOrder: sortOrder.value,
    categoryId: categoryId.value,
    locationOfUseId: locationOfUseId.value,
    storageLocationId: storageLocationId.value,
  }), {
    method: 'get'
  })
}

const resetState = () => {
  //それぞれのリアクティブな値もデフォルトの値に戻して、プルダウンや検索フォームに反映する 
  search.value = ''
  sortOrder.value = 'asc'
  categoryId.value = 0
  locationOfUseId.value = 0
  storageLocationId.value = 0

  fetchAndFilterItems()
}

// 行表示・タイル表示の切替 セッションにisTableViewを保存する
const isTableView = ref(sessionStorage.getItem('isTableView') !== 'false')

// watchでisTableViewを監視している
watch(isTableView, (newValue) => {
  sessionStorage.setItem('isTableView', newValue)
})

onMounted(() => {
  console.log(props.items)
  console.log(props.locationOfUseId)
  if (sessionStorage.getItem('isTableView') === null) {
    isTableView.value = true
    sessionStorage.setItem('isTableView', 'true')
  }
})

const toggleSortOrder = () => {
  // 昇順降順の切り替え
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  fetchAndFilterItems()
};


// 備品<-->廃棄済み備品の表示切り替え
// 初期値はfalse
const showDisposal = ref(false);
const toggleItems = async () => {
  // showDisposal.value = !showDisposal.value;
  // showDisposalの値で叩くAPIのURLを変える
  console.log(showDisposal.value)
  const url = showDisposal.value ? 'api/items?disposal=true' : 'api/items?disposal=false';
  console.log(url)
  try {
    const res = await axios.get(url)
    // 受け取るデータはどんな型か
    
    console.log(res.data)
    localItems.value = res.data
    console.log(localItems.value)
  } catch (e) {
    console.log('エラーメッセージです', e.message)
  }
};

// props.itemsが新しい値になったら、代入
watch(() => props.items, (newItems) => {
  localItems.value = {...newItems};
});

// const editHistories = async () => {
//   try {
//     await axios.get(`api/edithistory/?item_id=${props.item.id}`)
//     .then( res => {
//       console.log(res.data)
//       editHistoriesData.value = res.data;
//     })
//     toggleStatus()
//   } catch(e) {
//       console.log(e.message)
//   }
// }


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
                      <FlashMessage />
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <div class="flex justify-between space-x-4">
                              
                              <!-- チェックボックスを使用したトグルボタン -->
                              <label for="toggle" class="flex items-center cursor-pointer">
                                  <div class="relative">
                                      <!-- Input -->
                                      <input id="toggle" type="checkbox" v-model="showDisposal" @change="toggleItems" class="sr-only">
                                      <!-- 背景 -->
                                      <div class="block bg-gray-300 w-14 h-8 rounded-full "></div>
                                      <!-- 丸 -->
                                      <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                                  </div>
                                  <!-- Label -->
                                  <div class="ml-3 text-gray-700 font-medium">
                                      廃棄済みの備品を表示する
                                  </div>
                              </label>

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
                              <div class="mr-4 flex space-x-0">
                                <div @click="isTableView = true" class="h-10 rounded-l-full" 
                                :class="{ 'bg-gray-300': isTableView, 'bg-white': !isTableView }">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded-l-full" style="border: 1px solid black;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                  </svg>
                                </div>
                                <div @click="isTableView = false" class="h-10 rounded-r-full" 
                                :class="{ 'bg-gray-300': !isTableView, 'bg-white': isTableView }">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 p-2 rounded-r-full" style="border: 1px solid black;">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                    </svg>
                                </div>
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

                              <!-- 備品カテゴリプルダウン -->
                              <div>
                                <select v-model="categoryId" @change="fetchAndFilterItems" class="h-9 text-sm">
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

                              <!-- 利用場所のプルダウン -->
                              <div>
                                <select v-model="locationOfUseId" @change="fetchAndFilterItems" class="ml-4 h-9 text-sm">
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

                              <!-- 保管場所のプルダウン -->
                              <div>
                                <select v-model="storageLocationId" @change="fetchAndFilterItems" class="ml-4 h-9 text-sm">
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


                          
                          <div class="mb-4 flex justify-end items-center">
                            <div class="font-medium">備品合計 {{ totalCount }}件</div>
                            <Pagination class="ml-4" :links="items.links"></Pagination>
                          </div>

                         
                          <!-- 行表示 -->
                          <div v-if="isTableView">
                            <div class="min-w-full overflow-auto">
                              <table class="table-fixed min-w-full text-left whitespace-no-wrap">
                                <thead>
                                  <tr>
                                    <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">復元</th>
                                    <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">履歴</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">管理ID</th>
                                    <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">登録日</th>
                                    <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">備品名</th>
                                    <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">画像</th>
                                    <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">カテゴリ</th>
                                    <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">在庫数</th>
                                    <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">利用状況</th>
                                    <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">使用者</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">利用場所</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">保管場所</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">取得区分</th>
                                    <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">取得先</th>
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
                                  <tr v-for="(item, index) in localItems.data" :key="item.id" class="item">
                                    <td>
                                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 12l-4-4-4 4M12 16V9"/></svg>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">
                                       <!-- マイクロモーダル -->
                                      <MicroModal v-bind:item="item" />
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">
                                      <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                        {{ item.management_id }}
                                      </Link>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.created_at }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3 max-w-full h-auto" :class="showDisposal ? 'bg-red-100' : ''"><img :src="item.image_path1" alt="画像" class=""></td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.category.name }}</td>
                                    <td class="text-right border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''"><span>{{ item.stock }}{{ item.unit.name }}</span></td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.usage_status.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.end_user }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.location_of_use.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.storage_location.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.acquisition_method.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.acquisition_source }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.price }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.date_of_acquisition }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.inspection_schedule }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.disposal_schedule }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.manufacturer }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.product_number }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3" :class="showDisposal ? 'bg-red-100' : ''">{{ item.remarks ?? '' }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>


                          <!-- タイル表示 -->
                          <div v-else>
                            <div class="flex flex-wrap -mx-4">
                              <template v-for="item in localItems.data" :key="item.id">
                                <div class="lg:w-1/5 w-1/2 p-4 border" :class="showDisposal ? 'bg-red-100' : ''">
                                  <div class="" >
                                    <a class="mb-2 block relative h-48">
                                      <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                        <img alt="ecommerce" class="object-cover object-center w-full h-full block" :src="item.image_path1">
                                      </Link>
                                    </a>
                                    <div class="flex items-end">
                                      <h3 class="ml-2 text-gray-500 text-xs tracking-widest title-font mb-1">{{ item.category.name }}</h3>
                                    </div>
                                    <div class="">
                                      <span class="text-gray-900 title-font font-medium">{{ item.management_id }}</span>
                                      <span class="ml-4 text-gray-900 title-font font-medium">{{ item.name }}</span>
                                    </div>
                                    <div class="flex">
                                      <Link as="button" :href="route('items.create')" class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-6 focus:outline-none hover:bg-gray-600 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        履歴
                                      </Link>
                                      <Link as="button" :href="route('items.show', { item: item.id })" class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-6 focus:outline-none hover:bg-blue-900 rounded">
                                        詳細を見る
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M12 5l7 7-7 7"/></svg>
                                      </Link>
                                    </div>
                                  </div>
                                </div>
                              </template>
                            </div>
                          </div>

                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="items.links"></Pagination>
                          </div>

                          <!-- テスト
                          <div>
                            <table>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id">
                                        <td>{{ item.management_id }}</td>
                                        <td>{{ item.created_at }}</td>
                                        <td>{{ item.name }}</td>
                                        <td><img :src="item.image_path1" alt="Item Image"></td>
                                        <td>{{ item.category.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> -->
                        
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
        background-color: black;
    }
</style>