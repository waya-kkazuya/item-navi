<script setup lang="ts">
import axios from 'axios';
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import BellNotification from '@/Components/BellNotification.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

import type { Ref } from 'vue';
import type { NotificationType, UserType } from '@/@types/model';

const showingNavigationDropdown: Ref<boolean> = ref(false);

type AuthType = {
  user: UserType;
  user_role: number;
};

type PageType = {
  props: {
    auth: AuthType;
  };
};

const page: PageType = usePage();

const profileImageUrl: Ref<string> = ref('');
const notifications: Ref<NotificationType[]> = ref([]);

onMounted(async () => {
  // プロフィール画像URLの取得
  try {
    const profileRes = await axios.get('/api/profile-image');
    profileImageUrl.value = profileRes.data.profile_image_url;
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'AuthenticatedLayout.vue get profile_image_url',
    });
  }

  // 通知数の取得
  try {
    const notificationsRes = await axios.get('/api/notifications_count');
    notifications.value = notificationsRes.data;
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'AuthenticatedLayout.vue get notifications_count',
    });
  }
});
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100">
      <nav class="bg-blue-900 text-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('dashboard')">
                <ApplicationLogo class="block h-20 w-40 fill-current text-gray-800" />
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="items-center hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink v-if="page.props.auth.user_role <= 5" :href="route('items.index')"
                  :active="route().current('items.index')">
                  備品管理
                </NavLink>
                <NavLink :href="route('consumable_items')" :active="route().current('consumable_items')">
                  消耗品管理
                </NavLink>
                <NavLink v-if="page.props.auth.user_role <= 5" :href="route('inspection_and_disposal_items')"
                  :active="route().current('inspection_and_disposal_items')">
                  点検と廃棄
                </NavLink>
                <NavLink :href="route('item_requests.index')" :active="route().current('item_requests.index')">
                  リクエスト
                </NavLink>

                <!-- profile側にまとめるべきか -->
                <BellNotification
                  :notifications="notifications"
                  :isLink="true" 
                  v-if="page.props.auth.user_role <= 5"
                />
              </div>
            </div>


            <div class="hidden sm:flex sm:items-center sm:ms-6">
              <!-- Settings Dropdown -->
              <div class="ms-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        <img :src="profileImageUrl" alt="ProfileImage"
                          class="mr-4 w-9 h-9 rounded-full border border-black object-cover">

                        {{ page.props.auth.user.name }}

                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                          fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')"> プロフィール </DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">
                      ログアウト
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{
                    hidden: showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown,
                  }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{
                    hidden: !showingNavigationDropdown,
                    'inline-flex': showingNavigationDropdown,
                  }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <div class="pt-2 pb-3 space-y-1 bg-white">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
              ダッシュボード
            </ResponsiveNavLink>
            <ResponsiveNavLink v-if="page.props.auth.user_role <= 5" :href="route('items.index')"
              :active="route().current('items.index')">
              備品管理
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('consumable_items')" :active="route().current('consumable_items')">
              消耗品管理
            </ResponsiveNavLink>
            <ResponsiveNavLink v-if="page.props.auth.user_role <= 5" :href="route('inspection_and_disposal_items')"
              :active="route().current('inspection_and_disposal_items')">
              点検と廃棄
            </ResponsiveNavLink>
            <ResponsiveNavLink :href="route('item_requests.index')" :active="route().current('item_requests.index')">
              リクエスト
            </ResponsiveNavLink>
            <ResponsiveNavLink v-if="page.props.auth.user_role <= 5" :href="route('notifications.index')"
              :active="route().current('notifications.index')">
              <div class="flex">
                <div class="mr-2">通知</div>
                <BellNotification 
                  :notifications="notifications"
                  :isLink="false"
                  v-if="page.props.auth.user_role <= 5"
                  class="flex"
                />
              </div>
            </ResponsiveNavLink>
          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200 bg-white">
            <div class="space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                <div class="flex">
                  <div>
                    <img :src="profileImageUrl" alt="ProfileImage"
                      class="mr-4 w-9 h-9 rounded-full border border-black object-cover">
                  </div>
                  <div>
                    <div class="font-medium text-base text-gray-800">
                      {{ page.props.auth.user.name }}
                    </div>
                    <div class="font-medium text-sm text-gray-500">{{ page.props.auth.user.email }}</div>
                  </div>
                </div>
                <!-- Profile  -->
              </ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                ログアウト
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>

      <footer class="text-gray-400 text-center text-sm py-4">
        <p>&copy; 2024 waya. All rights reserved.</p>
        <p>
          Icons provided by
          <a href="https://heroicons.com/" target="_blank" class="text-blue-500">Heroicons</a>
          . Licensed under the MIT License.
        </p>
        <p>
          Fonts provided by
          <a href="https://fonts.google.com/" target="_blank" class="text-blue-500">Google Fonts</a>. Licensed under the
          <a href="https://scripts.sil.org/cms/scripts/page.php?item_id=OFL_web" target="_blank"
            class="text-blue-500">Open
            Font License</a>.
        </p>
      </footer>
    </div>
  </div>
</template>

<style scoped>
.border-black {
  border-color: #000;
}
</style>