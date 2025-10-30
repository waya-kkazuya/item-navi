<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

type SharedProps = {
  assets: {
    logo: string;
  };
};

const page = usePage<SharedProps>();
const logoUrl = computed(() => page.props.assets.logo);
</script>

<template>
  <div>
    <img :src="logoUrl" alt="アプリケーションロゴ" />
  </div>
</template>
