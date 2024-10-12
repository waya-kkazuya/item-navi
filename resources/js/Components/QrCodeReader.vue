<script setup>
import { QrcodeStream } from 'vue-qrcode-reader'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'


const emits = defineEmits(['qrDetected'])

const scannerActive = ref(false)

const startScan = () => {
  if (confirm('QRコードのスキャンを開始しますか？')) {
    scannerActive.value = true
  }
}

const onDetect = content => {
  // QRコードの内容を取得し、特定のURLに遷移
  // alert(`QRコードの内容stringify: ${JSON.stringify(content)}`)

  // vue-qrcode-readerは複数のバーコードを同時に読み込めるので配列形式
  if (Array.isArray(content) && content.length > 0) {
    const firstItem = content[0];
    const itemId = firstItem.rawValue;
    // alert(`QRコードのidデータ: ${itemId}`);
    try {
      router.visit(route('consumable_items', { item_id: itemId }), {
        onSuccess: (page) => {
          // alert('成功しました！')
          // console.log('成功時のページデータ:', page);
        },
        onError: (errors) => {
          // alert('エラーが発生しました。')
          console.log('エラー内容:', errors);
        }
      })
    } catch (e) {
      // alert(`画面の遷移時にエラーが発生しました: ${e.message}`)
    }

  } else {
    alert('QRコードの内容を取得できませんでした')
  }
  scannerActive.value = false // スキャンが完了したらカメラを停止
}

const stopScan = () => {
  scannerActive.value = false;
}
</script>

<template>
  <div>
    <button @click="startScan" class="flex items-center">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
      </svg>
    </button>

    <!-- <qrcode-stream v-if="scannerActive" @detect="onDetect" class="scanner"></qrcode-stream>
    <button v-if="scannerActive" @click="stopScan" class="stop-scan-button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
      </svg>
    </button> -->

    <div v-show="scannerActive" class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" id="modal-1" aria-hidden="true">
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container bg-white w-full md:w-11/12 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0  transition-transform duration-500 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
          <header class="modal__header">
            <button class="modal__close" aria-label="Close modal" @click="stopScan"></button>
          </header>
          <main class="modal__content" id="modal-1-content">
            <qrcode-stream @detect="onDetect" class="scanner"></qrcode-stream>
            <!-- カメラ映像を止める「×」ボタン -->
            <button v-if="scannerActive" @click="stopScan" class="stop-scan-button">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </main>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.scanner {
  position: fixed; /* 固定位置にする */
  top: 0;
  left: 0;
  width: 100vw; /* ビューポートの幅いっぱいに */
  height: 100vh; /* ビューポートの高さいっぱいに */
  object-fit: cover; /* カメラ映像を全体に表示 */
  z-index: 1000; /* 他の要素の上に表示 */
  background-color: black; /* 背景を黒にしてカメラ以外の部分を隠す */
}
.stop-scan-button {
  position: fixed;
  top: 10px;
  right: 10px;
  z-index: 1001; /* カメラ映像の上に表示 */
}
</style>