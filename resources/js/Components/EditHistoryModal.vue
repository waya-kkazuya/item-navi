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
  // MicroModal.init({
  //   disableScroll: true,
  //   closeTrigger: 'data-micromodal-close',
  //   closeOnOutsideClick: true,
  // });
})

// itemだけでなくオブジェクトごと取ってきた方がいい

// const itemId = 1
const editHistoriesData = ref([]);


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}
const editHistories = async item => {
  try {
    await axios.get(`api/edithistory?item_id=${item.id}`)
    .then( res => {
      console.log(res.data)
      editHistoriesData.value = res.data.edithistories
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
  <div v-show="isShow" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-end md:items-center md:justify-center z-50" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container bg-white w-full md:w-11/12 lg:w-2/3 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0  transition-transform duration-500 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            <span class="text-sm lg:text-lg">【管理ID{{ props.item.management_id }}】{{ props.item.name }}の編集履歴</span>
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="min-w-full overflow-auto">
            <table v-if="editHistoriesData.length > 0" class="table-fixed min-w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="min-w-16 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新日時</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新種類</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">編集者</th>
                  <th class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新フィールド</th>
                  <th class="min-w-40 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新前</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">更新後</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">編集理由</th>
                  <th class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700">理由詳細</th>
                </tr>
              </thead>
              <tbody>
                <tr  v-for="history in editHistoriesData" :key="history.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ formatDate(history.created_at) }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.operation_type_for_display }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.edit_user }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.edited_field_for_display }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.old_value }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.new_value }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.edit_reason ? history.edit_reason.reason : null }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">{{ history.edit_reason_text ?? 'なし' }}</td>
                </tr>
              </tbody>
            </table>
            <div v-else>
              <div class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div class="ml-2 text-center py-4 text-sm lg:text-base">編集履歴がありません</div>
              </div>
            </div>
          </div>
        </main>
        <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">
          <span class="text-xs md:text-sm">閉じる</span>
          </button>
        </footer>
      </div>
    </div>
  </div>
  <!-- item.idを親から子へ渡す、async await axiosの変数として渡される -->
   <!-- 行表示かタイル表示かでボタンの表示を切り替え -->
  <button v-if="props.isTableView" @click="editHistories(item)" type="button" data-micromodal-trigger="modal-1" class="h-4">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
  </button>
  <button v-else @click="editHistories(item)" type="button" data-micromodal-trigger="modal-1" class="flex items-center text-white text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded">
    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
    履歴
  </button>
</template>