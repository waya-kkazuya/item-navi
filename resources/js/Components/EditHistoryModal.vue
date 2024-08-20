<script setup>
import axios from 'axios';
import { ref, reactive, onMounted, defineProps } from 'vue';

// 親コンポーネントから受け取る
const props = defineProps({
  item: Object,
  isTableView: Boolean
})


onMounted(() => {
  console.log(props.isTableView)
})

// itemだけでなくオブジェクトごと取ってきた方がいい

// const itemId = 1
const editHistoriesData = ref([]);


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}
const editHistories = async item => {
  try {
    await axios.get(`api/edithistory/?item_id=${item.id}`)
    .then( res => {
      console.log(res.data)
      editHistoriesData.value = res.data
    })
    toggleStatus()
  } catch(e) {
      console.log(e.message)
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
}
</script>
<template>
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container w-2/3" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            【管理ID{{ props.item.management_id }}】{{ props.item.name }}の編集履歴
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="min-w-full overflow-auto">
            <table class="table-fixed min-w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新日時</th>
                  <th class="min-w-24 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">編集者</th>
                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新フィールド</th>
                  <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新前</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-sm bg-sky-700">更新後</th>
                </tr>
              </thead>
              <tbody>
                <tr  v-for="history in editHistoriesData" :key="history.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ formatDate(history.created_at) }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ history.edit_user }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ history.edited_field }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ history.old_value }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ history.new_value }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
        <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">閉じる</button>
        </footer>
      </div>
    </div>
  </div>
  <!-- item.idを親から子へ渡す、async await axiosの変数として渡される -->
   <!-- 行表示かタイル表示かでボタンの表示を切り替え -->
  <button v-if="props.isTableView" @click="editHistories" type="button" data-micromodal-trigger="modal-1">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
  </button>
  <button v-else @click="editHistories(item)" type="button" data-micromodal-trigger="modal-1"
  class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded">
    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
    履歴
    </button>
</template>