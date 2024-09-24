<script setup>
import { QrcodeStream } from 'vue-qrcode-reader'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'


const emits = defineEmits(['qrDetected'])

const scannerActive = ref(false)

const startScan = () => {
  scannerActive.value = true
}

const onDetect = (content) => {
  // QRコードの内容を取得し、特定のURLに遷移
  // emits('qrDetected', content)
  // モーダルを開くにはitemモデルが必要なので、一旦コントローラー側を経由する
  // item_idのみあればfind($id)で取って来れる
  console.log(content)
  router.visit(`consumable_items/${content}`)
  // router.push(content)
  scannerActive.value = false // スキャンが完了したらカメラを停止
}

</script>

<template>
  <div>
    <button @click="startScan">QRコードをスキャン</button>
    <qrcode-stream v-if="scannerActive" @detect="onDetect"></qrcode-stream>
  </div>
</template>