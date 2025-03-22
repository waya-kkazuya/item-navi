<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
  laravelVersion: {
    type: String,
    required: true,
  },
  phpVersion: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <Head title="Welcome" />

  <div class="min-h-screen bg-white">
    <nav class="bg-blue-900 text-white fixed top-0 left-0 w-full z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
              <ApplicationLogo class="block h-20 w-40 fill-current text-gray-800"/>
          </div>
        </div>
      </div>
    </nav>

    <section class="bg-cover bg-center pt-20 pb-5" style="background-image: url('/images/office_layer.png'); background-color: #eef2ff; background-size: cover; background-position: left;">
      <div class="container mx-auto px-20 py-8 grid grid-cols-1 md:grid-cols-2">
        <div class="flex flex-col justify-center order-2 md:order-1">
          <h1 class="pt-4 md:pt-0 font-noto-sans-jp text-2xl font-bold leading-normal tracking-widest text-center">
            <P class="font-normal">
              利用者も参加できる備品管理システム<br>
              事業所の生産性と満足度を上げよう<br>
            </P>
          </h1>
          <div v-if="canLogin" class="mt-4 flex justify-center">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="font-semibold text-white text-base bg-green-500 hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm px-4 py-2 rounded"
            >
              ダッシュボードに移動
            </Link>
            <template v-else>
              <Link
                :href="route('login')"
                class="font-semibold text-white text-base bg-green-500 hover:bg-gray-100 focus:outline focus:outline-2 focus:rounded-sm px-4 py-2 rounded"
              >
                ログインする
              </Link>
            </template>
          </div>
        </div>
        <div class="order-1 md:order-2">
          <img src="/images/hero_image.png" alt="システム紹介画像" class="w-full h-auto"/>
        </div>
      </div>
    </section>

    <section class="text-gray-600 body-font bg-white max-w-7xl mx-auto">
      <div class="container px-5 py-20 mx-auto">
        <div class="text-center mb-20">
          <h1 class="sm:text-2xl text-2xl font-medium title-font text-gray-900 mb-4">
            主な機能一覧
          </h1>
          <div class="flex mt-6 justify-center">
            <div class="w-16 h-1 rounded-full bg-indigo-500 inline-flex"></div>
          </div>
        </div>
        <div class="flex flex-wrap sm:-m-4 -mx-4 -mb-10 -mt-4 md:space-y-0 space-y-6">
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
            <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
              <img src="/images/item.png" alt="備品管理の画像" class="w-10 h-auto"/>
            </div>
            <div class="flex-grow">
              <h2 class="text-gray-900 text-lg title-font font-medium mb-3">備品管理</h2>
              <p class="leading-relaxed text-base">
                カテゴリ別に備品を登録することが出来ます。詳細な項目を設定可能です。<br>
                登録した備品は備品一覧で確認することが出来ます。
              </p>
            </div>
          </div>
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
            <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
              <img src="/images/graph.png" alt="消耗品管理の画像" class="w-10 h-auto"/>
            </div>
            <div class="flex-grow">
                <h2 class="text-gray-900 text-lg title-font font-medium mb-3">消耗品在庫管理</h2>
                <p class="leading-relaxed text-base">
                  消耗品の入出庫を行うことができます。<br>
                  また入出庫の履歴や在庫数の遷移をグラフで確認することが可能です。
                </p>
            </div>
          </div>
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
              <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
                <img src="/images/checklist.png" alt="備品の点検・廃棄の画像" class="w-10 h-auto"/>
              </div>
              <div class="flex-grow">
                  <h2 class="text-gray-900 text-lg title-font font-medium mb-3">備品の点検と廃棄</h2>
                  <p class="leading-relaxed text-base">
                    備品の点検と廃棄を行うことが出来ます。<br>
                    点検と廃棄の予定や履歴もも確認可能で便利です。
                  </p>
              </div>
          </div>
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
              <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
                <img src="/images/light_bulb.png" alt="備品の点検・廃棄の画像" class="w-10 h-auto"/>
              </div>
              <div class="flex-grow">
                <h2 class="text-gray-900 text-lg title-font font-medium mb-3">備品のリクエスト</h2>
                <p class="leading-relaxed text-base">
                  利用者が必要な備品をリクエストすることが出来ます。<br>
                  管理者はそのリクエストに対する反応を簡単に返すことが可能です。    
                </p>
              </div>
          </div>
        </div>

        <div class="mt-8"></div>

        <div class="flex flex-wrap sm:-m-4 -mx-4 -mb-10 -mt-4 md:space-y-0 space-y-6">
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
            <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
              <img src="/images/bell.png" alt="備品管理の画像" class="w-10 h-auto"/>
            </div>
            <div class="flex-grow">
              <h2 class="text-gray-900 text-lg title-font font-medium mb-3">通知機能</h2>
              <p class="leading-relaxed text-base">
                以下の時に通知を受け取れます。<br>
                ①消耗品在庫数が少なくなった時<br>
                ②点検・廃棄の予定日が近づいたとき<br>
                ③備品のリクエストが作成されたとき
              </p>
            </div>
          </div>
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
              <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
                <img src="/images/users.png" alt="消耗品管理の画像" class="w-10 h-auto"/>
              </div>
              <div class="flex-grow">
                <h2 class="text-gray-900 text-lg title-font font-medium mb-3">ユーザー権限機能</h2>
                <p class="leading-relaxed text-base">
                  管理者権限と利用者権限を用意しています。<br>
                  利用者権限では消耗品の入出庫や備品のリクエストのみを行うことが出来ます。
                </p>
              </div>
          </div>
          <div class="p-4 w-full md:w-1/4 flex flex-col text-center items-center mx-auto">
              <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
                <img src="/images/qrcode.png" alt="備品の点検・廃棄の画像" class="w-10 h-auto"/>
              </div>
              <div class="flex-grow">
                <h2 class="text-gray-900 text-lg title-font font-medium mb-3">QRコードで入出庫</h2>
                <p class="leading-relaxed text-base">
                  PDFで生成されるQRコードをカメラで読み込むことで、消耗品を検索することなく入出庫画面を表示することが出来便利です。
                </p>
              </div>
          </div>
          <div class="w-full p-4 md:w-1/4 flex flex-col text-center items-center mx-auto">
            <div class="w-20 h-20 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mb-5 flex-shrink-0">
              <img src="/images/PDF.png" alt="備品の点検・廃棄の画像" class="w-10 h-auto"/>
            </div>
            <div class="flex-grow">
              <h2 class="text-gray-900 text-lg title-font font-medium mb-3">QRコードのPDFダウンロード</h2>
              <p class="leading-relaxed text-base">
                登録してある消耗品のQRコードをPDFで自動生成します。<br>
                PDFで生成されるQRコードを印刷し、消耗品の配置場所に貼ることで入出庫を効率か出来ます。
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="bg-gray-100 text-gray-400 text-center text-sm py-4">
      <p>&copy; 2024 waya. All rights reserved.</p>
      <p>
        Icons provided by
        <a
          href="https://heroicons.com/"
          target="_blank"
          class="text-blue-500"
          >Heroicons</a>
        . Licensed under the MIT License.
      </p>
      <p>
        Fonts provided by
        <a
          href="https://fonts.google.com/"
          target="_blank"
          class="text-blue-500"
        >Google Fonts</a>
        . Licensed under the 
        <a
          href="https://scripts.sil.org/cms/scripts/page.php?item_id=OFL_web"
          target="_blank"
          class="text-blue-500"
        >Open Font License</a>
        .
      </p>
    </footer>
  </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
