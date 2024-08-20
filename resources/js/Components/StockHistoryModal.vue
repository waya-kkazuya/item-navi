<script setup>
import axios from 'axios';
import { ref, reactive, onMounted } from 'vue';

// 親コンポーネントから受け取る
const props = defineProps({
  item: Object
})

const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}

// stockTransactions: Array,
// API通信で、where(item_id. item->id)でレコードを取ってくる
// APIのルート、StockTransactionのコントローラー
const stockTransactions = ref([])
const fetchStockTransactions = async item => {
  
  try {
    // await axios.get('api/stock_transactions', { item })
    await axios.get(`api/stock_transactions/?item_id=${item.id}`)
    .then( res => {
      console.log(res.data)
      stockTransactions.value = res.data
      stockTransactions.value = res.data.stockTransactions
    })
    toggleStatus()
  } catch (e) {
    console.log('エラーメッセージです', e.message)
  }
};

onMounted(() => {
  console.log(props.item)
})

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
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container w-2/3" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            【管理ID{{ props.item.management_id }}】{{ props.item.name }}の編集履歴
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="min-w-full overflow-auto">
            <table class="table-fixed min-w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新日時</th>
                  <th class="min-w-12 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新区分</th>
                  <th class="min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">入庫数</th>
                  <th class="min-w-8 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">出庫数</th>
                  <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">在庫数</th>
                  <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">記録者</th>
                </tr>
              </thead>
              <tbody>
                <tr  v-for="stockTransaction in stockTransactions" :key="stockTransaction.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.transaction_date }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.transaction_type }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.transaction_type === '入庫' ? stockTransaction.quantity : ''}}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.transaction_type === '出庫' ? stockTransaction.quantity : '' }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.current_stock }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ stockTransaction.operator_name }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
        <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">閉じる</button>
        </footer>
      </div>
    </div>
  </div>
  <!-- item.idを親から子へ渡す、async await axiosの変数として渡される -->
  <button @click="fetchStockTransactions(props.item)" type="button" data-micromodal-trigger="modal-1"
  class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded">
    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
    履歴
    </button>
</template>