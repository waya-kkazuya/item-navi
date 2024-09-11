
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
  imageTest: Array,
  errors: Object
})

const form = reactive({
  id: props.imageTest.id,
  name: props.imageTest.name,
  file_name: props.imageTest.file_name,
})

const file_src = ref('')

// お手本
// const updateItem = id => {
//   router.visit(route('items.update', { item: id }), {
//     method: 'put',
//     data: form
//   })
// }


const updateItem = id => {
  // let formData = new FormData();
  // formData.append('name', form.name);
  // form.file_name.forEach((file, index) => {
  //   formData.append(`file_name[${index}]`, file);
  // });

  router.visit(route('image_tests.update', { image_test: id }), {
    method: 'put',
    data: form
  })
}

const deleteItem = id => {
    router.visit(route('image_tests.destroy', { image_test: id }), {
        method: 'delete',
        onBefore: visit => confirm('本当に削除しますか？')
        // onBefore: () => confirm('本当に削除しますか？')
    })
}



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
                  <form @submit.prevent="updateItem(form.id)">

                    <div class="flex justify-around">
                      <div class="p-2 w-full">
                        <div class="relative">
                            <label for="name" class="leading-7 text-sm text-gray-600">備品名</label>
                            <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                            <div v-if="errors.name" class="font-medium text-red-600">{{ errors.name }}</div>
                        </div>
                      </div>
                    </div>

                    <div class="">
                      <!-- <div v-for="item in imageTests" :key="item.id" class="p-4 border"> -->
                        <!-- <div>{{ item.file_name }}</div> -->
                        <div class="w-32">
                          <img :src="form.file_name">
                        </div>
                        <!-- 今回はshowコンポーネントは省略 -->
                        <div>{{ form.id }}</div>
                        <div class="text-gray-700">{{ form.name }}</div>
                      <!-- </div>  -->
                    </div>    
                    <div class="p-2 w-full">
                      <button class="flex mt-4 mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">更新する</button>
                    </div>
                  </form>
                  <div class="p-2 w-full">
                      <button @click="deleteItem(form.id)" class="flex mt-4 mx-auto text-white bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded text-lg">削除する</button>
                    </div>   
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
