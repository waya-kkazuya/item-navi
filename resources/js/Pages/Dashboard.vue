<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';


const props = defineProps({
    allItems: Array,
    itemsByType: Object,
    groupedEdithistories: Array,
    type: String
})

// 登録件数をプルダウンで切り替え
const type = ref(props.type ?? 'category')

const switchViewMode = () => {
  router.visit(route('dashboard', {
    type: type.value,
  }), {
    method: 'get'
  })
}
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
                    <div class="md:flex w-full p-4">
                        <div class="md:w-2/5 md:border-r border-gray-300 p-4">
                            <!-- 左側のテーブル -->
                            <table class="w-full border border-gray-200 text-xs md:text-base">
                                <thead>
                                    <tr>
                                        <th class="w-2/3 border-b-2 border-gray-200 px-4 py-3 text-left text-white bg-sky-700">
                                            備品の登録件数
                                        </th>
                                        <th class="w-1/3 border-b-2 border-gray-200 px-4 py-3 text-right bg-sky-700">
                                            <select v-model="type" @change="switchViewMode" class="h-9 text-xs md:text-base">
                                                <option value="category">カテゴリ
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </option>  
                                                <option value="locationOfUse">使用場所
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </option>  
                                            </select>
                                        </th>           
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="type == 'category'">
                                        <tr>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">
                                                <Link :href="route('items.index')" class="text-blue-500 hover:text-blue-700 underline block">
                                                    全体
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link :href="route('items.index')" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ allItems.length }}件
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-for="(group, categoryName) in itemsByType" :key="categoryName">
                                            <td class="border-b-2 border-gray-200 px-4 py-3">
                                                <Link :href="route('items.index', { categoryId: group.category_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                {{ categoryName }}
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link :href="route('items.index', { categoryId: group.category_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ group.items.length }}件
                                                </Link>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td class="border-b-2 border-gray-200 px-4 py-3">
                                                <Link :href="route('items.index')" class="text-blue-500 hover:text-blue-700 underline block">
                                                    全体
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link :href="route('items.index')" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ allItems.length }}件
                                                </Link>
                                            </td>
                                        </tr>
                                        <tr v-for="(group, locationOfUseName) in itemsByType" :key="locationOfUseName">
                                            <td class="border-b-2 border-gray-200 px-4 py-3">
                                                <Link :href="route('items.index', { locationOfUseId: group.location_of_use_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ locationOfUseName }}
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link :href="route('items.index', { locationOfUseId: group.location_of_use_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ group.items.length }}件
                                                </Link>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>


                        <!-- 右側のテーブル -->
                        <div class="w-full md:w-3/5 p-4">
                            <table class="w-full border border-gray-200 text-xs md:text-base">
                            <thead>
                                <tr>
                                    <th class="border-b-2 border-gray-200 px-4 py-3 text-left text-white bg-sky-700" colspan="2">備品の編集履歴</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template  v-for="(histories, date) in groupedEdithistories" :key="date">
                                    <tr>
                                        <td class="border-b-2 p-2 border-gray-200 text-left bg-gray-200" colspan="2">
                                            {{ date }} ({{ histories[0].day_of_week }})
                                        </td>
                                    </tr>
                                    <template v-for="history in histories" :key="history.id">
                                        <tr>
                                            <td class="border-b-2 p-2">{{ history.time }}</td>
                                            <td class="border-b-2 p-2">
                                                <template v-if="history.operation_type === 'soft_delete'">
                                                    【管理ID{{ history.item.management_id }}】{{ history.item.name }}
                                                </template>
                                                <template v-else>
                                                    <Link as="button" :href="route('items.show', { item: history.item_id })" class="text-blue-500 hover:text-blue-700 underline md:text-base">
                                                        【管理ID{{ history.item.management_id }}】{{ history.item.name }}
                                                    </Link>
                                                </template>
                                                を<span class="font-medium">{{ history.operation_description }}</span>しました
                                            </td>
                                        </tr>
                                        <tr v-if="history.edit_reason_id">
                                            <td></td>
                                            <td class="p-2">
                                                <div class="relative bg-indigo-50 md:text-base p-2 rounded">
                                                    <div class="arrow-up"></div>
                                                    <span>{{ history.edit_reason.reason }}</span>
                                                    <span v-if="history.edit_reason_text" class="ml-3">
                                                        {{ history.edit_reason_text }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.arrow-up {
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 5px solid #eef2ff; /* Tailwind's indigo-50 */
  position: absolute;
  top: -5px;
  left: 10px; /* Adjust as needed */
}
</style>