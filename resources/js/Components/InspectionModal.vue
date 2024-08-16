<script setup>
import axios from 'axios';
import { ref, reactive, onMounted, defineProps } from 'vue';

// 親コンポーネントから受け取る
const props = defineProps({
  item: Object
})

// itemだけでなくオブジェクトごと取ってきた方がいい

// const itemId = 1
// const editHistoriesData = ref([]);


const isShow = ref(false)
const toggleStatus = () => { isShow.value = !isShow.value}

// const editHistories = async () => {
//   try {
//     await axios.get(`api/edithistory/?item_id=${props.item.id}`)
//     .then( res => {
//       console.log(res.data)
//       editHistoriesData.value = res.data;
//     })
//     toggleStatus()
//   } catch(e) {
//       console.log(e.message)
//   }
// }

// 日付フォーマット関数
const formatDate = (timestamp) => {
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
  <div v-show="isShow" class="modal" id="modal-1" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            DisposalMicromodal
          </h2>
          <button @click="toggleStatus" type="button" class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <p>
            Try hitting the <code>tab</code> key and notice how the focus stays within the modal itself. Also, <code>esc</code> to close modal.
          </p>
        </main>
        <footer class="modal__footer">
          <button @click="toggleStatus" type="button" class="modal__btn modal__btn-primary">Continue</button>
          <button @click="toggleStatus" type="button" class="modal__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
        </footer>
      </div>
    </div>
  </div>
  <button @click="toggleStatus" type="button" data-micromodal-trigger="modal-1" href='javascript:;'>点検する</button>
</template>