<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

type FlashMessage = {
  status: 'success' | 'danger';
  message: string;
};

type PageProps = {
  flash: FlashMessage;
}

const page = usePage<PageProps>();
</script>

<template>
  <div id="flash-message">
    <div v-if="page.props.flash.status === 'success'" class="bg-blue-300 text-xs md:text-base text-white p-4">
      {{ page.props.flash.message }}
    </div>
    <div v-if="page.props.flash.status === 'danger'" class="bg-red-300 text-xs md:text-base text-white p-4">
      {{ page.props.flash.message }}
    </div>
  </div>
</template>
