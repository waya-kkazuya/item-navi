<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

import type { ValidationErrors } from '@/@types/types';

defineProps<{
  mustVerifyEmail: boolean;
  status: string;
  profile_image_path: string;
  errors: ValidationErrors;
}>();
</script>

<template>
  <Head title="Profile" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">プロフィール</h2>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">     
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
          <UpdateProfileInformationForm
            :must-verify-email="mustVerifyEmail"
            :status="status"
            :profile_image_path="profile_image_path"
            :errors="errors"
            class="max-w-xl"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
