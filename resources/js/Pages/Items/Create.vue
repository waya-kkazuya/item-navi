<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

defineProps({
    categories: Array,
    errors: Object
})

// useFormは後回し
const form = reactive({
  id: null,
  name: null,
  category_id: null,
  file_name: null,
//   image_path1: null,
//   image_path2: null,
//   image_path3: null,
  stocks: null,
  usage_status: "未選択",
  end_user: null,
  location_of_use: "未選択",
  storage_location: "未選択",
  acquisition_category: "未選択",
  where_to_buy: null,
  price: null,
  date_of_acquisition: new Date().toISOString().substr(0, 10),
  inspection_schedule: new Date().toISOString().substr(0, 10),
  disposal_schedule: new Date().toISOString().substr(0, 10),
  manufacturer: null,
  product_number: null,
  remarks: null,
  qrcode_path: null
})

const file_src = ref('')

const storeItem = () => {
    let formData = new FormData();
    formData.append('name', form.name);
    formData.append('category_id', form.category_id);
    formData.append('stocks', form.stocks);
    formData.append('usage_status', form.usage_status);
    formData.append('end_user', form.end_user);
    formData.append('location_of_use', form.location_of_use);
    formData.append('storage_location', form.storage_location);
    formData.append('acquisition_category', form.acquisition_category);
    formData.append('where_to_buy', form.where_to_buy);
    formData.append('price', form.price);
    formData.append('date_of_acquisition', form.date_of_acquisition);
    formData.append('inspection_schedule', form.inspection_schedule);
    formData.append('disposal_schedule', form.disposal_schedule);
    formData.append('manufacturer', form.manufacturer);
    formData.append('product_number', form.product_number);
    formData.append('remarks', form.remarks);
    formData.append('qrcode_path', form.qrcode_path);
    if(form.file_name){
        form.file_name.forEach((file, index) => {
            formData.append(`file_name[${index}]`, file);
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
  form.file_name = Array.from(event.target.files);
  // form.file_name = event.target.files[0];
  // プレビュー画像表示用のいソースを書く
  if (form.file_name) {
    file_src.value = form.file_name.map(file => URL.createObjectURL(file));
    // file_src.value = URL.createObjectURL(form.file_name);
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">

                            <form @submit.prevent="storeItem">
                                <div class="container px-5 py-8 mx-auto">
                                    
                                    
                                    <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                        <div class="-m-2">

                                        <div class="border bordr-4 mb-8">
                                            <div class="p-2 w-full">
                                            <div class="relative">
                                                <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                                                <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                                            </div>
                                            </div>
                                            <div class="p-2 w-full">
                                            <div class="relative">
                                                <label for="category" class="leading-7 text-sm text-gray-600">カテゴリ</label><br>
                                                <select name="category" id="category" v-model="form.category_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                </select>
                                                <div v-if="errors.category" class="font-medium text-red-600">{{ errors.category }}</div>
                                            </div>
                                            </div>

                                            <!-- <div class="flex justify-around">
                                                <div class="p-2 w-full">
                                                    <div class="relative">
                                                        <label for="file_name" class="leading-7 text-sm text-gray-600">画像データ ※3枚まで</label>
                                                        <input type="file" @change="handleFileUpload" accept="image/png, image/jpeg, image/jpg" id="image_path1" name="image_path1" 
                                                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <img v-if="file_src.value" :src="file_src.value" alt="Image preview...">
                                                    </div>
                                                </div>
                                            </div> -->

                                            <div class="relative">
                                                <label for="file_name" class="leading-7 text-sm text-gray-600">画像データ ※3枚まで</label>
                                                <input type="file" @change="handleFileUpload" multiple accept="image/png, image/jpeg, image/jpg" id="file_name" name="file_name" 
                                                    class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <div class="flex">
                                                    <img v-for="(src, index) in file_src" :key="index" :src=src alt="Image preview..." class="mr-6 w-36 mt-4">
                                                </div>
                                                <div v-if="errors.file_name" class="font-medium text-red-600">{{ errors.file_name }}</div>
                                            </div>

                                        </div>
                                        
                                        <div class="p-2 w-full mb-8">
                                        <div class="relative">
                                            <label for="stocks" class="leading-7 text-sm text-gray-600">在庫数</label><br>
                                            <input type="number" id="stocks" name="s tocks" v-model="form.stocks" class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <span class="ml-1 leading-7 text-sm text-gray-600">個</span>
                                            <div v-if="errors.stocks" class="font-medium text-red-600">{{ errors.stocks }}</div>
                                        </div>
                                        </div>

                                    <div class="border bordr-4 mb-8">
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="usage_status" class="leading-7 text-sm text-gray-600">利用状況</label>
                                            <select name="usage_status" id="usage_status" v-model="form.usage_status" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <option value="未選択">未選択</option>
                                                <option value="使用中">使用中</option>usage_status
                                                <option value="未使用">未使用</option>
                                            </select>
                                            <!-- <input type="text" id="usage_status" name="usage_status" v-model="form.usage_status" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"> -->
                                            <div v-if="errors.usage_status" class="font-medium text-red-600">{{ errors.usage_status }}</div>
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="end_user" class="leading-7 text-sm text-gray-600">使用者</label>
                                            <input type="text" id="end_user" name="end_user" v-model="form.end_user" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.end_user" class="font-medium text-red-600">{{ errors.end_user }}</div>       
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="location_of_use" class="leading-7 text-sm text-gray-600">設置場所</label>
                                            <select name="location_of_use" id="location_of_use" v-model="form.location_of_use" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <option value="未選択">未選択</option>
                                                <option value="作業室1">作業室1</option>
                                                <option value="作業室2">作業室2</option>
                                                <option value="玄関">玄関</option>
                                                <option value="廊下">廊下</option>
                                                <option value="給湯室">給湯室</option>
                                                <option value="トイレ">トイレ</option>
                                                <option value="事務室">事務室</option>
                                            </select>
                                            <!-- <input type="text" id="location_of_use" name="location_of_use" v-model="form.location_of_use" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"> -->
                                            <div v-if="errors.location_of_use" class="font-medium text-red-600">{{ errors.location_of_use }}</div>
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="storage_location" class="leading-7 text-sm text-gray-600">保管場所</label>
                                            <select name="storage_location" id="storage_location" v-model="form.storage_location" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <option value="未選択">未選択</option>
                                                <option value="作業室1">作業室1</option>
                                                <option value="作業室2">作業室2</option>
                                                <option value="玄関">玄関</option>
                                                <option value="廊下">廊下</option>
                                                <option value="給湯室">給湯室</option>
                                                <option value="トイレ">トイレ</option>
                                                <option value="事務室">事務室</option>
                                                <option value="倉庫">倉庫</option>
                                            </select>
                                            <!-- <input type="text" id="storage_location" name="storage_location" v-model="form.storage_location" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"> -->
                                            <div v-if="errors.storage_location" class="font-medium text-red-600">{{ errors.storage_location }}</div>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="border bordr-4 mb-8">
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="acquisition_category" class="leading-7 text-sm text-gray-600">取得区分</label>
                                            <select name="acquisition_category" id="acquisition_category" v-model="form.acquisition_category" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <option value="未選択">未選択</option>
                                                <option value="購入">購入</option>
                                                <option value="リース（レンタル）">リース（レンタル）</option>
                                                <option value="譲渡">譲渡</option>
                                                <option value="その他">その他</option>
                                            </select>
                                            <!-- <input type="text" id="acquisition_category" name="acquisition_category" v-model="form.acquisition_category" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"> -->
                                            <div v-if="errors.acquisition_category" class="font-medium text-red-600">{{ errors.acquisition_category }}</div>
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                            <div class="relative">
                                                <label for="where_to_buy" class="leading-7 text-sm text-gray-600">購入先</label>
                                                <input type="text" id="where_to_buy" name="where_to_buy" v-model="form.where_to_buy" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                <div v-if="errors.where_to_buy" class="font-medium text-red-600">{{ errors.where_to_buy }}</div>
                                            </div>
                                        </div>


                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="price" class="leading-7 text-sm text-gray-600">取得価額</label>
                                            <input type="number" id="price" name="price" v-model="form.price" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.price" class="font-medium text-red-600">{{ errors.price }}</div>
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="date_of_acquisition" class="leading-7 text-sm text-gray-600">取得年月日</label>
                                            <input type="date" id="date_of_acquisition" name="date_of_acquisition" v-model="form.date_of_acquisition" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.date_of_acquisition" class="font-medium text-red-600">{{ errors.date_of_acquisition }}</div>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="border bordr-4 mb-8">
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="inspection_schedule" class="leading-7 text-sm text-gray-600">点検時期</label>
                                            <input type="date" id="inspection_schedule" name="inspection_schedule" v-model="form.inspection_schedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.inspection_schedule" class="font-medium text-red-600">{{ errors.inspection_schedule }}</div>
                                        </div>
                                        </div>

                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="disposal_schedule" class="leading-7 text-sm text-gray-600">廃棄時期</label>
                                            <input type="date" id="disposal_schedule" name="disposal_schedule" v-model="form.disposal_schedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.disposal_schedule" class="font-medium text-red-600">{{ errors.disposal_schedule }}</div>
                                        </div>
                                        </div>
                                    </div>

                                    <div class="border bordr-4 mb-4">
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="manufacturer" class="leading-7 text-sm text-gray-600">メーカー</label>
                                            <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.manufacturer" class="font-medium text-red-600">{{ errors.manufacturer }}</div>
                                        </div>
                                        </div>
                                        
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="product_number" class="leading-7 text-sm text-gray-600">製品番号</label>
                                            <input type="text" id="product_number" name="product_number" v-model="form.product_number" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                            <div v-if="errors.product_number" class="font-medium text-red-600">{{ errors.product_number }}</div>
                                        </div>
                                        </div>

                                    
                                        <div class="p-2 w-full">
                                        <div class="relative">
                                            <label for="remarks" class="leading-7 text-sm text-gray-600">備考</label>
                                            <textarea id="remarks" name="remarks" v-model="form.remarks" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                            <div v-if="errors.remarks" class="font-medium text-red-600">{{ errors.remarks }}</div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="p-2 w-full">
                                        <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
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
