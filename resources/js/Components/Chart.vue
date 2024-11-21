<script setup lang="ts">
import { Chart, registerables } from "chart.js";
import { LineChart } from "vue-chart-3";
import { reactive, computed } from "vue";


const props = defineProps({
  graphData: Object
});

const labels = computed(() => props.graphData.labels);
const stocks = computed(() => props.graphData.stocks);
const transaction_types = computed(() => props.graphData.transaction_types);

const pointColors = computed(() => { 
  return transaction_types.value.map((type) => {
    return type === '修正' ? 'rgba(255, 206, 86, 1)' : 'rgba(54, 162, 235, 1)'; 
  }); 
});

Chart.register(...registerables);

const lineData = reactive({
labels: labels,
  datasets: [
    {
      label: '在庫数',
      data: stocks,
      backgroundColor: "rgb(75, 192, 192)",
      tension: 0.1,
      pointBackgroundColor: pointColors.value,
      pointBorderColor: pointColors.value
    }
  ]
});

const options = reactive({
  maintainAspectRatio: false, //指定した高さに収まるようにする
  scales: { 
    y: {
      min: 0 // 縦軸の最小値を0に固定
    } 
  }
});
</script>

<template>
  <div v-show="props.graphData" class="min-w-full">
    <LineChart :chartData="lineData" :options="options" />
  </div>
</template>