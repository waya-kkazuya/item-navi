<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/FlashMessage.vue';

defineProps({
  wishes: Array
})

</script>

<template>
    <Head title="ウィッシュリスト" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              ウィッシュリスト
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                      <section class="text-gray-600 body-font">
                          <div class="container px-5 py-8 mx-auto">
                            <FlashMessage />
                            <div class="flex pl-4 mt-4 lg:w-2/3 w-full mx-auto">
                              <Link as="button" :href="route('wishes.create')" class="flex ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">リクエストする</Link>
                            </div>
                          </div>

                          <div class="min-w-full overflow-auto">
                          <table class="table-auto min-w-full text-left whitespace-no-wrap">
                            <thead>
                              <tr>
                                <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">ID</th>
                                <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">商品名</th>
                                <th class="min-w-20 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">カテゴリ</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">メーカー</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">値段</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">利用場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">保管場所</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">参考サイト</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">申請者</th>
                                <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">申請者コメント</th>
                                <th class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">ステータス</th>
                                <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">管理者コメント</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="wish in wishes" :key="wish.id">
                                <td class="min-w-16 border-b-2 border-gray-200 px-4 py-3">
                                  <Link class="text-blue-400" :href="route('wishes.show', { wish: wish.id })">
                                    {{ wish.id }}
                                  </Link>
                                </td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.category.name }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.vendor }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.price }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.location_of_use }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.storage_location }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.reference_site_url }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.applicant }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.comment_from_applicant }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.decision_status }}</td>
                                <td class="border-b-2 border-gray-200 px-4 py-3">{{ wish.comment_from_administrator }}</td>
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
