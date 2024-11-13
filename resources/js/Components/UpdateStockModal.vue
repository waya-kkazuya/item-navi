<script setup lang="ts">
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'
import type { Ref, ComputedRef } from 'vue'
import type { ItemType } from '@/@types/model';
import type { ValidationErrors } from '@/@types/types';

// 親コンポーネントから、itemオブジェクトを受け取る
type Props = {
  item: ItemType;
  userName: string;
  errors: ValidationErrors;
};

const props = defineProps<Props>();

// errorsを更新できるようにする
const localErrors: Ref<ValidationErrors> = ref({...props.errors});

// props.errorsの変更を監視し、localErrorsに反映
watch(() => props.errors, (newErrors: ValidationErrors) => {
  localErrors.value = { ...newErrors };
}, { deep: true });


const activeTab: Ref<string> = ref('出庫');
const activateDecreaseTab = (): void => {
  localErrors.value = {}; // タブ切り替えでバリデーションエラーエラーメッセージをリセット
  activeTab.value = '出庫';
};
const activateIncreaseTab = (): void => {
  localErrors.value = {}; // タブ切り替えでバリデーションエラーエラーメッセージをリセット
  activeTab.value = '入庫';
};


const emit = defineEmits<{(e: 'close') : void}>();

const closeModal = (): void => {
  localErrors.value = {}; // バリデーションエラーメッセージをリセット
  emit('close'); // モーダルウィンドウを閉じるイベント打ち上げ
};

// 出庫タブの出庫後在庫数の表示自動計算
const stockAfterDecrease: ComputedRef<number> = computed(() => props.item.stock - decreaseForm.quantity);
// 入庫タブの入庫後在庫数の表示自動計算
const stockAfterIncrease: ComputedRef<number> = computed(() => props.item.stock + increaseForm.quantity);

const decreaseForm = useForm({
  transaction_date: new Date().toISOString().substr(0, 10) as string,
  operator_name: props.userName as string,
  quantity: 1 as number,
  transaction_type: '出庫' as string,　// enum型で入庫か出庫のみ
});

const decreaseStock = (item: ItemType): void => {
  try {
    if (confirm('本当に出庫処理をしますか？')) {
        decreaseForm.put(`/decreaseStock/${item.id}`, {
          onSuccess: () => {
            closeModal(); // 成功したらモーダルを閉じる
            decreaseForm.quantity = 1; // モーダルのquantityをリセット
          },
        });
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'UpdateStockModal.vue decreaseStock method',
    });
  }
};

const increaseForm = useForm({
    transaction_date: new Date().toISOString().substr(0, 10) as string,
    operator_name: props.userName as string,
    quantity: 1 as number,
    transaction_type: '入庫' as string,　// enum型で入庫か出庫のみ
});

const increaseStock = (item: ItemType): void => {
  try {
    if (confirm('本当に入庫処理をしますか？')) {
        increaseForm.put(`/increaseStock/${item.id}`, {
          onSuccess: () => {
            closeModal();
            increaseForm.quantity = 1; // モーダルのquantityをリセット
          },
        });
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'UpdateStockModal.vue increaseStock method',
    });
  }
};
</script>

