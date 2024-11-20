<script setup lang="ts">
import { Chart, registerables } from "chart.js";
import { BarChart, LineChart } from "vue-chart-3";
import { reactive, computed } from "vue";


const props = defineProps({
  graphData: Object
});

const labels =computed(() => props.graphData.labels);
const stocks =computed(() => props.graphData.stocks);

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
  ]
});
</script>

<template>
  <div v-show="props.graphData" class="min-w-full">
    <LineChart :chartData="lineData" />
  </div>
</template>