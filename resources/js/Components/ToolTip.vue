<script setup>
import { ref } from 'vue';

const isTooltipVisible = ref(false);

const toggleTooltip = () => {
  isTooltipVisible.value = !isTooltipVisible.value;
};

const hideTooltip = () => {
  isTooltipVisible.value = false;
};

</script>

<template>
  <div class="tooltip" @click="toggleTooltip" @mouseleave="hideTooltip">
    Hover or Tab over me
    <span class="tooltiptext" :class="{ visible: isTooltipVisible }">Tooltip text</span>
  </div>
</template>

<style scoped>
.tooltip {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  position: absolute;
  z-index: 1;
  bottom: 125%; /* ツールチップの位置 */
  left: 50%;
  margin-left: -60px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
</style>
