<script setup lang="ts">
import { Chart, registerables } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';
import { computed, reactive } from 'vue';
import { LineChart } from 'vue-chart-3';

import type { ComputedRef } from 'vue';
import type { ChartData, ChartOptions } from 'chart.js';
import type { GraphData } from '@/@types/graph-data';

Chart.register(...registerables);
Chart.register(annotationPlugin);

type Props = {
  graphData: GraphData;
};

const props = defineProps<Props>();

const labels: ComputedRef<string[]> = computed(() => props.graphData.labels);
const stocks: ComputedRef<number[]> = computed(() => props.graphData.stocks);
const transaction_types: ComputedRef<string[]> = computed(
  () => props.graphData.transaction_types || []
);
const minimum_stock: ComputedRef<number> = computed(() => props.graphData.minimum_stock);

const minimumStockvalue = minimum_stock.value;

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
      backgroundColor: 'rgb(75, 192, 192)',
      tension: 0.1,
      pointBackgroundColor: pointColors.value,
      pointBorderColor: pointColors.value,
    },
    {
      label: '通知在庫数',
      data: Array(labels.value.length).fill(minimumStockvalue), // 通知在庫数の水平線
      borderColor: 'rgba(255, 99, 132, 1)',
      borderWidth: 1,
      pointRadius: 0, // ポイントを非表示
      borderDash: [], // 点線
      fill: false,
    },
  ],
});

const options: ChartOptions<'line'> = reactive({
  maintainAspectRatio: false, //指定した高さに収まるようにする
  scales: {
    y: {
      min: 0, // 縦軸の最小値を0に固定
      ticks: {
        callback: function (value) {
          //整数ならティック値＝メモリの数を返す
          return Number.isInteger(value) ? value : null;
        },
      },
    },
  },
  plugins: {
    legend: {
      display: false, //凡例を非表示
    },
    annotation: {
      annotations: {
        line1: {
          type: 'line',
          yMin: minimumStockvalue, // 通知在庫数
          yMax: minimumStockvalue, // 通知在庫数
          borderColor: 'rgba(255, 99, 132, 1)', // 黄色
          borderWidth: 1,
          label: {
            content: `通知在庫数: ${minimumStockvalue}`,
            enabled: true,
            position: 'start',
            backgroundColor: 'rgba(255, 99, 132, 1)',
            // yAdjust: 10, // ラベルの位置を調整
            // xAdjust: -10
          },
        },
      },
    },
  },
});
</script>

<template>
  <div v-show="props.graphData" class="min-w-full">
    <LineChart :chartData="lineData" :options="options" />
  </div>
</template>
