<script setup lang="ts">
import { ref } from 'vue';

import type { Ref } from 'vue';

const props = defineProps({
  text: {
    type: String,
    required: true
  }
});

const show: Ref<boolean> = ref(false);
</script>

<template>
  <div class="tooltip-container" @mouseover="show = true" @mouseleave="show = false">
    <slot></slot>
    <div v-if="show" class="tooltip-content">
      {{ text }}
    </div>
  </div>
</template>

<style scoped>
.tooltip-container {
  position: relative;
  display: inline-block;
}

.tooltip-content {
  position: absolute;
  bottom: 125%; /* ツールチップの位置を調整 */
  left: 50%;
  transform: translateX(-50%);
  background-color: black;
  color: #fff;
  padding: 5px;
  border-radius: 5px;
  white-space: nowrap;
  z-index: 1;
}

.tooltip-container:hover .tooltip-content {
  visibility: visible;
}
</style>
