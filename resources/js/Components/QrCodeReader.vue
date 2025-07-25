<script setup lang="ts">
import { QrCodeIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { QrcodeStream } from 'vue-qrcode-reader';
import { ref } from 'vue';

import type { Ref } from 'vue';

type QrCodeContent = {
  rawValue: string;
};

const emits = defineEmits<{ (e: 'qrDetected'): void }>();

const scannerActive: Ref<boolean> = ref(false);

const startScan = (): void => {
  if (confirm('QRコードのスキャンを開始しますか？')) {
    scannerActive.value = true;
  }
};

// QRコードに記録された備品のitemIdを取得し、消耗品管理画面の該当の備品のモーダルを開く
const onDetect = (content: QrCodeContent[]) => {
  // vue-qrcode-readerは複数のバーコードを同時に読み込めるので配列形式
  try {
    if (Array.isArray(content) && content.length > 0) {
      const firstItem = content[0];
      const itemId = firstItem.rawValue;
      router.visit(route('consumable_items', { item_id: itemId }));
    } else {
      alert('QRコードの内容を取得できませんでした');
    }
  } catch (e: any) {
    axios.post('/api/log-error', {
      error: e.toString(),
      component: 'QrCodeReader.vue onDetect method',
    });
    alert('エラーが発生しました、もう一度お試しください');
  }
  scannerActive.value = false; // スキャンが完了したらカメラを停止
};

const stopScan = (): void => {
  scannerActive.value = false;
};
</script>

<template>
  <div>
    <button @click="startScan" class="flex items-center">
      <QrCodeIcon class="size-7" />
    </button>

    <div
      v-show="scannerActive"
      class="modal fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
      id="modal-1"
      aria-hidden="true"
    >
      <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div
          class="modal__container bg-white w-full md:w-11/12 md:h-auto md:rounded-lg p-4 md:p-8 md:shadow-lg md:transform-none transform md:translate-y-0 transition-transform duration-500 ease-in-out"
          role="dialog"
          aria-modal="true"
          aria-labelledby="modal-1-title"
        >
          <header class="modal__header">
            <button class="modal__close" aria-label="Close modal" @click="stopScan"></button>
          </header>
          <main class="modal__content" id="modal-1-content">
            <qrcode-stream @detect="onDetect" class="scanner"></qrcode-stream>
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
