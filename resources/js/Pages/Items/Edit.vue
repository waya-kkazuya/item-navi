<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    item: Object,
    categories: Array,
    locations: Array,
    errors: Object
})

const form = reactive({
  id: props.item.id,
  name: props.item.name,
  category_id: props.item.category_id,
  image_path1: props.item.image_path1,
  image_path2: props.item.image_path2,
  image_path3: props.item.image_path3,
  stocks: props.item.stocks,
  usage_status: props.item.usage_status,
  end_user: props.item.end_user,
  location_of_use_id: props.item.location_of_use_id,
  storage_location_id: props.item.storage_location_id,
  acquisition_category: props.item.acquisition_category,
  price: props.item.price,
  date_of_acquisition: props.item.date_of_acquisition,
  inspection_schedule: props.item.inspection_schedule,
  disposal_schedule: props.item.disposal_schedule,
  manufacturer: props.item.manufacturer,
  product_number: props.item.product_number,
  vendor: props.item.vendor,
  vendor_website_url: props.item.vendor_website_url,
  remarks: props.item.remarks,
  qrcode_path: props.item.qrcode_path
})

// v1.oで書き方が変わった
// 旧　Inertia.post('/items', form)
const updateItem = id => {
  router.visit(route('items.update', { item: id }), {
    method: 'put',
    data: form
  })
}
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">

                            <form @submit.prevent="updateItem(form.id)">
                                <div class="container px-5 py-8 mx-auto">
                                    
                                    
                                    <div class="lg:w-1/2 md:w-2/3 mx-auto">
                                        <div class="-m-2">

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                                                    <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                                                </div>
                                                <div class="p-2 w-full">
                                                    <label for="category" class="leading-7 text-sm text-gray-600">カテゴリ</label><br>
                                                    <select name="category" id="category" v-model="form.category_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                    </select>
                                                    <div v-if="errors.category" class="font-medium text-red-600">{{ errors.category }}</div>
                                                </div>

                                                <label class="ml-2 leading-7 text-sm text-gray-600">画像</label>
                                                <div class="flex justify-around">
                                                    <div v-if="item.image_path1" class="p-2 w-full">
                                                        <img :src="`/storage/items/${item.image_path1}`" alt="Image preview...">
                                                    </div>
                                                    <div v-if="item.image_path2" class="p-2 w-full">
                                                        <img :src="`/storage/items/${item.image_path2}`" alt="Image preview...">
                                                    </div>
                                                    <div v-if="item.image_path3" class="p-2 w-full">          
                                                        <img :src="`/storage/items/${item.image_path3}`" alt="Image preview...">
                                                </div>
                                                </div>
                                            </div>

                                            <div class="p-4 flex justify-between border bordr-4 mb-8">
                                                <div class="pl-2 w-full">
                                                    <label for="stocks" class="leading-7 text-sm text-gray-600">在庫数</label><br>
                                                    <input type="number" id="stocks" name="s tocks" v-model="form.stocks" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <span class="ml-1 leading-7 text-sm text-gray-600">個</span>
                                                    <div v-if="errors.stocks" class="font-medium text-red-600">{{ errors.stocks }}</div>
                                                </div>
                                                <div v-if="form.category_id == 2" class="p-4 w-full">
                                                    <label for="minimum_stock" class="leading-7 text-sm text-gray-600">最低在庫数</label><br>
                                                    <input type="number" id="minimum_stock" name="s tocks" v-model="form.minimum_stock" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <span class="ml-1 leading-7 text-sm text-gray-600">個</span>
                                                    <div v-if="errors.minimum_stock" class="font-medium text-red-600">{{ errors.minimum_stock }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="usage_status" class="leading-7 text-sm text-gray-600">利用状況</label>
                                                    <select name="usage_status" id="usage_status" v-model="form.usage_status" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option value="未選択">未選択</option>
                                                        <option value="使用中">使用中</option>usage_status
                                                        <option value="未使用">未使用</option>
                                                    </select>
                                                    <div v-if="errors.usage_status" class="font-medium text-red-600">{{ errors.usage_status }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="end_user" class="leading-7 text-sm text-gray-600">使用者</label>
                                                    <input type="text" id="end_user" name="end_user" v-model="form.end_user" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.end_user" class="font-medium text-red-600">{{ errors.end_user }}</div>       
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="location_of_use_id" class="leading-7 text-sm text-gray-600">設置場所</label>
                                                    <select name="location_of_use_id" id="location_of_use_id" v-model="form.location_of_use_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                    </select>
                                                    <div v-if="errors.location_of_use_id" class="font-medium text-red-600">{{ errors.location_of_use_id }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="storage_location_id" class="leading-7 text-sm text-gray-600">保管場所</label>
                                                    <select name="storage_location_id" id="storage_location_id" v-model="form.storage_location_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                    </select>
                                                    <div v-if="errors.storage_location_id" class="font-medium text-red-600">{{ errors.storage_location_id }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="acquisition_category" class="leading-7 text-sm text-gray-600">取得区分</label>
                                                    <select name="acquisition_category" id="acquisition_category" v-model="form.acquisition_category" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option value="未選択">未選択</option>
                                                        <option value="購入">購入</option>
                                                        <option value="リース（レンタル）">リース（レンタル）</option>
                                                        <option value="譲渡">譲渡</option>
                                                        <option value="その他">その他</option>
                                                    </select>
                                                    <div v-if="errors.acquisition_category" class="font-medium text-red-600">{{ errors.acquisition_category }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="where_to_buy" class="leading-7 text-sm text-gray-600">購入先</label>
                                                    <input type="text" id="where_to_buy" name="where_to_buy" v-model="form.where_to_buy" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.where_to_buy" class="font-medium text-red-600">{{ errors.where_to_buy }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="price" class="leading-7 text-sm text-gray-600">取得価額</label>
                                                    <input type="number" id="price" name="price" v-model="form.price" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.price" class="font-medium text-red-600">{{ errors.price }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="date_of_acquisition" class="leading-7 text-sm text-gray-600">取得年月日</label>
                                                    <input type="date" id="date_of_acquisition" name="date_of_acquisition" v-model="form.date_of_acquisition" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.date_of_acquisition" class="font-medium text-red-600">{{ errors.date_of_acquisition }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="inspection_schedule" class="leading-7 text-sm text-gray-600">点検時期</label>
                                                    <input type="date" id="inspection_schedule" name="inspection_schedule" v-model="form.inspection_schedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.inspection_schedule" class="font-medium text-red-600">{{ errors.inspection_schedule }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="disposal_schedule" class="leading-7 text-sm text-gray-600">廃棄時期</label>
                                                    <input type="date" id="disposal_schedule" name="disposal_schedule" v-model="form.disposal_schedule" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.disposal_schedule" class="font-medium text-red-600">{{ errors.disposal_schedule }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-4">
                                                <div class="p-2 w-full">
                                                    <label for="manufacturer" class="leading-7 text-sm text-gray-600">メーカー</label>
                                                    <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.manufacturer" class="font-medium text-red-600">{{ errors.manufacturer }}</div>
                                                </div>
                                                
                                                <div class="p-2 w-full">
                                                    <label for="product_number" class="leading-7 text-sm text-gray-600">製品番号</label>
                                                    <input type="text" id="product_number" name="product_number" v-model="form.product_number" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.product_number" class="font-medium text-red-600">{{ errors.product_number }}</div>
                                                </div>
                                            
                                                <div class="p-2 w-full">
                                                    <label for="remarks" class="leading-7 text-sm text-gray-600">備考</label>
                                                    <textarea id="remarks" name="remarks" v-model="form.remarks" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                    <div v-if="errors.remarks" class="font-medium text-red-600">{{ errors.remarks }}</div>
                                                </div>
                                            </div>
                                            <div class="p-2 w-full">
                                                <button class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">更新する</button>
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
