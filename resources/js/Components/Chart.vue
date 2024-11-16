<script setup lang="ts">
import { Chart, registerables } from "chart.js";
import { BarChart, LineChart } from "vue-chart-3";
import { reactive, computed } from "vue"

const props = defineProps({
  data : Object
});

const labels = computed(() => props.data.labels);
const stocks = computed(() => props.data.stocks);

Chart.register(...registerables);
const lineData = reactive({
  labels: labels,
  datasets: [
    {
      label: '在庫数',
      data: stocks,
      backgroundColor: "rgb(75, 192, 192)",
      tension: 0.1,
    }
  ],
  options: {
    scales: {
      y: {
        min: 0, // y軸の最小値を0に設定
      }
    },
    responsive: true, // レスポンシブにする
    maintainAspectRatio: false, // アスペクト比を維持しない（これにより、グラフの大きさを固定できます）
  }
});

</script>

<template>
  <div v-show="props.data">
    <LineChart :chartData="lineData" :options="lineData.options" />
  </div>
</template>