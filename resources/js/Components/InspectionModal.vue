<script setup lang="ts">
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import type { Ref } from 'vue';
import type { ItemType, InspectionType } from '@/@types/model';
import type { ValidationErrors } from '@/@types/types';

const isShow: Ref<boolean> = ref(false);
const toggleStatus = (): void => { isShow.value = !isShow.value };

type Props = {
  item: ItemType;
  uncompleted_inspection: InspectionType;
  userName: string;
  errors: ValidationErrors;
};

const props = defineProps<Props>();

const form = useForm({
    inspection_date: new Date().toISOString().substr(0, 10),
    inspection_person: props.userName,
    details: null,
});

const saveInspection = (item: ItemType): void => {
  try {
    if (confirm('本当に点検しますか？')) {
      form.put(`/inspect_item/${item.id}`, {
        onSuccess: () => {
          toggleStatus();
        },
      });
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'InspectionModal.vue saveInspection method',
    });
  }
};
</script>

<template>
  <div v-show="isShow" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-end md:items-center md:justify-center z-50" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container bg-white w-full md:w-2/3 lg:w-1/3 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0  transition-transform duration-500 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="flex modal__title" id="modal-1-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 0 1-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 1 1-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 0 1 6.336-4.486l-3.276 3.276a3.004 3.004 0 0 0 2.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008Z" />
            </svg>
            <span class="text-sm lg:text-lg">点検</span>
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <!-- フォームの開始 -->
          <form @submit.prevent="saveInspection(item)">
            <div>
              <div class="p-2 w-full">
                  <label for="name" class="leading-7 text-xs md:text-base text-blue-900">
                      備品名
                  </label>
                  <div id="name" name="name" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                      {{ item.name }}
                  </div>
              </div>
              <div class="p-2 w-full">
                <label for="inspection_scheduled_date" class="leading-7 text-xs md:text-base text-blue-900">
                  点検予定日
                </label>
                <div id="inspection_scheduled_date" name="inspection_scheduled_date" class="w-full bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  {{ uncompleted_inspection ? uncompleted_inspection.inspection_scheduled_date : '予定なし' }}  
                </div>
              </div>
              <div class="p-2 w-full">
                  <label for="inspection_date" class="leading-7 text-xs md:text-base text-blue-900">
                    点検実施日
                    <span class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md">必須</span>
                  </label>
                  <div class="relative">
                      <input type="date" id="inspection_date" name="inspection_date" v-model="form.inspection_date" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  </div>
                  <div v-if="errors.inspection_date" class="font-medium text-xs md:text-base text-red-600">{{ errors.inspection_date }}</div>
              </div>
              <div class="p-2 w-full">
                <label for="inspection_person" class="leading-7 text-xs md:text-base text-blue-900">
                  点検実施者
                  <span class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md">必須</span>
                </label>
                <div>
                  <input type="text" id="inspection_person" name="inspection_person" v-model="form.inspection_person" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                  <div v-if="errors.inspection_person" class="font-medium text-xs md:text-base text-red-600">{{ errors.inspection_person }}</div>
                </div>
              </div>
              <div class="p-2 w-full">
                <label for="details" class="leading-7 text-xs md:text-base text-blue-900">
                  詳細情報
                  <span class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md">必須</span>
                </label>
                <div>
                  <textarea id="inspectionDetails" name="details" maxlength="500" v-model="form.details" class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-xs md:text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
                  <div v-if="errors.details" class="font-medium text-xs md:text-base text-red-600">{{ errors.details }}</div>
                </div>
              </div>
            </div>
            <div class="p-2 w-full">
              <button class="flex mx-auto text-white text-xs md:text-sm bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">
                点検を実施する
              </button>
            </div>
          </form>
        </main>
      </div>
    </div>
  </div>
  <button @click="toggleStatus" type="button" id="inspectionButton" data-micromodal-trigger="modal-1" href='javascript:;' class="flex mx-auto md:ml-4 text-xs md:text-sm text-white bg-sky-500 border-0 py-2 px-8 focus:outline-none hover:bg-sky-600 rounded">
    点検する
  </button>
</template>