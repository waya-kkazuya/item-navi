<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

defineProps({
    errors: Object
})

const form = reactive({
  name: null,
  file_name: null,
})

const file_src = ref('')

const storeItem = () => {
  router.visit('/image_tests', {
    method: 'post',
    data: form
  })
}



const handleFileUpload = (event) => {
  // formにはv-modelで値が入らないので、コードで入れる
  form.file_name = event.target.files[0];
  // プレビュー画像表示用のいソースを書く
  if (form.file_name) {
    file_src.value = URL.createObjectURL(form.file_name);
  }
};

</script>

<template>
    <Head title="画像テスト" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">画像テスト</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                  <form @submit.prevent="storeItem">

                    <div class="p-6 text-gray-900">
                      <div class="flex justify-around">
                        <div class="p-2 w-full">
                          <div class="relative">
                              <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                              <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                          </div>
                          <div class="relative">
                              <label for="file_name" class="leading-7 text-sm text-gray-600">画像データ</label>
                              <input type="file" @change="handleFileUpload" multiple accept="image/png, image/jpeg, image/jpg" id="file_name" name="file_name" 
                                  class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              <!-- <input type="file" @change="handleFileUpload" accept="image/png, image/jpeg, image/jpg" id="file_name" name="file_name" 
                                  class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"> -->
                              <img v-if="file_src" :src="file_src" alt="Image preview..." class="w-36 mt-4">
                              <div v-if="errors.file_name" class="font-medium text-red-600">{{ errors.file_name }}</div>
                          </div>
                          <div class="p-2 w-full">
                            <button class="flex mt-4 mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">登録する</button>
                          </div>                     
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
