<script setup lang="ts">
import { Chart, registerables } from "chart.js";
import { LineChart } from "vue-chart-3";
import { reactive, computed } from "vue";
import type { ComputedRef } from "vue";
import type { ChartData, ChartOptions } from "chart.js";
import type { GraphData } from "@/@types/graph-data";


Chart.register(...registerables);

type Props = {
  graphData: GraphData;
};

const props = defineProps<Props>();

const labels: ComputedRef<string[]> = computed(() => props.graphData.labels);
const stocks: ComputedRef<number[]> = computed(() => props.graphData.stocks);
const transaction_types: ComputedRef<string[]> = computed(() => props.graphData.transaction_types || []);

const pointColors: ComputedRef<string[]> = computed(() => { 
  return transaction_types.value.map((type) => {
    return type === '修正' ? 'rgba(255, 206, 86, 1)' : 'rgba(54, 162, 235, 1)'; 
  }); 
});

const lineData: ChartData<'line'> = reactive({
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

const options: ChartOptions<'line'> = reactive({
  maintainAspectRatio: false, //指定した高さに収まるようにする
  scales: { 
    y: {
      min: 0, // 縦軸の最小値を0に固定
    }
  }
});
</script>

<template>
  <div v-show="props.graphData" class="min-w-full">
    <LineChart :chartData="lineData" :options="options" />
  </div>
</template>