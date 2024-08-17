<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}

// 親コンポーネントから、itemオブジェクトを受け取る
const props = defineProps({
  item: Object,
  userName: String,
  errors: Object
})

const form = useForm({
    disposalDate: new Date().toISOString().substr(0, 10),
    disposalPerson: props.userName,
    details: null,
})


// disposalsテーブルに保存する関数
// <form></form>
const saveDisposal = item => {
  if (confirm('本当に削除しますか？')) {
    form.put(`/dispose_item/${item.id}`)
    // toggleStatus()
  }
}

// const deleteItem = id => {
//     if (confirm('本当に削除しますか？')) {
//         router.delete(`/items/${id}`, {
//             onSuccess: () => {
//                 // 成功時の処理
//             },
//             onError: () => {
//                 // エラー時の処理
//             }
//         });
//     }
// }


</script>
<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="flex modal__title" id="modal-1-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            廃棄
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <!-- フォームの開始 -->
          <form @submit.prevent="saveDisposal(item)">
            <div>
              <div class="p-2 w-full">
                  <label for="name" class="leading-7 text-sm text-blue-900">
                      備品名
                  </label>
                  <div id="name" name="name" class="w-1/2 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                      {{ item.name }}
                  </div>
              </div>
              <div class="p-2 w-full">
                <label for="disposalSchedule" class="leading-7 text-sm text-blue-900">廃棄予定日</label>
                <div id="disposalSchedule" name="disposalSchedule" class="w-1/2 min-h-[2em] bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  {{ item.disposal ? item.disposal.scheduled_date : '' }}  
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="disposalDate" class="leading-7 text-sm text-blue-900">廃棄実施日</label>
                  <div class="relative">
                      <input type="date" id="disposalDate" name="disposalDate" v-model="form.disposalDate" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  </div>
                  <div v-if="errors.disposalDate" class="font-medium text-red-600">{{ errors.disposalDate }}</div>
              </div>
              <div class="p-2 w-full">
                <label for="disposalPerson" class="leading-7 text-sm text-blue-900">廃棄実施者</label>
                <div>
                  <input type="text" id="disposalPerson" name="disposalPerson" v-model="form.disposalPerson" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  <div v-if="errors.disposalPerson" class="font-medium text-red-600">{{ errors.disposalPerson }}</div>       
                </div>
              </div>
              <div class="p-2 w-full">
                <label for="details" class="leading-7 text-sm text-blue-900">詳細情報</label>
                <div>
                  <textarea id="details" name="details" maxlength="500" v-model="form.details" class="w-2/3 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                  <div v-if="errors.details" class="font-medium text-red-600">{{ errors.details }}</div>
                </div>
              </div>
            </div>
            <div class="p-2 w-full">
              <button class="flex mx-auto text-white bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded">廃棄を実施する</button>
            </div>

            <p>Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also, <code>esc</code> to close modal.</p>
          </form>
        </main>
        <!-- <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn modal__btn-primary">Continue</button>
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
        </footer> -->
      </div>
    </div>
  </div>
  <button @click="toggleStatus" type="button" data-micromodal-trigger="modal-1" href='javascript:;'
  class="flex mx-auto text-white bg-red-500 border-0 py-2 px-8 focus:outline-none hover:bg-red-600 rounded">廃棄するモーダル</button>
</template>