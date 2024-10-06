<script setup>
import axios from 'axios';
import { ref, onMounted, watch, getCurrentInstance } from 'vue';

// 親コンポーネントから受け取る
const props = defineProps({
  item: Object
})

const item = ref(props.item);

// const isShow = ref(false)
// const toggleStatus = () => { isShow.value = !isShow.value}


// API通信で、where(item_id. item->id)でレコードを取ってくる
// APIのルート、StockTransactionのコントローラー
const stockTransactions = ref([])
const fetchStockTransactions = async item => {
  try {
    // await axios.get('api/stock_transactions', { item })
    const res = await axios.get(`/api/stock_transactions?item_id=${item.id}`)
    console.log('res.data')
    console.log(res.data)
    stockTransactions.value = res.data.stockTransactions
    console.log('stockTransactions')
    console.log(stockTransactions)
    
    // 明示的に更新
    // const instance = getCurrentInstance();
    // if (instance) {
    //   instance.proxy.$forceUpdate();
    // }

  } catch (e) {
    console.log('エラーメッセージです', e.message)
    if (e.response) {
      console.error('ステータスコード:', e.response.status);
      console.error('レスポンスデータ:', e.response.data);
    }
  }
}


// モーダルが開かなくてもonMountedが動いてしまう
onMounted(() => {
  console.log('StockHistoryModalのonMounted')
  console.log('props.item')
  console.log(props.item)
})

watch(() => props.item, (newItem) => {
  console.log('newItem')
  console.log(newItem)
  if (newItem) {
    fetchStockTransactions(newItem);
  }
}, { immediate: true });



const emit = defineEmits(['close'])
const closeModal = () => {
  // console.log('イベント打ち上げ')
  emit('close') // 閉じるイベント発火
}



// 日付フォーマット関数
const formatDate = (timestamp) => {
  const date = new Date(timestamp);
  const year = date.getFullYear();
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const day = ('0' + date.getDate()).slice(-2);
  const hours = ('0' + date.getHours()).slice(-2);
  const minutes = ('0' + date.getMinutes()).slice(-2);
  return `${year}/${month}/${day} ${hours}:${minutes}`;
}
</script>

<template>
  <div v-if="props.item" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container bg-white w-full md:w-11/12 lg:w-2/3 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0  transition-transform duration-500 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            <span class="text-sm lg:text-lg">【管理ID{{ props.item.management_id }}】{{ props.item.name }}の入出庫履歴</span>
          </h2>
          <button @click="closeModal" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="min-w-full overflow-auto">
            <!-- <div>{{ stockTransactions }}</div> -->
            <table v-if="stockTransactions.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="min-w-24 md:min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新日時</th>
                  <th class="min-w-24 md:min-w-12 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新区分</th>
                  <th class="min-w-20 md:min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">入庫数</th>
                  <th class="min-w-20 md:min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">出庫数</th>
                  <th class="min-w-24 md:min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">残在庫数</th>
                  <th class="min-w-28 md:min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">記録者</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="stockTransaction in stockTransactions" :key="stockTransaction.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.transaction_date }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.transaction_type }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.transaction_type === '入庫' ? stockTransaction.quantity : ''}}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.transaction_type === '出庫' ? stockTransaction.quantity : '' }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.current_stock }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm lg:text-base text-center">{{ stockTransaction.operator_name }}</td>
                </tr>
              </tbody>
            </table>
            <div v-else>
              <div class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div class="ml-2 text-center py-4 text-sm lg:text-base">入出庫履歴がありません</div>
              </div>
            </div>
          </div>
        </main>
        <footer class="modal__footer">
          <button @click="closeModal" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">
            <span class="text-xs md:text-sm">閉じる</span>
          </button>
        </footer>
      </div>
    </div>
  </div>
  <!-- item.idを親から子へ渡す、async await axiosの変数として渡される -->
  <!-- <button @click="fetchStockTransactions(props.item)" type="button" data-micromodal-trigger="modal-1"
  class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded">
    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
    履歴
  </button> -->
</template>