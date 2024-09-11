<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({
    categories: Array,
    locations: Array,
    errors: Object
})


const form = useForm({
    id: null,
    name: null,
    category_id: 0, 
    location_of_use_id: 0,
    requestor: null,
    remarks_from_requestor: null,
    request_status_id: null, // request_statusはindex画面で変更する
    manufacturer: null,
    reference: null,
    price: 0,
})


// router.visitではuseFormの入力値保持機能は使えない
// form.postなら入力値保持機能(old関数))が使える
const storeItemRequest = () => {
  form.post('/item-requests', {
    onError: (errors) => {
        console.log('保存に失敗しました')
        console.log(errors)
    //   form.errors = errors
    }
  })
}

</script>

<template>
    <Head title="リクエスト登録" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                リクエスト登録
            </h2>
        </template>

        <div class="py-12">
            <!-- ここから白い背景 -->
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <section class="text-gray-600 body-font relative">
                            <FlashMessage />
                            <form @submit.prevent="storeItemRequest" enctype="multipart/form-data">
                                <div class="container px-8 py-8 mx-auto">                                    
                                    <div class="md:w-full mx-auto">
                                        <div class="-m-2">
                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="name" class="leading-7 text-sm text-blue-900">
                                                        商品名 <span class="text-red-600">*</span>
                                                    </label>
                                                    <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                                                </div>
                                                <div class="p-2 w-full">
                                                    <label for="category_id" class="leading-7 text-sm text-blue-900">
                                                        カテゴリ <span class="text-red-600">*</span>
                                                    </label><br>
                                                    <select name="category_id" id="category_id" v-model="form.category_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option :value="0">選択してください</option>  
                                                        <option v-for="category in categories" :value="category.id" :key="category.id">{{ category.name }}</option>
                                                    </select>
                                                    <div v-if="errors.category_id" class="font-medium text-red-600">{{ errors.category_id }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="location_of_use_id" class="leading-7 text-sm text-blue-900">
                                                        利用場所 <span class="text-red-600">*</span>
                                                    </label>
                                                    <select name="location_of_use_id" id="location_of_use_id" v-model="form.location_of_use_id" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                        <option :value="0">選択してください</option>
                                                        <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                                    </select>
                                                    <div v-if="errors.location_of_use_id" class="font-medium text-red-600">{{ errors.location_of_use_id }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 border bordr-4 mb-8">
                                                <div class="p-2 w-full">
                                                    <label for="manufacturer" class="leading-7 text-sm text-blue-900">
                                                        メーカー
                                                    </label>
                                                    <input type="text" id="manufacturer" name="manufacturer" v-model="form.manufacturer" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.manufacturer" class="font-medium text-red-600">{{ errors.manufacturer }}</div>
                                                </div>  

                                                <div class="p-2 w-full">
                                                    <label for="reference" class="leading-7 text-sm text-blue-900">
                                                        参考サイト
                                                    </label>
                                                    <input type="text" id="reference" name="reference" v-model="form.reference" placeholder="例 Amazonなど" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.reference" class="font-medium text-red-600">{{ errors.reference }}</div>
                                                </div>

                                                <div class="p-2 w-full">
                                                    <label for="price" class="leading-7 text-sm text-blue-900">
                                                        価格
                                                    </label>
                                                    <input type="number" id="price" name="price" v-model="form.price" min="0" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.price" class="font-medium text-red-600">{{ errors.price }}</div>
                                                </div>

                                            </div>

                                            <div class="p-4 border bordr-4 mb-4">
                                                <div class="p-2 w-full">
                                                    <label for="requestor" class="leading-7 text-sm text-blue-900">
                                                        申請者 <span class="text-red-600">*</span>
                                                    </label>
                                                    <input type="text" id="requestor" name="requestor" v-model="form.requestor" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                                    <div v-if="errors.requestor" class="font-medium text-red-600">{{ errors.requestor }}</div>
                                                </div>
                                            
                                                <div class="p-2 w-full">
                                                    <label for="remarks_from_requestor" class="leading-7 text-sm text-blue-900">
                                                        申請理由 <span class="text-red-600">*</span>
                                                    </label>
                                                    <textarea id="remarks_from_requestor" name="remarks_from_requestor" maxlength="500" v-model="form.remarks_from_requestor" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                                                    <div v-if="errors.remarks_from_requestor" class="font-medium text-red-600">{{ errors.remarks_from_requestor }}</div>
                                                </div>
                                            </div>

                                            <div class="p-2 w-full">
                                                <!-- <Link>タグでキャンセルボタン -->
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
