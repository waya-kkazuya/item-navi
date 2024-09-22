<script setup>
import { ref, computed } from 'vue';
import { useWindowSize } from '@/hooks/useWindowSize';
import IndexMobile from './IndexMobile.vue';
import IndexDesktopTablet from './IndexDesktopTablet.vue';

const { width } = useWindowSize();
const isMobile = computed(() => width.value < 768);
</script>

<template>
  <div>
    <IndexMobile v-if="isMobile" />
    <IndexDesktopTablet v-else />
  </div>
</template>


