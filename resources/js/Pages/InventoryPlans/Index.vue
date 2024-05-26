<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  inventoryPlans: Array
})


</script>

<template>
    <Head title="棚卸計画管理" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              棚卸計画管理
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                      <section class="text-gray-600 body-font">
                        <div class="container px-5 py-24 mx-auto">

                          <div class="mb-4 flex pl-4 mt-4 lg:w-2/3 w-full mx-auto">
                            <Link as="button" :href="route('inventory_plans.create')" class="ml-4 text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded">棚卸計画を作成する</Link>
                          </div>
                          <div class="lg:w-2/3 w-full mx-auto overflow-auto">
                            <table class="table-auto w-full text-left whitespace-no-wrap">
                              <thead>
                                <tr>
                                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">進捗</th>
                                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">棚卸計画名</th>
                                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">実施予定期間</th>
                                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">レポート出力</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="inventoryPlan in inventoryPlans" :key="inventoryPlan.id">
                                  <td class="px-4 py-3">{{ inventoryPlan.status ? '完了' : '未完了' }}</td>
                                  <td class="px-4 py-3">{{ inventoryPlan.name }}</td>
                                  <td class="px-4 py-3">{{ inventoryPlan.start_date }} ~ {{ inventoryPlan.end_date }}</td>
                                  <td class="border-b-2 border-gray-200 px-4 py-3"><button @click="" class="ml-2 p-2 text-white bg-gray-400 border-0 focus:outline-none hover:bg-gray-500 rounded">PDFレポート出力</button></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>

                        </div>
                      </section>
                      
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
