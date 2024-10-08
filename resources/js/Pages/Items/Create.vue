<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, watch, reactive, ref } from 'vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({
    categories: Array,
    locations: Array,
    units: Array,
    usageStatuses: Array,
    acquisitionMethods: Array,
    name: String,
    category_id: Number,
    location_of_use_id: Number,
    manufacturer: String,
    price: Number,
    errors: Object
})


const form = useForm({
    id: null, //
    image_file: null,
    name: props.name ?? null,
    category_id: props.category_id ?? 0, 
    //   image1: null, //保存する際は画像名 
    stock: 1, // 最初から1を入力しておく
    unit_id: 1,
    minimum_stock: 0,
    notification: true,
    usage_status_id: 0,
    end_user: null,
    location_of_use_id: props.location_of_use_id ?? 0, // locationsテーブルで1は「未選択」
    storage_location_id: 0, // locationsテーブルで1は「未選択」
    acquisition_method_id: 0,
    acquisition_source: null,
    price: props.price ?? 0,
    date_of_acquisition: new Date().toISOString().substr(0, 10),
    manufacturer: props.manufacturer ?? null,
    product_number: null,
    inspection_scheduled_date: null, // 初期値はnull
    disposal_scheduled_date: null, // 初期値はnull
    remarks: null,
})


const file_preview_src = ref('')

const handleFileUpload = (event) => {
    form.image_file = event.target.files[0];
    // ブラウザ限定の画像プレビュー用のURL生成
    if (form.image_file) {
        file_preview_src.value = URL.createObjectURL(form.image_file);
        console.log(file_preview_src.value)
    }
};

// router.visitではuseFormの入力値保持機能は使えない
// form.postなら入力値保持機能(old関数))が使える
const storeItem = () => {
  form.post('/items', {
    // onError: (errors) => {
    //   form.errors = errors
    // }
  })
}


// 「×」ボタンで日付リセット
const clearDateOfAcquisition = () => {
    form.date_of_acquisition = '';
};
const clearInspectionSchedule = () => {
    form.inspection_schedule_date = '';
};
const clearDisposalSchedule = () => {
    form.disposal_scheduled_date = '';
};

</script>

