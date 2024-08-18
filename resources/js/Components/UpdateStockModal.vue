<script setup>
import axios from 'axios';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue'


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}

// 親コンポーネントから、itemオブジェクトを受け取る
const props = defineProps({
  item: Object,
  userName: String,
  errors: Object
})

const form = useForm({
    transactionDate: new Date().toISOString().substr(0, 10),
    operatorName: props.userName,
    quantity: 0,
    transactionType: '出庫',　// enum型で入庫か出庫のみ
})

const stockAfterChange = computed(() => props.item.stock - form.quantity)


const updateStock = item => {
  if (confirm('本当に出庫処理をしますか？')) {
    form.put(`/updateStock/${item.id}`)
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
          <!-- <h2 class="flex modal__title" id="modal-1-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
            </svg>
            出庫
          </h2> -->
          <ul class="flex border-b">
              <li class="mr-1">
                <a :class="{'border-b-4 border-orange-500': form.transactionType === '出庫', 'border-transparent text-gray-400 opacity-50': form.transactionType !== '出庫'}" class="flex inline-block py-2 px-4 font-semibold cursor-pointer" @click="form.transactionType = '出庫'">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M15 18h6"/></svg>
                  出庫
                </a>
              </li>
              <li class="mr-1">
                <a :class="{'border-b-4 border-blue-500': form.transactionType === '入庫', 'border-transparent text-gray-400 opacity-50': form.transactionType !== '入庫'}" class="flex inline-block py-2 px-4 font-semibold cursor-pointer" @click="form.transactionType = '入庫'">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M18 21v-6M15 18h6"/></svg>
                  入庫
                </a>
              </li>
            </ul>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div v-if="form.transactionType === '出庫'" class="p-4">
            <!-- フォームの開始 -->
            <form @submit.prevent="updateStock(item)">
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
                    <label for="transactionDate" class="leading-7 text-sm text-blue-900">出庫日</label>
                    <div class="relative">
                        <input type="date" id="transactionDate" name="transactionDate" v-model="form.transactionDate" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    </div>
                    <div v-if="errors.transactionDate" class="font-medium text-red-600">{{ errors.transactionDate }}</div>
                </div>
                <div class="p-2 w-full">
                  <label for="operatorName" class="leading-7 text-sm text-blue-900">実施者</label>
                  <div>
                    <input type="text" id="operatorName" name="operatorName" v-model="form.operatorName" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="errors.operatorName" class="font-medium text-red-600">{{ errors.operatorName }}</div>       
                  </div>
                </div>
                <div class="flex p-2 w-full">
                  <div class="w-1/4">
                    <label for="name" class="leading-7 text-sm text-blue-900">
                      現在在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.stock }}
                    </div>
                  </div>
                  <div class="flex items-center mx-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 17l5-5-5-5M6 17l5-5-5-5"/></svg>
                  </div>
                  <div class="w-1/4">
                    <label for="name" class="leading-7 text-sm text-blue-900">
                      出庫後の在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ stockAfterChange  }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="quantity" class="leading-7 text-sm text-blue-900">出庫数</label>
                  <div>
                    <input type="number" id="quantity" name="quantity" v-model="form.quantity" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="errors.quantity" class="font-medium text-red-600">{{ errors.quantity }}</div>       
                  </div>
              </div>
              <div class="mt-4 p-2 w-full">
                <button class="flex mx-auto text-white bg-orange-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">出庫処理をする</button>
              </div>
              
              <p>Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also, <code>esc</code> to close modal.</p>
            </form>
          </div>
          <div v-if="form.transactionType === '入庫'" class="p-4">
            <!-- フォームの開始 -->
            <form @submit.prevent="updateStock(item)">
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
                    <label for="transactionDate" class="leading-7 text-sm text-blue-900">入庫日</label>
                    <div class="relative">
                        <input type="date" id="transactionDate" name="transactionDate" v-model="form.transactionDate" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    </div>
                    <div v-if="errors.transactionDate" class="font-medium text-red-600">{{ errors.transactionDate }}</div>
                </div>
                <div class="p-2 w-full">
                  <label for="operatorName" class="leading-7 text-sm text-blue-900">実施者</label>
                  <div>
                    <input type="text" id="operatorName" name="operatorName" v-model="form.operatorName" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="errors.operatorName" class="font-medium text-red-600">{{ errors.operatorName }}</div>       
                  </div>
                </div>
                <div class="p-2 w-full">
                    <label for="name" class="leading-7 text-sm text-blue-900">
                      現在在庫数
                    </label>
                    <div id="name" name="name" class="w-1/2 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.stock }}
                    </div>
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="quantity" class="leading-7 text-sm text-blue-900">入庫数</label>
                  <div>
                    <input type="number" id="quantity" name="quantity" v-model="form.quantity" class="w-1/2 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="errors.quantity" class="font-medium text-red-600">{{ errors.quantity }}</div>       
                  </div>
              </div>
              <div class="mt-4 p-2 w-full">
                <button class="flex mx-auto text-white bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">入庫処理をする</button>
              </div>
              
              <p>Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also, <code>esc</code> to close modal.</p>
            </form>
          </div>
        </main>
        <!-- <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn modal__btn-primary">Continue</button>
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
        </footer> -->
      </div>
    </div>
  </div>
  <button @click="toggleStatus" type="button" data-micromodal-trigger="modal-1" href='javascript:;'
  class="flex mx-auto text-white bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
  </svg>
  入出庫をする
  </button>
</template>

<!-- <style>
.tabs {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
}

.tabs li {
  padding: 10px 20px;
  cursor: pointer;
  font-size: 1.5em; /* h2タグの文字の大きさに合わせる */
}

.tabs li.active {
  background-color: #007bff;
  color: white;
}
</style> -->