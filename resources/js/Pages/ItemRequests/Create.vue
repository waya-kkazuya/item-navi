<script setup lang="ts">
import axios from 'axios';
import { Head, useForm } from '@inertiajs/vue3';

import FlashMessage from '@/Components/FlashMessage.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import type { CategoryType, LocationType } from '@/@types/model';
import type { ValidationErrors } from '@/@types/types';

defineProps<{
  categories: CategoryType[];
  locations: LocationType[];
  errors: ValidationErrors;
}>();

const form = useForm({
  name: null as string | null,
  category_id: 0 as number,
  location_of_use_id: 0 as number,
  requestor: null as string | null,
  remarks_from_requestor: null as string | null,
  request_status_id: null as number | null,
  manufacturer: null as string | null,
  reference: null as string | null,
  price: 0 as number,
});

// router.visitではuseFormの入力値保持機能は使えない
// form.postなら入力値保持機能(old関数))が使える
const storeItemRequest = () => {
  try {
    form.post('/item-requests');
  } catch (error: any) {
    console.error('ItemRequests/Create.vue storeItemRequest method error:', error.message);
  }
};
</script>

<template>
  <Head title="リクエスト登録" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">リクエスト登録</h2>
    </template>

    <div class="py-2 md:py-4">
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
                      <div class="p-3 border bordr-4 mb-4">
                        <div class="py-3 px-4 w-full">
                          <label for="name" class="leading-7 text-sm text-blue-900">
                            商品名
                            <span
                              class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md"
                              >必須</span
                            >
                          </label>
                          <input
                            type="text"
                            id="name"
                            name="name"
                            v-model="form.name"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                          <div v-if="errors.name" class="font-medium text-red-600 text-sm">
                            {{ errors.name }}
                          </div>
                        </div>
                        <div class="py-3 px-4 w-full">
                          <label for="category_id" class="leading-7 text-sm text-blue-900">
                            カテゴリ
                            <span
                              class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md"
                              >必須</span
                            > </label
                          ><br />
                          <select
                            name="category_id"
                            id="category_id"
                            v-model="form.category_id"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          >
                            <option :value="0">選択してください</option>
                            <option
                              v-for="category in categories"
                              :value="category.id"
                              :key="category.id"
                            >
                              {{ category.name }}
                            </option>
                          </select>
                          <div v-if="errors.category_id" class="font-medium text-red-600 text-sm">
                            {{ errors.category_id }}
                          </div>
                        </div>
                      </div>

                      <div class="p-3 border bordr-4 mb-4">
                        <div class="py-3 px-4 w-full">
                          <label for="location_of_use_id" class="leading-7 text-sm text-blue-900">
                            利用場所
                            <span
                              class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md"
                              >必須</span
                            >
                          </label>
                          <select
                            name="location_of_use_id"
                            id="location_of_use_id"
                            v-model="form.location_of_use_id"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          >
                            <option :value="0">選択してください</option>
                            <option
                              v-for="location in locations"
                              :key="location.id"
                              :value="location.id"
                            >
                              {{ location.name }}
                            </option>
                          </select>
                          <div
                            v-if="errors.location_of_use_id"
                            class="font-medium text-red-600 text-sm"
                          >
                            {{ errors.location_of_use_id }}
                          </div>
                        </div>
                      </div>

                      <div class="p-3 border bordr-4 mb-4">
                        <div class="py-3 px-4 w-full">
                          <label for="manufacturer" class="leading-7 text-sm text-blue-900">
                            メーカー
                            <span
                              class="ml-1 mr-2 bg-gray-400 text-white text-xs py-1 px-2 rounded-md"
                              >任意</span
                            >
                          </label>
                          <input
                            type="text"
                            id="manufacturer"
                            name="manufacturer"
                            v-model="form.manufacturer"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                          <div v-if="errors.manufacturer" class="font-medium text-red-600 text-sm">
                            {{ errors.manufacturer }}
                          </div>
                        </div>

                        <div class="py-3 px-4 w-full">
                          <label for="reference" class="leading-7 text-sm text-blue-900">
                            参考サイト
                            <span
                              class="ml-1 mr-2 bg-gray-400 text-white text-xs py-1 px-2 rounded-md"
                              >任意</span
                            >
                          </label>
                          <input
                            type="text"
                            id="reference"
                            name="reference"
                            v-model="form.reference"
                            placeholder="例 Amazonなど"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                          <div v-if="errors.reference" class="font-medium text-red-600 text-sm">
                            {{ errors.reference }}
                          </div>
                        </div>

                        <div class="py-3 px-4 w-full">
                          <label for="price" class="leading-7 text-sm text-blue-900">
                            価格
                            <span
                              class="ml-1 mr-2 bg-gray-400 text-white text-xs py-1 px-2 rounded-md"
                              >任意</span
                            >
                          </label>
                          <input
                            type="number"
                            id="price"
                            name="price"
                            v-model="form.price"
                            min="0"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                          <div v-if="errors.price" class="font-medium text-red-600 text-sm">
                            {{ errors.price }}
                          </div>
                        </div>
                      </div>

                      <div class="p-3 border bordr-4 mb-4">
                        <div class="py-3 px-4 w-full">
                          <label for="requestor" class="leading-7 text-sm text-blue-900">
                            申請者
                            <span
                              class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md"
                              >必須</span
                            >
                          </label>
                          <input
                            type="text"
                            id="requestor"
                            name="requestor"
                            v-model="form.requestor"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-sm outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                          <div v-if="errors.requestor" class="font-medium text-red-600 text-sm">
                            {{ errors.requestor }}
                          </div>
                        </div>

                        <div class="py-3 px-4 w-full">
                          <label
                            for="remarks_from_requestor"
                            class="leading-7 text-sm text-blue-900"
                          >
                            申請理由
                            <span
                              class="ml-1 mr-2 bg-red-400 text-white text-xs py-1 px-2 rounded-md"
                              >必須</span
                            >
                          </label>
                          <textarea
                            id="remarks_from_requestor"
                            name="remarks_from_requestor"
                            maxlength="500"
                            v-model="form.remarks_from_requestor"
                            class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-sm outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"
                          ></textarea>
                          <div
                            v-if="errors.remarks_from_requestor"
                            class="font-medium text-red-600 text-sm"
                          >
                            {{ errors.remarks_from_requestor }}
                          </div>
                        </div>
                      </div>

                      <div class="py-3 px-4 w-full">
                        <!-- <Link>タグでキャンセルボタン -->
                        <button
                          id="createRequest"
                          class="flex mx-auto text-white text-sm bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded"
                        >
                          リクエストする
                        </button>
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
