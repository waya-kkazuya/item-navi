<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';

const props = defineProps({
    item: Object,
    pendingInspection: Object,
    categories: Array,
    locations: Array,
    units: Array,
    usageStatuses: Array,
    acquisitionMethods: Array,
    editReasons: Array,
    errors: Object
})

const form = useForm({
    id: props.item.id,
    imageFile: null,
    // image_path1: props.item.image_path1, //画像を更新できるようにするか、まずはCreateの方で「×」ボタンでキャンセル機能を実装
    name: props.item.name,
    categoryId: props.item.category_id,
    stock: props.item.stock,
    unitId: props.item.unit_id,
    minimumStock: props.item.minimum_stock,
    notification: props.item.notification,
    usageStatusId: props.item.usage_status_id,
    endUser: props.item.end_user,
    locationOfUseId: props.item.location_of_use_id,
    storageLocationId: props.item.storage_location_id,
    acquisitionMethodId: props.item.acquisition_method_id,
    acquisitionSource: props.item.acquisition_source,
    price: props.item.price,
    dateOfAcquisition: props.item.date_of_acquisition,
    manufacturer: props.item.manufacturer,
    productNumber: props.item.product_number,
    inspectionSchedule: props.pendingInspection ? props.pendingInspection.scheduled_date : null,
    pendingInspection: props.pendingInspection, // pendingInspectionにオブジェクトを入れる,idが取得できる
    disposalSchedule: props.item.disposal ? props.item.disposal.scheduled_date : null ,
    remarks: props.item.remarks,
    editReasonId: 0,
    editReasonText: null
})

// const editReasonId = ref(0)
// const editReasonText = ref('')

// v1.oで書き方が変わった
// const updateItem = id => {
//   router.visit(route('items.update', { item: id }), {i
//     method: 'put',
//     data: form
//   })
// }

// router.visitではuseFormの入力値保持機能は使えない
// form.postなら入力値保持機能(old関数))が使える
const updateItem = id => {
    const formData = new FormData();
    formData.append('id', form.id);
    formData.append('imageFile', form.imageFile);
    formData.append('name', form.name);
    formData.append('categoryId', form.categoryId);
    formData.append('stock', form.stock);
    formData.append('unitId', form.unitId);
    formData.append('minimumStock', form.minimumStock);
    formData.append('notification', form.notification);
    formData.append('usageStatusId', form.usageStatusId);
    formData.append('endUser', form.endUser);
    formData.append('locationOfUseId', form.locationOfUseId);
    formData.append('storageLocationId', form.storageLocationId);
    formData.append('acquisitionMethodId', form.acquisitionMethodId);
    formData.append('acquisitionSource', form.acquisitionSource);
    formData.append('price', form.price);
    formData.append('dateOfAcquisition', form.dateOfAcquisition);
    formData.append('manufacturer', form.manufacturer);
    formData.append('productNumber', form.productNumber);
    formData.append('inspectionSchedule', form.inspectionSchedule);
    formData.append('pendingInspection', form.pendingInspection);
    formData.append('disposalSchedule', form.disposalSchedule);
    formData.append('remarks', form.remarks);
    formData.append('editReasonId', form.editReasonId);
    formData.append('editReasonText', form.editReasonText);


    form.put(`/items/${id}`);
    // form.put(`/items/${id}`, formData, {
    //     headers: {
    //         'Content-Type': 'multipart/form-data'
    //     }
    // }).then(response => {
    //     console.log('成功')
    // }).catch(error => {
    //     console.log('失敗')
    // });
    // router.visit(`/items/${id}`, {
    //     method: 'put',
    //     data: formData,
    //     headers: {
    //         'Content-Type': 'multipart/form-data'
    //     },
    //     onSuccess: () => {
    //         // 成功時の処理
    //     },
    //     onError: () => {
    //         // エラー時の処理
    //     }
    // });
}


// const updateItem = id => {
//     form.put(`/items/${id}`, {
//         forceFormData: true,
//         onSuccess: () => {
//             // 成功時の処理
//         },
//         onError: () => {
//             // エラー時の処理
//         }
//     });
// }


const file_preview_src = ref(props.item.image_path1)

const handleFileUpload = (event) => {
    form.imageFile = event.target.files[0];
    // ブラウザ限定の画像プレビュー用のURL生成
    if (form.imageFile) {
        console.log(file_preview_src.value)
        file_preview_src.value = URL.createObjectURL(form.imageFile);
        console.log(file_preview_src.value)
    }
};


</script>

