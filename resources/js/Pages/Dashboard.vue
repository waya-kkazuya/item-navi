<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';


defineProps({
    itemsByCategory: Object,
})


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ダッシュボード
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're logged in!
                    </div>
                    <div class="container">
                        <div v-for="(items, categoryName) in itemsByCategory" :key="categoryName">
                        <h2>Category: {{ categoryName }}</h2>
                        <ul>
                            <li v-for="item in items" :key="item.id">{{ item.name }}</li>
                        </ul>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
