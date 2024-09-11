<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({
  imageTests: Array,
})

const deleteItem = id => {
    router.visit(route('image_tests.destroy', { image_test: id }), {
        method: 'delete',
        onBefore: visit => confirm('本当に削除しますか？')
    })
}


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <FlashMessage />
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <Link as="button" :href="route('image_tests.create')" class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">新規登録</Link>
                      <div class="flex flex-wrap">
                        <div v-for="item in imageTests" :key="item.id" class="p-4 border">
                          <!-- <div>{{ item.file_name }}</div> -->
                          <div class="relative w-32">
                            <img :src="item.file_name">
                            <button class="absolute top-0 right-0 bg-gray-500 text-white py-1 px-2" @click="deleteItem(item.id)">
                              ×
                            </button>
                          </div>
                          <!-- 今回はshowコンポーネントは省略 -->
                          <Link class="text-blue-400" :href="route('image_tests.edit', { image_test: item.id })">
                            {{ item.id }}
                          </Link>
                          <div class="text-gray-700">{{ item.name }}</div>
                        </div> 
                      </div> 
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
