<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  imageTests: Array,
})


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <div v-for="item in imageTests" :key="item.id">
                        <div>{{ item.name }}</div>
                        <div>{{ item.file_name }}</div>
                        <img :src="item.file_name">
                      </div>  
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
