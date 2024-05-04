<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';


defineProps({
    item: Object
})

const deleteItem = id => {
    router.visit(route('items.destroy', { item: id }), {
        method: 'delete',
        onBefore: visit => confirm('本当に削除しますか？')
        // onBefore: () => confirm('本当に削除しますか？')
    })
}
</script>

<template>
    <Head title="備品詳細" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                備品詳細
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">

                            <div class="container px-5 py-8 mx-auto">
                                                     
                                <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                    <div class="-m-2">

                                        <div class="p-4 border bordr-4 mb-8">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                                                    <div id="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        {{ item.name }}
                                                    </div>
                                                </div>                                                
                                            </div>
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="category" class="leading-7 text-sm text-gray-600">カテゴリ</label><br>
                                                    <div id="category" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        {{ item.category }}
                                                    </div>
                                            </div>
                                            </div>

                                            <div class="flex justify-around">
                                                <div v-if="item.image_path1" class="p-2 w-full">
                                                    <div class="relative">
                                                    <label for="image_path1" class="leading-7 text-sm text-gray-600">写真データ1</label>
                                                    <img  :src="item.image_path1" alt="Image preview...">
                                                    </div>
                                                </div>
                                                <div v-if="item.image_path2" class="p-2 w-full">
                                                    <div class="relative">
                                                    <label for="image_path2" class="leading-7 text-sm text-gray-600">写真データ2</label>
                                                    <img :src="item.image_path2" alt="Image preview...">
                                                    </div>
                                                </div>
                                                <div v-if="item.image_path3" class="p-2 w-full">
                                                    <div class="relative">
                                                    <label for="image_path3" class="leading-7 text-sm text-gray-600">写真データ3</label>
                                                    <img :src="item.image_path3" alt="Image preview...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                            <div class="p-2 w-full mb-8">
                                                <div class="relative">
                                                    <label for="stocks" class="leading-7 text-sm text-gray-600">在庫数</label><br>
                                                    <div class="flex items-center">
                                                        <div id="stocks"  class="w-1/4 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            {{ item.stocks }}
                                                        </div>
                                                        <span class="ml-1 leading-7 text-sm text-gray-600">個</span>
                                                    </div>
                                                </div>
                                            </div>

                                        <div class="p-4 border bordr-4 mb-8">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="usage_status" class="leading-7 text-sm text-gray-600">利用状況</label>
                                                    <div id="usage_status" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.usage_status }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="end_user" class="leading-7 text-sm text-gray-600">使用者</label>
                                                    <div id="end_user" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.end_user }}  
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="location_of_use" class="leading-7 text-sm text-gray-600">設置場所</label>
                                                    <div id="location_of_use" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.location_of_use }}  
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="storage_location" class="leading-7 text-sm text-gray-600">保管場所</label>
                                                    <div id="storage_location" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.storage_location }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 border bordr-4 mb-8">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="acquisition_category" class="leading-7 text-sm text-gray-600">取得区分</label>
                                                    <div id="acquisition_category" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.acquisition_category }}  
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="price" class="leading-7 text-sm text-gray-600">取得価額</label>
                                                    <div id="price" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.price }}  
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="date_of_acquisition" class="leading-7 text-sm text-gray-600">取得年月日</label>
                                                    <div id="date_of_acquisition" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.date_of_acquisition }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 border bordr-4 mb-8">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="inspection_schedule" class="leading-7 text-sm text-gray-600">点検時期</label>
                                                    <div id="inspection_schedule" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.inspection_schedule }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="disposal_schedule" class="leading-7 text-sm text-gray-600">廃棄時期</label>
                                                    <div id="disposal_schedule" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.disposal_schedule }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 border bordr-4 mb-4">
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="manufacturer" class="leading-7 text-sm text-gray-600">メーカー</label>
                                                    <div id="manufacturer" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.manufacturer }}
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="product_number" class="leading-7 text-sm text-gray-600">製品番号</label>
                                                    <div id="product_number" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.product_number }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="vendor" class="leading-7 text-sm text-gray-600">購入先</label>
                                                    <div id="vendor" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    {{ item.vendor }}
                                                    </div>
                                                    <div class="mt-4">
                                                    <a id="vendor_website_url" :href="item.vendor_website_url" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
                                                        購入サイトへ
                                                    </a>
                                                    </div>
                                                </div>
                                            </div>               
                                    
                                            <div class="p-2 w-full">
                                                <div class="relative">
                                                    <label for="remarks" class="leading-7 text-sm text-gray-600">備考</label>
                                                    <div id="remarks" class="whitespace-pre-wrap w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out">
                                                        {{ item.remarks }}
                                                    </div>
                                                    <!-- <div id="remarks" v-html="nl2br(item.remarks)" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out">
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-2 w-full">
                                            <Link as="button" :href="route('items.edit', { item: item.id })" class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">編集する</Link>
                                        </div>
                                        <div class="mt-8 p-2 w-full">
                                            <button @click="deleteItem(item.id)" class="flex mx-auto text-white bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded text-lg">削除する</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
