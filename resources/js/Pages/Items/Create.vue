<script setup lang="ts">
import axios from 'axios';
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

import type { Ref } from 'vue';
import type {
  CategoryType,
  LocationType,
  UnitType,
  UsageStatusType,
  AcquisitionMethodType,
} from '@/@types/model';
import type { ValidationErrors } from '@/@types/types';

type Props = {
  categories: CategoryType[];
  locations: LocationType[];
  units: UnitType[];
  usageStatuses: UsageStatusType[];
  acquisitionMethods: AcquisitionMethodType[];
  name: String; //リクエスト画面から新規登録画面を開く場合に使用
  category_id: Number;
  location_of_use_id: Number;
  manufacturer: String;
  price: Number;
  errors: ValidationErrors;
};

const props = defineProps<Props>();

const form = useForm({
  image_file: null as File | null,
  name: props.name ?? (null as string | null),
  category_id: props.category_id ?? (0 as number | null),
  stock: 1 as number, // 初期値=1
  unit_id: 1 as number,
  minimum_stock: 0 as number,
  notification: true as boolean,
  usage_status_id: 0 as number,
  end_user: null as string | null,
  location_of_use_id: props.location_of_use_id ?? (0 as number),
  storage_location_id: 0 as number,
  acquisition_method_id: 0 as number,
  acquisition_source: null as string | null,
  price: props.price ?? (0 as number),
  date_of_acquisition: new Date().toISOString().substr(0, 10) as string, //初期値は当日の日付
  manufacturer: props.manufacturer ?? (null as string | null),
  product_number: null as string | null,
  inspection_scheduled_date: null as Date | null, // 初期値null
  disposal_scheduled_date: null as Date | null, // 初期値null
  remarks: null as string | null,
});

const file_preview_src: Ref<string> = ref('');
const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;

  // nullチェックを厳密に行う
  if (target && target.files && target.files[0]) {
    form.image_file = target.files[0];
    file_preview_src.value = URL.createObjectURL(form.image_file);
  }
};

// router.visitではuseFormの入力値保持機能は使えない
// form.postなら入力値保持機能(old関数))が使える
const storeItem = (): void => {
  try {
    form.post('/items');
  } catch (error: any) {
    console.error('Items/Create.vue storeItem method error:', error.message);
  }
};

// 「×」ボタンで日付リセット
const clearInspectionSchedule = () => {
  form.inspection_scheduled_date = null;
};
const clearDisposalSchedule = () => {
  form.disposal_scheduled_date = null;
};
</script>

