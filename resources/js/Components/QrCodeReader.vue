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
    <qrcode-stream v-if="scannerActive" @detect="onDetect" class="scanner"></qrcode-stream>
    <button v-if="scannerActive" @click="stopScan" class="stop-scan-button">×</button>
    <!-- <button @click="transitionTest">機能テスト用</button> -->
  </div>
</template>

<style scoped>
.scanner {
  width: 100vw; /* ビューポートの幅いっぱいに */
  height: 100vh; /* ビューポートの高さいっぱいに */
  object-fit: cover; /* カメラ映像を全体に表示 */
}
</style>