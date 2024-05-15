<script setup>
import { Chart, registerables } from "chart.js";
import { BarChart, LineChart } from "vue-chart-3";
import { reactive, computed } from "vue"

const props = defineProps({
  "data" : Object
})

const labels = computed(() => props.data.labels)
const stocks = computed(() => props.data.stocks)

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
})

</script>

<template>
  <div v-show="props.data">
    <LineChart :chartData="lineData" />
  </div>
  <!-- <div v-show="props.data">
    <BarChart :chartData="barData" />
  </div> -->
</template>