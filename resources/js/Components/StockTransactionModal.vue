<script setup lang="ts">
import axios from 'axios';
import { reactive, ref, watch } from 'vue';
import Chart from '@/Components/Chart.vue';
import type { Ref } from 'vue';
import type { ItemType, StockTransactionType } from '@/@types/model';
import type { GraphData } from '@/@types/graph-data';

// 親コンポーネントから受け取る
type Props = {
  item: ItemType;
};

const props = defineProps<Props>();

const item: Ref<ItemType> = ref(props.item);

const stockTransactions: Ref<StockTransactionType[]> = ref([]);

const graphData = reactive({} as GraphData);

// 入出庫履歴情報を取得
const fetchStockTransactions = async (item: ItemType): Promise<void> => {
  try {
    const res = await axios.get(`/api/stock_transactions?item_id=${item.id}`);
    stockTransactions.value = res.data.stockTransactions;
    graphData.labels = res.data.labels;
    graphData.stocks = res.data.stocks;
    graphData.transaction_types = res.data.transaction_types;
    graphData.minimum_stock = res.data.minimum_stock;
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'StockTransactionModal.vue fetchStockTransactions method',
    });
  }
};

// 別な備品Itemが選択された問いに
watch(() => props.item, (newItem: ItemType) => {
  if (newItem) {
    fetchStockTransactions(newItem);
  }
}, { immediate: true });

const emit = defineEmits<{(e: 'stockTransactionModalClosed') : void}>();
const closeModal = (): void => {
  emit('stockTransactionModalClosed') // StockTransactionModalを閉じるイベント打ち上げ
};
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
            <div v-if="stockTransactions.length > 0" class="mb-2">
              <Chart :graphData="graphData" />
            </div>
            <table v-if="stockTransactions.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="min-w-24 md:min-w-44 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新日時</th>
                  <th class="min-w-24 md:min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新区分</th>
                  <th class="min-w-20 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">入庫数</th>
                  <th class="min-w-20 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">出庫数</th>
                  <th class="min-w-20 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">修正数</th>
                  <th class="min-w-24 md:min-w-20 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">在庫数</th>
                  <th class="min-w-28 md:min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">記録者</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="stockTransaction in stockTransactions" :key="stockTransaction.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.formatted_created_at }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.transaction_type }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.transaction_type === '入庫' ? stockTransaction.quantity : ''}}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.transaction_type === '出庫' ? stockTransaction.quantity : '' }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.transaction_type === '修正' ? stockTransaction.quantity : '' }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.current_stock }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ stockTransaction.operator_name }}</td>
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
</template>