<template>
  <Head title="備品新規登録" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">備品新規登録</h2>
    </template>

    <div class="py-2 md:py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- ここから白い背景 -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <section class="text-gray-600 body-font relative">
              <FlashMessage />
              <form @submit.prevent="storeItem" enctype="multipart/form-data">
                <div class="container px-5 py-8 mx-auto">
                  <div class="md:w-full mx-auto">
                    <div class="-m-2">
                      <div class="grid grid-cols-1 md:grid-cols-3 md:gap-4">
                        <div class="col-span-1">
                          <div class="p-4 border mb-8 md:mb-0 flex justify-center">
                            <div class="p-2 w-full">
                              <label
                                for="imageFile"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                画像
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <label for="imageFile" class="cursor-pointer md:flex">
                                <input
                                  type="file"
                                  @change="handleFileUpload"
                                  multiple
                                  accept="image/png, image/jpeg, image/jpg"
                                  id="imageFile"
                                  name="imageFile"
                                  class="sr-only"
                                />
                                <div
                                  class="md:mt-1 w-48 h-48 md:w-40 md:h-40 border border-gray-300 rounded-md shadow-sm flex items-center justify-center"
                                >
                                  <template v-if="file_preview_src">
                                    <img
                                      :src="file_preview_src"
                                      alt="画像プレビュー"
                                      class="w-full h-full object-cover rounded-md"
                                    />
                                  </template>
                                  <template v-else>
                                    <PhotoIcon class="size-6" />
                                  </template>
                                </div>
                              </label>
                              <div
                                v-if="errors.image_file"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.image_file }}
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-span-2">
                          <div class="p-4 border mb-4">
                            <div class="p-2 w-full">
                              <label
                                for="name"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                備品名
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <input
                                type="text"
                                id="name"
                                name="name"
                                v-model="form.name"
                                class="block md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div
                                v-if="errors.name"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.name }}
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <label
                                for="category_id"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                カテゴリ
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                > </label
                              ><br />
                              <select
                                name="category_id"
                                id="category_id"
                                v-model="form.category_id"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
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
                              <div
                                v-if="errors.category_id"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.category_id }}
                              </div>
                            </div>
                          </div>

                          <div class="p-4 border mb-4">
                            <div class="pl-2 w-full">
                              <label
                                for="stock"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                在庫数
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                > </label
                              ><br />
                              <input
                                type="number"
                                id="stock"
                                name="stock"
                                v-model="form.stock"
                                min="0"
                                class="md:mt-1 w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <select
                                name="unit"
                                id="unit"
                                v-model="form.unit_id"
                                class="md:w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              >
                                <option v-for="unit in units" :value="unit.id" :key="unit.id">
                                  {{ unit.name }}
                                </option>
                              </select>
                              <div
                                v-if="errors.stock"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.stock }}
                              </div>
                              <div
                                v-if="errors.unit"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.unit }}
                              </div>
                            </div>
                            <div v-show="form.category_id == 1" class="mt-4 pl-2 w-full">
                              <label
                                for="minimum_stock"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                通知在庫数 </label
                              ><br />
                              <input
                                type="number"
                                id="minimum_stock"
                                name="minimum_stock"
                                v-model="form.minimum_stock"
                                min="0"
                                class="w-1/4 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <select
                                name="unit"
                                id="unit"
                                v-model="form.unit_id"
                                class="md:w-1/6 bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              >
                                <option v-for="unit in units" :value="unit.id" :key="unit.id">
                                  {{ unit.name }}
                                </option>
                              </select>
                              <div
                                v-if="errors.minimum_stock"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.minimum_stock }}
                              </div>
                            </div>
                            <div v-show="form.category_id == 1" class="mt-4 pl-2 w-full">
                              <input
                                type="checkbox"
                                id="notification"
                                v-model="form.notification"
                              />
                              <label for="notification" class="ml-1 text-xs md:text-base">
                                在庫数が通知在庫数以下になったら通知する
                              </label>
                            </div>
                          </div>

                          <div class="p-4 border mb-4">
                            <div class="p-2 w-full">
                              <label
                                for="usage_status_id"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                利用状況
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <select
                                name="usage_status_id"
                                id="usage_status_id"
                                v-model="form.usage_status_id"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              >
                                <option :value="0">選択してください</option>
                                <option
                                  v-for="usageStatus in usageStatuses"
                                  :value="usageStatus.id"
                                  :key="usageStatus.id"
                                >
                                  {{ usageStatus.name }}
                                </option>
                              </select>
                              <div
                                v-if="errors.usage_status_id"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.usage_status_id }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="end_user"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                使用者
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <input
                                type="text"
                                id="end_user"
                                name="end_user"
                                v-model="form.end_user"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div v-if="errors.end_user" class="font-medium text-red-600">
                                {{ errors.end_user }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="location_of_use_id"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                利用場所
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <select
                                name="location_of_use_id"
                                id="location_of_use_id"
                                v-model="form.location_of_use_id"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
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
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.location_of_use_id }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="storage_location_id"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                保管場所
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <select
                                name="storage_location_id"
                                id="storage_location_id"
                                v-model="form.storage_location_id"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
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
                                v-if="errors.storage_location_id"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.storage_location_id }}
                              </div>
                            </div>
                          </div>

                          <div class="p-4 border mb-4">
                            <div class="p-2 w-full">
                              <label
                                for="acquisition_method_id"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                取得区分
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <select
                                name="acquisition_method_id"
                                id="acquisition_method_id"
                                v-model="form.acquisition_method_id"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              >
                                <option :value="0">選択してください</option>
                                <option
                                  v-for="acquisitionMethod in acquisitionMethods"
                                  :key="acquisitionMethod.id"
                                  :value="acquisitionMethod.id"
                                >
                                  {{ acquisitionMethod.name }}
                                </option>
                              </select>
                              <div
                                v-if="errors.acquisition_method_id"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.acquisition_method_id }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="acquisition_source"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                取得先
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <input
                                type="text"
                                id="acquisition_source"
                                name="acquisition_source"
                                v-model="form.acquisition_source"
                                placeholder="例 Amazonなど"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div
                                v-if="errors.acquisition_source"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.acquisition_source }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="price"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                取得価額
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <input
                                type="number"
                                id="price"
                                name="price"
                                v-model="form.price"
                                min="0"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div
                                v-if="errors.price"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.price }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="date_of_acquisition"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                取得年月日
                                <span
                                  class="ml-1 mr-2 bg-red-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >必須</span
                                >
                              </label>
                              <div class="relative">
                                <input
                                  type="date"
                                  id="date_of_acquisition"
                                  name="date_of_acquisition"
                                  v-model="form.date_of_acquisition"
                                  class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                />
                              </div>
                              <div
                                v-if="errors.date_of_acquisition"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.date_of_acquisition }}
                              </div>
                            </div>
                          </div>

                          <div class="p-4 border mb-4">
                            <div class="p-2 w-full">
                              <label
                                for="manufacturer"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                メーカー
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <input
                                type="text"
                                id="manufacturer"
                                name="manufacturer"
                                v-model="form.manufacturer"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div
                                v-if="errors.manufacturer"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.manufacturer }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="product_number"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                製品番号
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <input
                                type="text"
                                id="product_number"
                                name="product_number"
                                v-model="form.product_number"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                              />
                              <div
                                v-if="errors.product_number"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.product_number }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="remarks"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                備考
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <textarea
                                id="remarks"
                                name="remarks"
                                maxlength="500"
                                v-model="form.remarks"
                                class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-xs md:text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"
                              ></textarea>
                              <div
                                v-if="errors.remarks"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.remarks }}
                              </div>
                            </div>
                          </div>

                          <div class="p-4 border mb-8">
                            <div class="p-2 w-full">
                              <label
                                for="inspection_scheduled_date"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                点検予定日
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <div class="relative">
                                <input
                                  type="date"
                                  id="inspection_scheduled_date"
                                  name="inspection_scheduled_date"
                                  v-model="form.inspection_scheduled_date"
                                  class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                />
                                <button
                                  type="button"
                                  @click="clearInspectionSchedule"
                                  class="absolute right-12 inset-y-0 flex items-center text-gray-500"
                                >
                                  <XMarkIcon class="size-5" />
                                </button>
                              </div>
                              <div
                                v-if="errors.inspection_scheduled_date"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.inspection_scheduled_date }}
                              </div>
                            </div>

                            <div class="p-2 w-full">
                              <label
                                for="disposal_scheduled_date"
                                class="leading-7 text-xs md:text-base text-blue-900"
                              >
                                廃棄予定日
                                <span
                                  class="ml-1 mr-2 bg-gray-400 text-white text-xs md:text-base lg:text-xs py-1 px-2 rounded-md"
                                  >任意</span
                                >
                              </label>
                              <div class="relative">
                                <input
                                  type="date"
                                  id="disposal_scheduled_date"
                                  name="disposal_scheduled_date"
                                  v-model="form.disposal_scheduled_date"
                                  class="md:mt-1 w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-xs md:text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                />
                                <button
                                  type="button"
                                  @click="clearDisposalSchedule"
                                  class="absolute right-12 inset-y-0 flex items-center text-gray-500"
                                >
                                  <XMarkIcon class="size-5" />
                                </button>
                              </div>
                              <div
                                v-if="errors.disposal_scheduled_date"
                                class="font-medium text-red-600 text-xs md:text-base"
                              >
                                {{ errors.disposal_scheduled_date }}
                              </div>
                            </div>
                          </div>
                          <div class="w-full">
                            <button
                              id="item_register"
                              class="flex mx-auto text-white text-xs md:text-base bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded"
                            >
                              登録する
                            </button>
                          </div>
                        </div>
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
