<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, onMounted, ref } from 'vue';
import { getToday } from '@/common';
import axios from 'axios';
import Chart from '@/Components/Chart.vue'

const props = defineProps({
    data: Object,
    labels: Array,
    stocks: Array,
    item: Object
})

const data = reactive({})

const form = reactive({
    type: 'week'
})

onMounted(() => {
    data.data = props.data
    console.log(data.data)
    data.labels = props.labels
    data.stocks = props.stocks
})


const setType = (type) => {
    form.type = type;
    getData();
}


// タブ切り替え時にtypeがweekかmonthで非同期でデータを取得
const getData = async () => {
    try{
        await axios.get('/api/history/', {
            params: {
                id: props.item.id,
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
    <Head title="在庫数の遷移" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              在庫数の遷移
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex">
                            <div>{{ item.id }} </div>  
                            <div>{{ item.name }}</div>
                            <div class="w-40">
                                <img :src="item.image_path1" alt="" class="h-full w-full">
                            </div>
                        </div>
                        <div class="mb-8">注意点：消耗品が備品として追加される前までは遡れない</div>

                        <div class="flex mb-4">
                            <button @click="setType('week')"
                            :class="form.type === 'week' ? 'bg-blue-500 text-white' : 'text-blue-500'" class="px-4 py-2 font-semibold border border-blue-500 rounded-l hover:bg-blue-500 hover:text-white">
                                1週間
                            </button>
                            <button @click="setType('month')" 
                            :class="form.type  === 'month' ? 'bg-blue-500 text-white' : 'text-blue-500'" class="px-4 py-2 font-semibold border border-blue-500 rounded-r hover:bg-blue-500 hover:text-white">
                                1ヵ月
                            </button>
                        </div>

                        <div v-show="data.data" class="w-50">
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
        </div>
    </AuthenticatedLayout>
</template>