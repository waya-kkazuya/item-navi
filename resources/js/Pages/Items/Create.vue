<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

defineProps({
    categories: Array,
    locations: Array,
    units: Array,
    usageStatuses: Array,
    acquisitionMethods: Array,
    errors: Object
})

// useFormは後回し
const form = reactive({
  id: null,
  name: null,
  categoryId: 0, // categoriesテーブルで1は'未選択'
  fileName: null,
//   image1: null,
//   image2: null,
//   image3: null,
  stock: 1, // 最初から1を入力しておく
  unitId: 1,
  minimumStock: 0,
  notification: true,
  usageStatus: 0,
  endUser: null,
  locationOfUseId: 0, // locationsテーブルで1は「未選択」
  storageLocationId: 0, // locationsテーブルで1は「未選択」
  acquisitionMethod: 0,
  whereToBuy: null,
  price: 0,
  dateOfAcquisition: new Date().toISOString().substr(0, 10),
  inspectionSchedule: new Date().toISOString().substr(0, 10),
  disposalSchedule: new Date().toISOString().substr(0, 10),
  manufacturer: null,
  productNumber: null,
  remarks: null,
  qrcode: null
})

const file_src = ref('')

// FormDataを使用することで、複数ファイルのstoreすることを可能に
const storeItem = () => {
    let formData = new FormData();
    formData.append('name', form.name);
    formData.append('categoryId', form.categoryId);
    formData.append('stock', form.stock);
    formData.append('minimumStock', form.minimumStock);
    formData.append('usageStatus', form.usageStatus);
    formData.append('endUser', form.endUser);
    formData.append('locationOfUseId', form.locationOfUseId);
    formData.append('storageLocationId', form.storageLocationId);
    formData.append('acquisitionMethod', form.acquisitionMethod);
    formData.append('whereToBuy', form.whereToBuy);
    formData.append('price', form.price);
    formData.append('dateOfAcquisition', form.dateOfAcquisition);
    formData.append('inspectionSchedule', form.inspectionSchedule);
    formData.append('disposalSchedule', form.disposalSchedule);
    formData.append('manufacturer', form.manufacturer);
    formData.append('productNumber', form.productNumber);
    formData.append('remarks', form.remarks);
    formData.append('qrcode', form.qrcode);
    if(form.fileName){
        form.fileName.forEach((file, index) => {
            formData.append(`fileName[${index}]`, file);
        });
    }
    
    router.visit('/items', {
        method: 'post',
        data: formData
    })
}

// const handleFileUpload = (event) => {
//     form.image_path1 = event.target.files[0];
//   if (form.image_path1) {
//     file_src.value = URL.createObjectURL(form.image_path1);
//   }
// };

const handleFileUpload = (event) => {
  if(event.target.files.length > 3) {
    alert('アップロードできる画像は3枚までです');
    event.target.value = ''; // 選択状態を解除
    return
  }
  // formにはv-modelで値が入らないので、コードで入れる
  // 配列をform.file_nameに入れる
  form.fileName = Array.from(event.target.files);
  // form.fileName = event.target.files[0];
  // プレビュー画像表示用のいソースを書く
  if (form.fileName) {
    file_src.value = form.fileName.map(file => URL.createObjectURL(file));
    // file_src.value = URL.createObjectURL(form.fileName);
  }
};

