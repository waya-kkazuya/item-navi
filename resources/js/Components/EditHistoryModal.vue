<script setup lang="ts">
import { ClockIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import apiClient from '@/apiClient';
import axios from 'axios';
import { ref } from 'vue';

import type { Ref } from 'vue';
import type { EditHistoryType, ItemType } from '@/@types/model';

type Props = {
  item: ItemType;
  isTableView: boolean;
};

const props = defineProps<Props>();

const editHistoriesData: Ref<EditHistoryType[]> = ref([]);

const isShow: Ref<boolean> = ref(false);
const toggleStatus = (): void => {
  isShow.value = !isShow.value;
};

const editHistories = async (item: ItemType): Promise<void> => {
  try {
    await apiClient.get(`api/edithistory?item_id=${item.id}`).then((res) => {
      editHistoriesData.value = res.data.edithistories;
    });
    toggleStatus();
  } catch (error: any) {
    console.error('EditHistoryModal.vue editHistories method error:', error.message);
  }
};

// 日付フォーマット関数
const formatDate = (timestamp: string) => {
  const date = new Date(timestamp);
  const year = date.getFullYear();
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const day = ('0' + date.getDate()).slice(-2);
  const hours = ('0' + date.getHours()).slice(-2);
  const minutes = ('0' + date.getMinutes()).slice(-2);
  return `${year}/${month}/${day} ${hours}:${minutes}`;
};
</script>

<template>
  <div
    v-show="isShow"
    class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-end md:items-center md:justify-center z-50"
    id="modal-1"
    aria-hidden="true"
  >
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div
        class="modal__container bg-white w-full md:w-11/12 lg:w-2/3 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0 transition-transform duration-500 ease-in-out"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-1-title"
      >
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            <span class="text-sm lg:text-lg"
              >【ID{{ props.item.management_id }}】{{ props.item.name }}の編集履歴</span
            >
          </h2>
          <button
            @click="toggleStatus"
            type="button"
            class="modal__close"
            aria-label="Close modal"
            data-micromodal-close
          ></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="min-w-full overflow-auto">
            <table
              v-if="editHistoriesData.length > 0"
              class="table-fixed min-w-full text-left whitespace-no-wrap"
            >
              <thead>
                <tr>
                  <th
                    class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    更新日時
                  </th>
                  <th
                    class="min-w-28 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    更新種類
                  </th>
                  <th
                    class="min-w-32 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    編集者
                  </th>
                  <th
                    class="min-w-36 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    更新フィールド
                  </th>
                  <th
                    class="min-w-48 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    更新前
                  </th>
                  <th
                    class="min-w-48 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    更新後
                  </th>
                  <th
                    class="min-w-56 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    編集理由
                  </th>
                  <th
                    class="min-w-56 px-4 py-3 title-font tracking-wider font-medium text-white text-xs md:text-sm text-center bg-sky-700"
                  >
                    理由詳細
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="history in editHistoriesData" :key="history.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ formatDate(history.created_at) }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.operation_type_for_display }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.edit_user }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.edited_field_for_display }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.old_value }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.new_value }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.edit_reason ? history.edit_reason.reason : null }}
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3 text-xs md:text-sm text-center">
                    {{ history.edit_reason_text ?? 'なし' }}
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-else>
              <div class="flex items-center justify-center">
                <InformationCircleIcon class="w-6 h-6 text-black" />
                <div class="ml-2 text-center py-4 text-xs md:text-sm">編集履歴がありません</div>
              </div>
            </div>
          </div>
        </main>
        <footer class="modal__footer">
          <button
            @click="toggleStatus"
            type="button"
            class="modal__btn"
            data-micromodal-close
            aria-label="Close this dialog window"
          >
            <span class="text-xs md:text-sm">閉じる</span>
          </button>
        </footer>
      </div>
    </div>
  </div>
  <!-- 行表示かタイル表示かでボタンの表示を切り替え -->
  <div>
    <button
      v-if="props.isTableView"
      @click="editHistories(item)"
      type="button"
      data-micromodal-trigger="modal-1"
      class="h-4"
    >
      <ClockIcon class="size-5" />
    </button>
    <button
      v-else
      @click="editHistories(item)"
      type="button"
      data-micromodal-trigger="modal-1"
      class="flex items-center text-white text-xs md:text-sm bg-gray-500 border-0 py-2 px-4 mx-auto focus:outline-none hover:bg-gray-600 rounded"
    >
      <ClockIcon class="w-4 h-4 mr-1 transform -translate-y-px" />
      履歴
    </button>
  </div>
</template>