<template>
    <Head title="備品新規登録" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                備品新規登録
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- ここから白い背景 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">
                            <FlashMessage />
                            <form @submit.prevent="storeItem" enctype="multipart/form-data">
                                <div class="container px-5 py-8 mx-auto">                                    
                                    <div class="md:w-full mx-auto">
                                        <div class="-m-2">
                                            <div class="grid grid-cols-1 md:grid-cols-3 md:gap-4">

                                                <div class="col-span-1">
                                                    <div class="p-4 border mb-8 md:mb-0 flex justify-center">

                                                        <div class="p-2 w-full">
                                                            <label for="imageFile" class="leading-7 text-xs md:text-base text-blue-900">
                                                                画像
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <label for="imageFile" class="cursor-pointer md:flex">
                                                                <input type="file" @change="handleFileUpload" multiple accept="image/png, image/jpeg, image/jpg" id="imageFile" name="imageFile" class="sr-only">
                                                                <div class="md:mt-1 w-48 h-48 md:w-40 md:h-40 border border-gray-300 rounded-md shadow-sm flex items-center justify-center">
                                                                    <template v-if="file_preview_src">
                                                                        <img :src=file_preview_src alt="画像プレビュー" class="w-full h-full object-cover rounded-md">
                                                                    </template>
                                                                    <template v-else>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                                        </svg>
                                                                    </template>
                                                                </div>     
                                                            </label>    
                                                            <div v-if="errors.image_file" class="font-medium text-red-600 text-xs md:text-base">{{ errors.image_file }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-span-2">
                                                    <div class="p-4 border mb-4">
                                                        <div class="p-2 w-full">
                                                            <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                                                                備品名 
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <input type="text" id="name" name="name" v-model="form.name" class="block md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.name" class="font-medium text-red-600 text-xs md:text-base">{{ errors.name }}</div>
                                                        </div>
                                                        <div class="p-2 w-full">
                                                            <label for="category_id" class="leading-7 text-xs md:text-base text-blue-900">
                                                                カテゴリ
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label><br>
                                                            <select name="category_id" id="category_id" v-model="form.category_id" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>  
                                                                <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                            </select>
                                                            <div v-if="errors.category_id" class="font-medium text-red-600 text-xs md:text-base">{{ errors.category_id }}</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="p-4 border mb-4">
                                                        <div class="pl-2 w-full">
                                                            <label for="stock" class="leading-7 text-xs md:text-base text-blue-900">
                                                                在庫数
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label><br>
                                                            <input type="number" id="stock" name="stock" v-model="form.stock" min="0" class="md:mt-1 w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <select name="unit" id="unit" v-model="form.unit_id" class="md:w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>
                                                            </select>
                                                            <div v-if="errors.stock" class="font-medium text-red-600 text-xs md:text-base">{{ errors.stock }}</div>
                                                            <div v-if="errors.unit" class="font-medium text-red-600 text-xs md:text-base">{{ errors.unit }}</div>
                                                        </div>
                                                        <div v-show="form.category_id == 1" class="mt-4 pl-2 w-full">
                                                            <label for="minimum_stock" class="leading-7 text-xs md:text-base text-blue-900">
                                                                通知在庫数
                                                            </label><br>
                                                            <input type="number" id="minimum_stock" name="minimum_stock" v-model="form.minimum_stock" min="0" class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <select name="unit" id="unit" v-model="form.unit_id" class="md:w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>  
                                                            </select>
                                                            <div v-if="errors.minimum_stock" class="font-medium text-red-600 text-xs md:text-base">{{ errors.minimum_stock }}</div>
                                                        </div>
                                                        <div v-show="form.category_id == 1" class="mt-4 pl-2 w-full">
                                                            <input type="checkbox" id="notification" v-model="form.notification">
                                                            <label for="notification" class="ml-1 text-xs md:text-base">
                                                                在庫数が通知在庫数以下になったら通知する
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border mb-4">
                                                        <div class="p-2 w-full">
                                                            <label for="usage_status_id" class="leading-7 text-xs md:text-base text-blue-900">
                                                                利用状況
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <select name="usage_status_id" id="usage_status_id" v-model="form.usage_status_id" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="usageStatus in usageStatuses" :value="usageStatus.id" :key="usageStatus.id">{{ usageStatus.name }}</option>
                                                            </select>
                                                            <div v-if="errors.usage_status_id" class="font-medium text-red-600 text-xs md:text-base">{{ errors.usage_status_id }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="end_user" class="leading-7 text-xs md:text-base text-blue-900">
                                                                使用者
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <input type="text" id="end_user" name="end_user" v-model="form.end_user" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.end_user" class="font-medium text-red-600">{{ errors.end_user }}</div>       
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="location_of_use_id" class="leading-7 text-xs md:text-base text-blue-900">
                                                                利用場所
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <select name="location_of_use_id" id="location_of_use_id" v-model="form.location_of_use_id" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.location_of_use_id" class="font-medium text-red-600 text-xs md:text-base">{{ errors.location_of_use_id }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="storage_location_id" class="leading-7 text-xs md:text-base text-blue-900">
                                                                保管場所
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <select name="storage_location_id" id="storage_location_id" v-model="form.storage_location_id" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.storage_location_id" class="font-medium text-red-600 text-xs md:text-base">{{ errors.storage_location_id }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border mb-4">
                                                        <div class="p-2 w-full">
                                                            <label for="acquisition_method_id" class="leading-7 text-xs md:text-base text-blue-900">
                                                                取得区分
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <select name="acquisition_method_id" id="acquisition_method_id" v-model="form.acquisition_method_id" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="acquisitionMethod in acquisitionMethods" :key="acquisitionMethod.id" :value="acquisitionMethod.id">{{ acquisitionMethod.name }}</option>
                                                            </select>
                                                            <div v-if="errors.acquisition_method_id" class="font-medium text-red-600 text-xs md:text-base">{{ errors.acquisition_method_id }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="acquisition_source" class="leading-7 text-xs md:text-base text-blue-900">
                                                                取得先
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <input type="text" id="acquisition_source" name="acquisition_source" v-model="form.acquisition_source" placeholder="例 Amazonなど" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.acquisition_source" class="font-medium text-red-600 text-xs md:text-base">{{ errors.acquisition_source }}</div>
                                                        </div>


                                                        <div class="p-2 w-full">
                                                            <label for="price" class="leading-7 text-xs md:text-base text-blue-900">
                                                                取得価額
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <input type="number" id="price" name="price" v-model="form.price" min="0" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.price" class="font-medium text-red-600 text-xs md:text-base">{{ errors.price }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="date_of_acquisition" class="leading-7 text-xs md:text-base text-blue-900">
                                                                取得年月日
                                                                <span class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">必須</span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="date" id="date_of_acquisition" name="date_of_acquisition" v-model="form.date_of_acquisition" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearDateOfAcquisition" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500" text-lg>×</button>
                                                            </div>
                                                            <div v-if="errors.date_of_acquisition" class="font-medium text-red-600 text-xs md:text-base">{{ errors.date_of_acquisition }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border mb-4">
                                                        <div class="p-2 w-full">
                                                            <label for="manufacturer" class="leading-7 text-xs md:text-base text-blue-900">
                                                                メーカー
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.manufacturer" class="font-medium text-red-600 text-xs md:text-base">{{ errors.manufacturer }}</div>
                                                        </div>
                                                        
                                                        <div class="p-2 w-full">
                                                            <label for="product_number" class="leading-7 text-xs md:text-base text-blue-900">
                                                                製品番号
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <input type="text" id="product_number" name="product_number" v-model="form.product_number" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.product_number" class="font-medium text-red-600 text-xs md:text-base">{{ errors.product_number }}</div>
                                                        </div>

                                                    
                                                        <div class="p-2 w-full">
                                                            <label for="remarks" class="leading-7 text-xs md:text-base text-blue-900">
                                                                備考
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <textarea id="remarks" name="remarks" maxlength="500" v-model="form.remarks" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-xs md:text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                            <div v-if="errors.remarks" class="font-medium text-red-600 text-xs md:text-base">{{ errors.remarks }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="inspection_schedule_date" class="leading-7 text-xs md:text-base text-blue-900">
                                                                点検予定日
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="date" id="inspection_schedule_date" name="inspection_schedule_date" v-model="form.inspection_schedule_date" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearInspectionSchedule" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500">×</button>
                                                            </div>
                                                            <div v-if="errors.inspection_schedule_date" class="font-medium text-red-600 text-xs md:text-base">{{ errors.inspection_schedule_date }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="disposal_scheduled_date" class="leading-7 text-xs md:text-base text-blue-900">
                                                                廃棄予定日
                                                                <span class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md">任意</span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="date" id="disposal_scheduled_date" name="disposal_scheduled_date" v-model="form.disposal_scheduled_date" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearDisposalSchedule" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500">×</button>
                                                            </div>
                                                            <div v-if="errors.disposal_scheduled_date" class="font-medium text-red-600 text-xs md:text-base">{{ errors.disposal_scheduled_date }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="w-full">
                                                        <button id="item_register" class="flex mx-auto text-white text-xs md:text-base bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded">
                                                            登録する
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>                      
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
