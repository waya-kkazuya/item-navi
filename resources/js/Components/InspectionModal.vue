<script setup>
import axios from 'axios';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}

// 親コンポーネントから、itemオブジェクトを受け取る
const props = defineProps({
  item: Object,
  pendingInspection: Object,
  userName: String,
  errors: Object
})

const form = useForm({
    inspectionDate: new Date().toISOString().substr(0, 10),
    inspectionPerson: props.userName,
    details: null,
})


const saveInspection = item => {
  if (confirm('本当に点検しますか？')) {
    form.put(`/inspect_item/${item.id}`)
    // toggleStatus()
  }
}


// 日付フォーマット関数
const formatDate = (timestamp) => {
  const date = new Date(timestamp);
  const year = date.getFullYear();
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const day = ('0' + date.getDate()).slice(-2);
  const hours = ('0' + date.getHours()).slice(-2);
  const minutes = ('0' + date.getMinutes()).slice(-2);
  return `${year}/${month}/${day} ${hours}:${minutes}`;
};

</script>
<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="flex modal__title" id="modal-1-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
            </svg>
            点検
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <!-- フォームの開始 -->
          <form @submit.prevent="saveInspection(item)">
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
                <label for="inspectionSchedule" class="leading-7 text-sm text-blue-900">点検予定日</label>
                <div id="inspectionSchedule" name="inspectionSchedule" class="w-1/2 min-h-[2em] bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  {{ pendingInspection ? pendingInspection.scheduled_date : '予定なし' }}  
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="inspectionDate" class="leading-7 text-sm text-blue-900">点検実施日</label>
                  <div class="relative">
                      <input type="date" id="inspectionDate" name="inspectionDate" v-model="form.inspectionDate" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  </div>
                  <div v-if="errors.inspectionDate" class="font-medium text-red-600">{{ errors.inspectionDate }}</div>
              </div>
              <div class="p-2 w-full">
                <label for="inspectionPerson" class="leading-7 text-sm text-blue-900">点検実施者</label>
                <div>
                  <input type="text" id="inspectionPerson" name="inspectionPerson" v-model="form.inspectionPerson" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  <div v-if="errors.inspectionPerson" class="font-medium text-red-600">{{ errors.inspectionPerson }}</div>       
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
              <button class="flex mx-auto text-white bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">点検を実施する</button>
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
  class="flex mx-auto text-white bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">点検するモーダル</button>
</template>