<template>
    <Head title="備品編集" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                備品編集
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- ここから白い背景 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">
                            <FlashMessage />
                            <form @submit.prevent="updateItem(form.id)" enctype="multipart/form-data">
                                <div class="container px-5 py-8 mx-auto">                                    
                                    <div class="md:w-full mx-auto">
                                        <div class="-m-2">
                                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">

                                                <div class="col-span-2">
                                                    <div class="p-4 border bordr-4 mb-8">

                                                        <div class="p-2 w-full">
                                                            <label for="fileName" class="leading-7 text-sm text-blue-900">画像</label>
                                                            <label for="fileName" class="relative cursor-pointer">
                                                                <input type="file" @change="handleFileUpload" multiple accept="image/png, image/jpeg, image/jpg" id="fileName" name="fileName" 
                                                                    class="sr-only">
                                                                <div class="w-48 h-48 border border-gray-300 rounded-md shadow-sm flex items-center justify-center">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                                    </svg>
                                                                </div>     
                                                            </label>    
                                                            <div v-if="file_preview_src" class="">
                                                                <img :src="file_preview_src" alt="画像プレビュー" class="mr-6 w-48 mt-4">
                                                            </div>
                                                            <div v-if="errors.imageFile" class="font-medium text-red-600">{{ errors.imageFile }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-span-4">
                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="name" class="leading-7 text-sm text-blue-900">
                                                                備品名 <span class="text-red-600">*</span>
                                                            </label>
                                                            <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                                                        </div>
                                                        <div class="p-2 w-full">
                                                            <label for="categoryId" class="leading-7 text-sm text-blue-900">
                                                                カテゴリ <span class="text-red-600">*</span>
                                                            </label><br>
                                                            <select name="categoryId" id="categoryId" v-model="form.categoryId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>  
                                                                <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                            </select>
                                                            <div v-if="errors.categoryId" class="font-medium text-red-600">{{ errors.categoryId }}</div>
                                                        </div>

                                                        
                                                    </div>
                                                    
                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="pl-2 w-full">
                                                            <label for="stock" class="leading-7 text-sm text-blue-900">
                                                                在庫数 <span class="text-red-600">*</span>
                                                            </label><br>
                                                            <input type="number" id="stock" name="stock" v-model="form.stock" class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <select name="unit" id="unit" v-model="form.unitId" class="w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>
                                                            </select>
                                                            <div v-if="errors.stock" class="font-medium text-red-600">{{ errors.stock }}</div>
                                                            <div v-if="errors.unit" class="font-medium text-red-600">{{ errors.unit }}</div>
                                                        </div>
                                                        <div v-show="form.categoryId == 1" class="mt-4 pl-2 w-full">
                                                            <label for="minimumStock" class="leading-7 text-sm text-blue-900">通知在庫数</label><br>
                                                            <input type="number" id="minimumStock" name="minimumStock" v-model="form.minimumStock"
                                                            class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <!-- <span class="ml-1 leading-7 text-sm text-blue-900">個</span> -->
                                                            <select name="unit" id="unit" v-model="form.unitId" class="w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>  
                                                            </select>
                                                            <div v-if="errors.minimumStock" class="font-medium text-red-600">{{ errors.minimumStock }}</div>
                                                        </div>
                                                        <div v-show="form.categoryId == 1" class="mt-4 pl-2 w-full">
                                                            <input type="checkbox" id="notification" v-model="form.notification">
                                                            <label for="notification" class="ml-1">在庫数が通知在庫数以下になったら通知する</label>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="usageStatusId" class="leading-7 text-sm text-blue-900">
                                                                利用状況 <span class="text-red-600">*</span>
                                                            </label>
                                                            <select name="usageStatusId" id="usageStatusId" v-model="form.usageStatusId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="usageStatus in usageStatuses" :value="usageStatus.id" :key="usageStatus.id">{{ usageStatus.name }}</option>
                                                            </select>
                                                            <div v-if="errors.usageStatusId" class="font-medium text-red-600">{{ errors.usageStatusId }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="endUser" class="leading-7 text-sm text-blue-900">使用者</label>
                                                            <input type="text" id="endUser" name="endUser" v-model="form.endUser" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.endUser" class="font-medium text-red-600">{{ errors.endUser }}</div>       
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="locationOfUseId" class="leading-7 text-sm text-blue-900">
                                                                利用場所 <span class="text-red-600">*</span>
                                                            </label>
                                                            <select name="locationOfUseId" id="locationOfUseId" v-model="form.locationOfUseId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.locationOfUseId" class="font-medium text-red-600">{{ errors.locationOfUseId }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="storageLocationId" class="leading-7 text-sm text-blue-900">
                                                                保管場所 <span class="text-red-600">*</span>
                                                            </label>
                                                            <select name="storageLocationId" id="storageLocationId" v-model="form.storageLocationId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.storageLocationId" class="font-medium text-red-600">{{ errors.storageLocationId }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="acquisitionMethodId" class="leading-7 text-sm text-blue-900">
                                                                取得区分 <span class="text-red-600">*</span>
                                                            </label>
                                                            <select name="acquisitionMethodId" id="acquisitionMethodId" v-model="form.acquisitionMethodId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="acquisitionMethod in acquisitionMethods" :key="acquisitionMethod.id" :value="acquisitionMethod.id">{{ acquisitionMethod.name }}</option>
                                                            </select>
                                                            <div v-if="errors.acquisitionMethodId" class="font-medium text-red-600">{{ errors.acquisitionMethodId }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="acquisitionSource" class="leading-7 text-sm text-blue-900">
                                                                取得先 <span class="text-red-600">*</span>
                                                            </label>
                                                            <input type="text" id="acquisitionSource" name="acquisitionSource" v-model="form.acquisitionSource" placeholder="例 Amazonなど" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.acquisitionSource" class="font-medium text-red-600">{{ errors.acquisitionSource }}</div>
                                                        </div>


                                                        <div class="p-2 w-full">
                                                            <label for="price" class="leading-7 text-sm text-blue-900">
                                                                取得価額 <span class="text-red-600">*</span>
                                                            </label>
                                                            <input type="number" id="price" name="price" v-model="form.price" min="0" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.price" class="font-medium text-red-600">{{ errors.price }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="dateOfAcquisition" class="leading-7 text-sm text-blue-900">
                                                                取得年月日 <span class="text-red-600">*</span>
                                                            </label>
                                                            <div class="relative">
                                                                <input type="date" id="dateOfAcquisition" name="dateOfAcquisition" v-model="form.dateOfAcquisition" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearDateOfAcquisition" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500" text-lg>×</button>
                                                            </div>
                                                            <div v-if="errors.dateOfAcquisition" class="font-medium text-red-600">{{ errors.dateOfAcquisition }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="manufacturer" class="leading-7 text-sm text-blue-900">メーカー</label>
                                                            <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.manufacturer" class="font-medium text-red-600">{{ errors.manufacturer }}</div>
                                                        </div>
                                                        
                                                        <div class="p-2 w-full">
                                                            <label for="productNumber" class="leading-7 text-sm text-blue-900">製品番号</label>
                                                            <input type="text" id="productNumber" name="productNumber" v-model="form.productNumber" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.productNumber" class="font-medium text-red-600">{{ errors.productNumber }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="remarks" class="leading-7 text-sm text-blue-900">備考</label>
                                                            <textarea id="remarks" name="remarks" maxlength="500" v-model="form.remarks" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                            <div v-if="errors.remarks" class="font-medium text-red-600">{{ errors.remarks }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="inspectionSchedule" class="leading-7 text-sm text-blue-900">点検予定日</label>
                                                            <div class="relative">
                                                                <input type="date" id="inspectionSchedule" name="inspectionSchedule" v-model="form.inspectionSchedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearInspectionSchedule" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500" text-lg>×</button>
                                                            </div>
                                                            <div v-if="errors.inspectionSchedule" class="font-medium text-red-600">{{ errors.inspectionSchedule }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="disposalSchedule" class="leading-7 text-sm text-blue-900">廃棄予定日</label>
                                                            <div class="relative">
                                                                <input type="date" id="disposalSchedule" name="disposalSchedule" v-model="form.disposalSchedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <button type="button" @click="clearDisposalSchedule" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-500" text-lg>×</button>
                                                            </div>
                                                            <div v-if="errors.disposalSchedule" class="font-medium text-red-600">{{ errors.disposalSchedule }}</div>
                                                        </div>
                                                    </div>

                                                    <hr class="border-t my-4 border-gray-300">

                                                    <div class="p-4 border bordr-4 mt-8">
                                                        <div class="p-2 w-full">
                                                            <label for="categoryId" class="leading-7 text-sm text-blue-900">
                                                                編集理由 <span class="text-red-600">*</span>
                                                            </label><br>
                                                            <select name="editReasonId" id="editReasonId" v-model="form.editReasonId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>  
                                                                <option v-for="editReason in editReasons" :value="editReason.id" :key="editReason.id">{{ editReason.reason }}</option>
                                                            </select>
                                                            <div v-if="errors.editReasonId" class="font-medium text-red-600">{{ errors.editReasonId }}</div>
                                                        </div>
                                                        <div class="p-2 w-full">
                                                            <label for="remarks" class="leading-7 text-sm text-blue-900">その他の理由</label>
                                                            <textarea id="remarks" name="remarks" maxlength="500" v-model="form.editReasonText" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                            <div v-if="errors.editReasonText" class="font-medium text-red-600">{{ errors.editReasonText }}</div>
                                                        </div>

                                                    </div>



                                                    <div class="p-2 w-full">
                                                        <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">更新する</button>
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
