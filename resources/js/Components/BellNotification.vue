<script setup lang="ts">
import { BellIcon } from '@heroicons/vue/24/outline';
import { Bell } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

import type { PropType, Ref } from 'vue';
import type { NotificationType } from '@/@types/model';

// 通常のNavLinkの場合はリンクとして、ResponsiveNavLinkの場合はただのアイコンとして使用
defineProps({
  notifications: {
    type: Array as PropType<NotificationType[]>,
    required: true,
  },
  isLink: {
    type: Boolean,
    required: true,
  },
});
</script>

<template>
  <div class="relative" v-if="isLink">
    <Link as="button" :href="route('notifications.index')" class="flex">
      <Bell class="w-6 h-6 text-white" :stroke-width="2" />
      <span
        class="absolute top-0 right-0 text-xs bg-blue-500 text-white rounded-full w-4 h-4 flex items-center justify-center"
      >
        {{ notifications.length }}
      </span>
    </Link>
  </div>
  <div class="relative" v-else>
    <Bell class="w-6 h-6 text-gray-600" :stroke-width="2" />
    <span
      class="absolute top-0 right-0 text-xs bg-blue-500 text-white rounded-full w-4 h-4 flex items-center justify-center"
    >
      {{ notifications.length }}
    </span>
  </div>
</template>
