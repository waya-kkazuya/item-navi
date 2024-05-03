<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  items: Array
})

</script>

<template>
    <Head title="備品一覧" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              備品一覧
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <div class="flex pl-4 mt-4 lg:w-2/3 w-full mx-auto">
                              <Link as="button" :href="route('items.create')" class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">新規登録</Link>
                            </div>
                          </div>

                          <div class="min-w-full overflow-auto">
                          <table class="table-auto min-w-full text-left whitespace-no-wrap">
                            <thead>
                              <tr>
                                <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Id</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">備品名</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">カテゴリ</th>
                                <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">画像1パス</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">在庫数</th>
                                <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">利用状況</th>
                                <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">使用者</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">利用場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">保管場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得区分</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得価額</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">取得年月日</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">点検予定日</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">廃棄予定日</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">メーカー</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">製品番号</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">購入先</th>
                                <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">備考</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="item in items" :key="item.id">
                                <td class="border-b-2 border-gray-200 px-4 py-3">
                                  <!-- <Link class="text-blue-400" :href="route('items.show', { item: item.id })"> -->
                                    {{ item.id }}
                                  <!-- </Link> -->
                                </td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.category }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.image_path1 }}<img src="" alt=""></td>
                                <td class="border-b-2 border-gray-200 px-4 py-3"><span>{{ item.stocks }}</span> <button class="ml-2 p-2 text-white bg-gray-400 border-0 focus:outline-none hover:bg-gray-500 rounded">入出庫</button></td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.usage_status }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.end_user }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.location_of_use }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.storage_location }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.acquisition_category }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.date_of_acquisition }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.inspection_schedule }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.disposal_schedule }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.manufacturer }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.product_number }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3"><a :href="item.vendor_website_url" class="text-blue-500">{{ item.vendor }}</a></td>
                                <td class="overflow-hidden whitespace-nowrap border-b-2 border-gray-200 px-4 py-3">{{ item.remarks }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                          
                      </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