// const handleFileUpload = event => {
//     form.image_path1 = event.target.files[0];
// }
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

                            <form @submit.prevent="storeItem">
                                <div class="container px-5 py-8 mx-auto">                                    
                                    <div class="md:w-full mx-auto">
                                        <div class="-m-2">
                                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                                                <div class="col-span-2">
                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <!-- <div class="flex justify-around">
                                                            <div class="p-2 w-full">
                                                                <div class="relative">
                                                                    <label for="fileName" class="leading-7 text-sm text-gray-600">画像データ ※3枚まで</label>
                                                                    <input type="file" @change="handleFileUpload" accept="image/png, image/jpeg, image/jpg" id="image_path1" name="image_path1" 
                                                                        class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                    <img v-if="file_src.value" :src="file_src.value" alt="Image preview...">
                                                                </div>
                                                            </div>
                                                        </div> -->

                                                        <div class="p-2 w-full">
                                                            <label for="fileName" class="leading-7 text-sm text-gray-600">画像</label>
                                                            <label for="fileName" class="relative cursor-pointer">
                                                                <input type="file" @change="handleFileUpload" multiple accept="image/png, image/jpeg, image/jpg" id="fileName" name="fileName" 
                                                                    class="sr-only">
                                                                <div class="w-48 h-48 border border-gray-300 rounded-md shadow-sm flex items-center justify-center">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                                    </svg>
                                                                </div>     
                                                            </label>    
                                                            <div class="flex">
                                                                <img v-for="(src, index) in file_src" :key="index" :src=src alt="Image preview..." class="mr-6 w-36 mt-4">
                                                            </div>
                                                            <div v-if="errors.fileName" class="font-medium text-red-600">{{ errors.fileName }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-span-3">
                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                                                            <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                                                        </div>
                                                        <div class="p-2 w-full">
                                                            <label for="category" class="leading-7 text-sm text-gray-600">カテゴリ</label><br>
                                                            <select name="category" id="category" v-model="form.categoryId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>  
                                                                <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                            </select>
                                                            <div v-if="errors.category" class="font-medium text-red-600">{{ errors.category }}</div>
                                                        </div>

                                                        
                                                    </div>
                                                    
                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="pl-2 w-full">
                                                            <label for="stock" class="leading-7 text-sm text-gray-600">在庫数</label><br>
                                                            <input type="number" id="stock" name="stock" v-model="form.stock"
                                                            class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    
                                                            <select name="unit" id="unit" v-model="form.unitId" class="w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option v-for="unit in units" :value="unit.id" :key="unit.id">{{ unit.name }}</option>
                                                            </select>

                                                            <div v-if="errors.stock" class="font-medium text-red-600">{{ errors.stock }}</div>
                                                        </div>
                                                        <div v-show="form.categoryId == 1" class="mt-4 pl-2 w-full">
                                                            <label for="minimumStock" class="leading-7 text-sm text-gray-600">通知在庫数</label><br>
                                                            <input type="number" id="minimumStock" name="minimumStock" v-model="form.minimumStock"
                                                            class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <!-- <span class="ml-1 leading-7 text-sm text-gray-600">個</span> -->
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
                                                            <label for="usageStatus" class="leading-7 text-sm text-gray-600">利用状況</label>
                                                            <select name="usageStatus" id="usageStatus" v-model="form.usageStatus" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="usageStatus in usageStatuses" :value="usageStatus.id" :key="usageStatus.id">{{ usageStatus.name }}</option>
                                                            </select>
                                                            <div v-if="errors.usageStatus" class="font-medium text-red-600">{{ errors.usageStatus }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="endUser" class="leading-7 text-sm text-gray-600">使用者</label>
                                                            <input type="text" id="endUser" name="endUser" v-model="form.endUser" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.endUser" class="font-medium text-red-600">{{ errors.endUser }}</div>       
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="locationOfUseId" class="leading-7 text-sm text-gray-600">利用場所</label>
                                                            <select name="locationOfUseId" id="locationOfUseId" v-model="form.locationOfUseId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.locationOfUseId" class="font-medium text-red-600">{{ errors.locationOfUseId }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="storageLocationId" class="leading-7 text-sm text-gray-600">保管場所</label>
                                                            <select name="storageLocationId" id="storageLocationId" v-model="form.storageLocationId" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                            </select>
                                                            <div v-if="errors.storageLocationId" class="font-medium text-red-600">{{ errors.storageLocationId }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="acquisitionMethod" class="leading-7 text-sm text-gray-600">取得区分</label>
                                                            <select name="acquisitionMethod" id="acquisitionMethod" v-model="form.acquisitionMethod" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                                <option :value="0">選択してください</option>
                                                                <option v-for="acquisitionMethod in acquisitionMethods" :key="acquisitionMethod.id" :value="acquisitionMethod.id">{{ acquisitionMethod.name }}</option>
                                                            </select>
                                                            <div v-if="errors.acquisitionMethod" class="font-medium text-red-600">{{ errors.acquisitionMethod }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="whereToBuy" class="leading-7 text-sm text-gray-600">取得先</label>
                                                            <input type="text" id="whereToBuy" name="whereToBuy" v-model="form.whereToBuy" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.whereToBuy" class="font-medium text-red-600">{{ errors.whereToBuy }}</div>
                                                        </div>


                                                        <div class="p-2 w-full">
                                                            <label for="price" class="leading-7 text-sm text-gray-600">取得価額</label>
                                                            <input type="number" id="price" name="price" v-model="form.price" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.price" class="font-medium text-red-600">{{ errors.price }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="dateOfAcquisition" class="leading-7 text-sm text-gray-600">取得年月日</label>
                                                            <input type="date" id="dateOfAcquisition" name="dateOfAcquisition" v-model="form.dateOfAcquisition" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.dateOfAcquisition" class="font-medium text-red-600">{{ errors.dateOfAcquisition }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-8">
                                                        <div class="p-2 w-full">
                                                            <label for="inspectionSchedule" class="leading-7 text-sm text-gray-600">点検時期</label>
                                                            <input type="date" id="inspectionSchedule" name="inspectionSchedule" v-model="form.inspectionSchedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.inspectionSchedule" class="font-medium text-red-600">{{ errors.inspectionSchedule }}</div>
                                                        </div>

                                                        <div class="p-2 w-full">
                                                            <label for="disposalSchedule" class="leading-7 text-sm text-gray-600">廃棄時期</label>
                                                            <input type="date" id="disposalSchedule" name="disposalSchedule" v-model="form.disposalSchedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.disposalSchedule" class="font-medium text-red-600">{{ errors.disposalSchedule }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 border bordr-4 mb-4">
                                                        <div class="p-2 w-full">
                                                            <label for="manufacturer" class="leading-7 text-sm text-gray-600">メーカー</label>
                                                            <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.manufacturer" class="font-medium text-red-600">{{ errors.manufacturer }}</div>
                                                        </div>
                                                        
                                                        <div class="p-2 w-full">
                                                            <label for="productNumber" class="leading-7 text-sm text-gray-600">製品番号</label>
                                                            <input type="text" id="productNumber" name="productNumber" v-model="form.productNumber" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                            <div v-if="errors.productNumber" class="font-medium text-red-600">{{ errors.productNumber }}</div>
                                                        </div>

                                                    
                                                        <div class="p-2 w-full">
                                                            <label for="remarks" class="leading-7 text-sm text-gray-600">備考</label>
                                                            <textarea id="remarks" name="remarks" maxlength="500" v-model="form.remarks" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                            <div v-if="errors.remarks" class="font-medium text-red-600">{{ errors.remarks }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="p-2 w-full">
                                                        <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
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