<template>
  <div v-if="props.item" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container bg-white w-full md:w-2/3 lg:w-1/3 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0  transition-transform duration-500 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <ul class="flex border-b">
              <li class="mr-1">
                <a :class="{'border-b-4 border-orange-500': activeTab === '出庫', 'border-transparent text-gray-400 opacity-50': activeTab !== '出庫'}" class="flex py-2 px-4 font-semibold cursor-pointer" @click="activateDecreaseTab">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M15 18h6"/></svg>
                  <span class="text-sm md:text-base">出庫</span>
                </a>
              </li>
              <li class="mr-1">
                <a :class="{'border-b-4 border-blue-500': activeTab === '入庫', 'border-transparent text-gray-400 opacity-50': activeTab !== '入庫'}" class="flex py-2 px-4 font-semibold cursor-pointer" @click="activateIncreaseTab">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M18 21v-6M15 18h6"/></svg>
                  <span class="text-sm md:text-base">入庫</span>
                </a>
              </li>
            </ul>
          <button @click="closeModal" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div v-if="activeTab === '出庫'" class="p-4">
            <!-- フォームの開始 -->
            <form @submit.prevent="decreaseStock(item)">
              <div class="">
                <div class="p-2 w-full">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                        備品名
                    </label>
                    <div id="name" name="name" class="w-full md:w-full lg:full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.name }}
                    </div>
                </div>
                <div class="p-2 w-full">
                    <label for="transaction_date" class="leading-7 text-xs md:text-base text-blue-900">
                      出庫日
                    </label>
                    <div class="relative">
                        <input type="date" id="transaction_date" name="transaction_date" v-model="decreaseForm.transaction_date" class="w-full md:w-full lg:w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    </div>
                    <div v-if="localErrors.transaction_date" class="font-medium text-xs md:text-base text-red-600">{{ localErrors.transaction_date }}</div>
                </div>
                <div class="p-2 w-full">
                  <label for="operator_name" class="leading-7 text-xs md:text-base text-blue-900">実施者</label>
                  <div>
                    <input type="text" id="operator_name" name="operator_name" v-model="decreaseForm.operator_name" class="w-full md:w-full lg:w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="localErrors.operator_name" class="font-medium text-xs md:text-base text-red-600">{{ localErrors.operator_name }}</div>       
                  </div>
                </div>
                <div class="flex items-end md:justify-start space-x-2 md:items-end p-2 w-full md:w-full lg:w-full">
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      現在在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.stock }}
                    </div>
                  </div>
                  <div class="w-1/8 md:w-1/8 lg:w-1/8 mb-2 flex items-center justify-center">
                    <div>
                      <svg class="w-4 md:w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 17l5-5-5-5M6 17l5-5-5-5"/></svg>
                    </div>
                  </div>
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      出庫後
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ stockAfterDecrease  }}
                    </div>
                  </div>
                  <div class="w-1/8 md:w-1/8 lg:w-1/8">
                    <svg class="mb-2 size-5 w-4 md:w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9 20.247 6-16.5" />
                    </svg>
                  </div>
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      通知在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.minimum_stock  }}
                    </div>
                  </div>

                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="quantity" class="leading-7 text-xs md:text-base text-blue-900">出庫数</label>
                  <div class="flex items-center">
                    <input type="number" id="quantity" name="quantity" v-model="decreaseForm.quantity" min="1" :max="item.stock" step="1" class="w-1/3 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div id="unit" name="unit" class="w-1/2 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-7">
                      {{ item.unit.name }}
                    </div>                        
                  </div>
                  <div v-if="localErrors.quantity" class="font-medium text-xs md:text-md text-red-600">{{ localErrors.quantity }}</div>
              </div>
              <div class="mt-4 p-2 w-full">
                <button :class="{
                    'flex mx-auto text-white text-sm bg-orange-500 border-0 py-2 px-8 focus:outline-none hover:bg-orange-600 rounded': item.stock !== 0,
                    'flex mx-auto text-white text-sm bg-gray-500 border-0 py-2 px-8 focus:outline-none cursor-not-allowed rounded': item.stock === 0
                  }"
                  :disabled="item.stock === 0"
                >出庫処理をする</button>
              </div>
            </form>
          </div>


          <div v-if="activeTab === '入庫'" class="p-4">
            <!-- フォームの開始 -->
            <form @submit.prevent="increaseStock(item)">
              <div>
                <div class="p-2 w-full">
                  <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      備品名
                  </label>
                  <div id="name" name="name" class="w-full md:w-full lg:w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                      {{ item.name }}
                  </div>
                </div>
                <div class="p-2 w-full">
                  <label for="transaction_date" class="leading-7 text-xs md:text-base text-blue-900">
                    入庫日
                  </label>
                  <div class="relative">
                      <input type="date" id="transaction_date" name="transaction_date" v-model="increaseForm.transaction_date" class="w-full md:w-full lg:w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  </div>
                  <div v-if="localErrors.transaction_date" class="font-medium text-xs md:text-base text-red-600">{{ localErrors.transaction_date }}</div>
                </div>
                <div class="p-2 w-full">
                  <label for="operator_name" class="leading-7 text-xs md:text-base text-blue-900">
                    実施者
                  </label>
                  <div>
                    <input type="text" id="operator_name" name="operator_name" v-model="increaseForm.operator_name" class="w-full md:w-full lg:w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div v-if="localErrors.operator_name" class="font-medium text-xs md:text-base text-red-600">{{ localErrors.operator_name }}</div>       
                  </div>
                </div>
                <div class="flex items-end md:justify-start space-x-2 md:items-end p-2 w-full md:w-full lg:w-full">
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      現在在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                        {{ item.stock }}
                    </div>
                  </div>
                  <div class="w-1/8 md:w-1/8 lg:w-1/8 mb-2 flex items-center justify-center">
                    <div>  
                      <svg class="w-4 md:w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 17l5-5-5-5M6 17l5-5-5-5"/></svg>
                    </div>
                  </div>
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      入庫後
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ stockAfterIncrease  }}
                    </div>
                  </div>
                  <div class="w-1/8 md:w-1/8 lg:w-1/8">
                    <svg class="mb-2 size-5 w-4 md:w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m9 20.247 6-16.5" />
                    </svg>
                  </div>
                  <div class="w-1/4 md:w-1/4 lg:w-1/4">
                    <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      通知在庫数
                    </label>
                    <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                          {{ item.minimum_stock  }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="quantity" class="leading-7 text-xs md:text-base text-blue-900">入庫数</label>
                  <div class="flex items-centers">
                    <input type="number" id="quantity" name="quantity" v-model="increaseForm.quantity" min="1" max="100" step="1" class="w-1/3 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                    <div id="unit" name="unit" class="w-1/2  text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-7">
                      {{ item.unit.name }}
                    </div>    
                  </div>
                  <div v-if="localErrors.quantity" class="font-medium text-xs md:text-base text-red-600">{{ localErrors.quantity }}</div>       
              </div>
              <div class="mt-4 p-2 w-full">
                <button class="flex mx-auto text-white text-sm bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">
                  入庫処理をする
                </button>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>