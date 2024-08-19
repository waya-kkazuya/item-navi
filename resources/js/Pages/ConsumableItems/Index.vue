<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, reactive } from 'vue';
import { stringify } from 'postcss';
import StockHistoryModal from '@/Components/StockHistoryModal.vue';
import UpdateStockModal from '@/Components/UpdateStockModal.vue';


const props = defineProps({
  consumableItems: Object,
  locations: Array,
  search: String,
  sortOrder: String,
  locationOfUseId: Number,
  storageLocationId: Number,
  totalCount: Number,
  userName: String,
  errors: Object
})


// 読み取り専用のitemsを変更出来るようスプレッド構文でコピーする
const localConsumableItems = ref({...props.consumableItems})
// 合計件数
const totalCount = ref(props.totalCount)

// 検索フォーム
const search = ref(props.search)
// 作成日でソート
const sortOrder = ref(props.sortOrder ?? 'asc')

// カテゴリプルダウン用(初期値は0)、更新したらその値
// コントローラーをまたいで
const locationOfUseId = ref(props.locationOfUseId ?? 0)
const storageLocationId = ref(props.storageLocationId ?? 0)



// すべてのフィルターをまとめる
const fetchAndFilterItems = () => {
  router.visit(route('consumable_items.index', {
    search: search.value,
    sortOrder: sortOrder.value,
    locationOfUseId: locationOfUseId.value,
    storageLocationId: storageLocationId.value,
  }), {
    method: 'get'
  })
}

// 
// 入出庫処理をした後に画面の情報を更新する
const fetchConsumableItems = async () => {
  try {
    console.log('emit通っている')
    const res = await axios.get('/api/consumable_items?reload=true');
    // 必要に応じてデータを更新
    console.log(res.data.items);
    console.log(res.data.total_count);
    localConsumableItems.value = res.data.items
    totalCount.value = res.data.total_count
    console.log(localConsumableItems.value)
    console.log(localConsumableItems.length)
  } catch (e){
    console.error('データの取得に失敗しました:', e.message);
  }
};


// const updateStock = (id) => {
//   router.visit(route('updateStock', { id: id }), {
//     method: 'put',
//     data: {
//       stockValue: stockValue.value,
//       action: action.value
//     }
//   })
// }

// 消耗品の在庫数更新用
// const state = reactive({
//   stockValue: 0,
//   action: 'out'
// })

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
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <div class="flex justify-between space-x-4">
                              

                              <div class="">
                                  <Link as="button" :href="route('items.create')" class="flex items-center text-white text-sm bg-green-500 border-0 py-2 px-6 focus:outline-none hover:bg-green-600 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    備品QRコ―ドをダウンロード
                                  </Link>
                              </div>
                            </div>

                            
                            <div class="flex justify-center items-center pl-4 mt-4 lg:w-2/3 w-full mx-auto">
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
                                <button @click="resetState" class="flex justify-center items-center w-32 h-9 p-2 ml-4 text-white bg-indigo-500 border-0 focus:outline-none hover:bg-indigo-600 rounded text-sm">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                  </svg>
                                  <div class="ml-2">リセット</div>
                                </button>
                              </div>

                            </div>
                          </div>
                          
                          <div class="mb-4 flex justify-end items-center">
                            <div class="font-medium">備品合計 {{ totalCount }}件</div>
                            <Pagination class="ml-4" :links="localConsumableItems.links"></Pagination>
                          </div>
                         

                          <!-- タイル表示 -->
                          <div>
                            <div v-if="localConsumableItems.data.length > 0" class="flex flex-wrap -mx-4">
                              <template v-for="item in localConsumableItems.data" :key="item.id">
                                <div class="lg:w-1/5 w-1/2 p-4 border">
                                  <div class="" >
                                    <a class="mb-2 block relative h-48">
                                      <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                        <img alt="ecommerce" class="object-cover object-center w-full h-full block" :src="item.image_path1">
                                      </Link>
                                    </a>
                                    <div class="">
                                      <span class="text-gray-900 title-font font-medium">{{ item.management_id }}</span>
                                      <span class="ml-4 text-gray-900 title-font font-medium">{{ item.name }}</span>
                                    </div>
                                    <div>
                                      <div>在庫数 {{ item.stock }} / 通知在庫数 {{ item.minimum_stock }}</div>
                                    </div>
                                    <div class="flex max-h-20">
                                      <!-- <Link as="button" :href="route('items.create')"
                                      class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded">
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        履歴
                                      </Link> -->
                                      <StockHistoryModal :item="item" />
                                      <UpdateStockModal :item="item" :userName="userName" :errors="errors" @fetch-consumableItems="fetchConsumableItems" />
                                      <!-- <button  type="button" @click="restoreItem(item.id)"
                                      class="flex items-center text-white text-sm bg-blue-800 border-0 py-2 px-4 focus:outline-none hover:bg-blue-900 rounded">
                                        入出庫する
                                        <svg class="w-6 h-6 size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                        </svg>
                                      </button> -->
                                    </div>
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