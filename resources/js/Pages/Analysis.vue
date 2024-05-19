<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, onMounted, ref } from 'vue';
import { getToday } from '@/common';
import axios from 'axios';
import Chart from '@/Components/Chart.vue'

onMounted(() => {
    form.startDate = '2022-05-10'
    form.endDate = getToday()
})

const form = reactive({
    startDate: null,
    endDate: null,
    type: 'perday'
})

// ハーフモーダル用
const isModalOpen = ref(false);
const quantity = ref(0);


const data = reactive({})

const getData = async () => {
try{
    await axios.get('/api/analysis/', {
        params: {
            startDate: form.startDate,
            endDate: form.endDate,
            type: form.type
        }
    })
    .then( res => {
        data.data = res.data.data
        data.labels = res.data.labels
        data.stocks = res.data.stocks
        console.log(res.data)
    })
} catch (e){
    console.log(e.message)
}
}

</script>

<template>
    <Head title="データ分析" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                データ分析
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="getData">
                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button class="mt-4 flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">分析する</button>
                        </form>

                        <div v-show="data.data">
                            <Chart :data="data" />
                        </div>

                        <div v-show="data.data" class="lg:w-2/3 w-full mx-auto overflow-auto">
                            <table class="table-auto w-full text-left whitespace-no-wrap">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">更新日時</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">更新区分</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">入庫数</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">出庫数</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">操作元</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="edithistory in data.data" :key="edithistory.edited_at">
                                        <td class="px-4 py-3">{{ edithistory.edited_at }}</td>
                                        <td class="px-4 py-3">{{ edithistory.action_type }}</td>
                                        <td class="px-4 py-3">{{ edithistory.input }}</td>
                                        <td class="px-4 py-3">{{ edithistory.output }}</td>
                                        <td class="px-4 py-3">PC</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- ハーフモーダル -->
            <div class="h-screen flex items-center justify-center">
                <button @click="isModalOpen = true" class="px-4 py-2 bg-blue-500 text-white rounded">入出庫</button>
                <transition name="modal">
                    <div v-show="isModalOpen" class="fixed bottom-0 w-full bg-white p-4 shadow-lg modal-content">
                        <h2 class="text-xl mb-2">入出庫</h2>
                        <p class="mb-4">いくつ入庫するか出庫するか入力してください。</p>
                        <input type="number" v-model="quantity" class="border p-2 mb-4 w-full">
                        <button @click="isModalOpen = false" class="px-4 py-2 bg-red-500 text-white rounded">閉じる</button>
                    </div>
                </transition>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.modal-content {
  animation: slide-up 0.3s ease-out;
}
.modal-content-leave-active {
  animation: slide-down 0.3s ease-in;
}
@keyframes slide-up {
  0% {
    transform: translateY(100%);
  }
  100% {
    transform: translateY(0);
  }
}
@keyframes slide-down {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(100%);
  }
}
</style>