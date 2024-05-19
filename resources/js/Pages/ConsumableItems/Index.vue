<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import { stringify } from 'postcss';

defineProps({
  consumableItems: Object,
})

// 検索用
const search = ref('')
// プルダウン用

// 表示切替用
const isTableView = ref('true')

// ハーフモーダル用
const isModalOpen = ref(false);
const modalItem = ref('');

const openModal = (item) => {
  modalItem.value = item
  isModalOpen.value = true
}


const quantity = ref(0);

// 消耗品の在庫数更新用
const newStockValue = ref('')

const updateStock = (id) => {
  router.visit(route('updateStock', { item: id }), {
    method: 'put',
    data: newStockValue.value
  })
}


// const fetchItems = () => {
//   router.visit(route('consumable_items.index', { search: search.value }), {
//     method: 'get'
//   })
// }

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
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <FlashMessage />
                            <div class="flex pl-4 mt-4 lg:w-2/3 w-full mx-auto">
                              <div class="flex items-center">
                                <input type="text" name="search" v-model="search" placeholder="備品名で検索">
                                <button class="bg-blue-300 text-white py-2 px-2" @click="fetchItems">検索</button>
                                </div>
                            </div>
                          </div>

                          <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="consumableItems  .links"></Pagination>
                          </div>

                          <div class="min-w-full overflow-auto">
                          <table class="table-fixed min-w-full text-left whitespace-no-wrap">
                            <thead>
                              <tr>
                                <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Id</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">備品名</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">カテゴリ</th>
                                <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">画像1パス</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">在庫数</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">入出庫</th>
                                <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">利用状況</th>
                                <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">使用者</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">利用場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">保管場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得区分</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得価額</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得年月日</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">点検予定日</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">廃棄予定日</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">メーカー</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">製品番号</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">購入先</th>
                                <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">備考</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="item in consumableItems.data" :key="item.id">
                                <td class="border-b-2 border-gray-200 px-4 py-3">
                                  <Link class="text-blue-400" :href="route('items.show', { item: item.id })">
                                    {{ item.id }}
                                  </Link>
                                </td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.category.name }}</td>
                                <td class="h-24 border-b-2 border-gray-200 px-4 py-3"><img :src="item.image_path1" alt="" class="h-full w-full"></td>
                                <td class="text-right border-b-2 border-gray-200 px-4 py-3"><span>{{ item.stocks }}</span></td>
                                <td class="border-b-2 border-gray-200 px-4 py-3"><button @click="openModal(item)" class="ml-2 p-2 text-white bg-gray-400 border-0 focus:outline-none hover:bg-gray-500 rounded">入出庫</button></td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.usage_status }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.end_user }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.location_of_use }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.storage_location }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.acquisition_category }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.date_of_acquisition }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.inspection_schedule }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.disposal_schedule }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.manufacturer }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.product_number }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3"><a :href="item.vendor_website_url" class="text-blue-500">{{ item.vendor }}</a></td>
                                <td class="overflow-hidden whitespace-nowrap border-b-2 border-gray-200 px-4 py-3">{{ item.remarks }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="mb-4 flex justify-end">
                            <Pagination class="mt-6" :links="consumableItems.links"></Pagination>
                          </div>
                      </section>
                    </div>
                </div>
              </div>
              <!-- ハーフモーダル -->
              <div class="h-screen flex items-center justify-center">
                <button @click="isModalOpen = true" class="px-4 py-2 bg-blue-500 text-white rounded">入出庫</button>
                <transition name="modal">
                    <div v-show="isModalOpen" class="fixed bottom-0 w-full bg-white p-4 shadow-lg modal-content">
                        <div>備品名：{{ modalItem.name }}</div>
                        <div>在庫数：{{ modalItem.stocks }}</div>
                        <h2 class="text-xl mb-2">入庫</h2><h2 class="text-xl mb-2">出庫</h2>
                        <p class="mb-4">いくつ入庫するか出庫するか入力してください。</p>
                        <input type="number" v-model="quantity" class="border p-2 mb-4 w-full">
                        <button>確定</button><button>キャンセル</button>
                        <div>
                          <button @click="isModalOpen = false" class="px-4 py-2 bg-red-500 text-white rounded">閉じる</button>
                        </div>
                    </div>
                </transition>
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