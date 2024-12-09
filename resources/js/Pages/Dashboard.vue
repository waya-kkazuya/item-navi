<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import type { Ref } from 'vue'
import type { ItemType, CategoryType, LocationType, EditHistoryType } from '@/@types/model';


type CategoryGroupedItems = {
    category_id: number;
    items: ItemType[];
}

type LocationGroupedItems = {
    location_of_use_id: number;
    items: ItemType[];
}

type ItemsByType = Record<string, CategoryGroupedItems | LocationGroupedItems>

type GroupedEditHistoriesType = Record<string, EditHistoryType[]>;

type Props = {
    allItems: ItemType[];
    itemsByType: ItemsByType;
    groupedEdithistories: GroupedEditHistoriesType;
    type: string;
}

const props = defineProps<Props>();

// 登録件数をプルダウンで切り替え
const type: Ref<string> = ref(props.type ?? 'category');

const switchViewMode = (): void => {
  router.visit(route('dashboard', {
    type: type.value,
  }), {
    method: 'get'
  })
};

// groupedEdithistoriesをローカル用にコピー
const localGroupedEdithistories: Ref<GroupedEditHistoriesType> = ref({});

onMounted(() => { 
    Object.keys(props.groupedEdithistories).forEach(date => { 
        localGroupedEdithistories.value[date] = props.groupedEdithistories[date].map(history => ({ 
            ...history, 
            showDetails: false // 初期状態では非表示 
        })); 
    }); 
});

// 編集履歴のアコーディオンパネルの開閉を切り替える関数 
const toggleDetails = (history: EditHistoryType) => {
    history.showDetails = !history.showDetails;
};
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
                                                <Link v-if="('category_id' in group)" :href="route('items.index', { categoryId: group.category_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ categoryName }}
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link v-if="('category_id' in group)" :href="route('items.index', { categoryId: group.category_id})" class="text-blue-500 hover:text-blue-700 underline block">
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
                                                <Link v-if="('location_of_use_id' in group)" :href="route('items.index', { locationOfUseId: group.location_of_use_id})" class="text-blue-500 hover:text-blue-700 underline block">
                                                    {{ locationOfUseName }}
                                                </Link>
                                            </td>
                                            <td class="border-b-2 border-gray-200 px-4 py-3 text-center">
                                                <Link v-if="('location_of_use_id' in group)" :href="route('items.index', { locationOfUseId: group.location_of_use_id})" class="text-blue-500 hover:text-blue-700 underline block">
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
                                    <template  v-for="(histories, date) in localGroupedEdithistories" :key="date">
                                        <tr>
                                            <td class="border-b-2 p-2 border-gray-200 text-left bg-gray-200" colspan="2">
                                                {{ date }} ({{ histories[0].day_of_week }})
                                            </td>
                                        </tr>
                                        <template v-for="history in histories" :key="history.id">
                                            <tr>
                                                <td class="border-b-2 p-2 align-top">{{ history.time }}</td>
                                                <td class="border-b-2 p-2">
                                                    <div class="flex items-center">
                                                        <button v-if="history.operation_type == 'update' && history.edit_reason.reason" @click="toggleDetails(history)" class="text-sm text-gray-500 ml-2">
                                                            <svg v-if="history.showDetails" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                        </button>
                                                        <div>
                                                            <span class="">{{ history.edit_user }}</span>が
                                                            <template v-if="history.operation_type === 'soft_delete'">
                                                                <span class="underline md:text-base">【管理ID{{ history.item.management_id }}】{{ history.item.name }}</span>
                                                            </template>
                                                            <template v-else>
                                                                <Link as="button" :href="route('items.show', { item: history.item_id })" class="text-blue-500 hover:text-blue-700 underline md:text-base">
                                                                    【管理ID{{ history.item.management_id }}】{{ history.item.name }}
                                                                </Link>
                                                            </template>
                                                            <span v-if="history.operation_type == 'update'">の{{ history.edited_field_for_display }}</span>
                                                            {{ history.operation_description }}
                                                        </div>
                                                    </div>
                                                    <!-- アコーディオンパネル部分 -->
                                                    <div v-if="history.showDetails && history.operation_type == 'update' && history.edit_reason.reason" class="relative bg-indigo-50 md:text-base p-2 mt-1 rounded">
                                                        <div class="arrow-up"></div>
                                                        <span class="font-semibold">編集理由 </span><span>{{ history.edit_reason.reason }}</span>
                                                        <span v-if="history.edit_reason_text" class="ml-3">
                                                            {{ history.edit_reason_text }}
                                                        </span>
                                                        <div><span class="font-semibold">編集後 </span>{{ history.new_value }}</div>
                                                        <div><span class="font-semibold">編集前 </span>{{ history.old_value }}</div>
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