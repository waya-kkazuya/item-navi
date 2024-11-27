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

onMounted(() => {
  console.log(Ziggy.namedRoutes);
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gray-100">
        <nav class="bg-blue-900 text-white fixed top-0 left-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <ApplicationLogo
                            class="block h-20 w-40 fill-current text-gray-800"
                        />
                    </div>
                </div>
                <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-end">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-white hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                        ダッシュボード
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="font-semibold text-blue-900 bg-white hover:bg-gray-100 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 px-4 py-2 rounded">
                            ログイン
                        </Link>
                    </template>
                </div>
            </div>
        </nav>


        <section class="bg-[#366097] text-white pt-20 pb-5">
            <div class="container mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col justify-center">
                    <h1 class="text-4xl font-bold leading-tight tracking-widest">
                        管理者の負担を減らし<br>
                        備品の利用者の満足度を上げる<br>
                        備品管理システム
                    </h1>
                    <div class="flex items-center">
                        <img src="/images/hero-logo.png" class="block w-88 fill-current text-gray-800">
                    </div>
                    <div v-if="canLogin" class="mt-4 flex justify-start">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-white text-xl hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                            システムを見る
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="font-semibold text-blue-900 text-xl bg-white hover:bg-gray-100 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 px-6 py-4 rounded">
                                ログインする
                            </Link>
                        </template>
                    </div>
                </div>
                <div>
                    <img src="/images/ヒーローセクション写真テスト.png" alt="システム紹介画像" class="w-full h-auto">
                </div>
            </div>
        </section>

        <section class="bg-white w-full py-8">
            <div class="container mx-auto px-4">
                <h2 class="text-center text-2xl font-bold text-gray-800">システムの特徴</h2>
            </div>
        </section>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-center justify-center">
                        <div class="md:w-1/2">
                            <p class="text-lg">
                                ItemNaviは、管理者だけでなく備品の利用者も参加することで、企業全体の満足度を上げることを目指しています。
                            </p>
                            <p class="text-lg">
                                このシステムで、管理者と備品の利用者のコミュニケーション不足を補います。
                                <!-- 管理者が気付きづらいところ、備品の利用者の伝えづらいところ、このシステムが仲介して、コミュニケーションを行うことで解消していきます。 -->
                            </p>
                        </div>
                        <div class="md:w-1/2 flex justify-center">
                            <img src="/images/people.png" alt="人々の画像" class="w-1/2 h-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="bg-white w-full py-8">
            <div class="container mx-auto px-4">
                <h2 class="text-center text-2xl font-bold text-gray-800">機能一覧</h2>
            </div>
        </section>

        <!-- <section class="bg-white w-full py-8">
            <div class="container mx-auto px-4">
                <h2 class="text-center text-2xl font-bold text-gray-800">機能一覧</h2>
                <ul class="mt-4 text-center">
                <li class="mb-2"><a href="#feature1" class="text-blue-500 hover:underline">備品登録管理</a></li>
                <li class="mb-2"><a href="#feature2" class="text-blue-500 hover:underline">消耗品管理</a></li>
                <li class="mb-2"><a href="#feature3" class="text-blue-500 hover:underline">備品の点検と廃棄</a></li>
                <li class="mb-2"><a href="#feature4" class="text-blue-500 hover:underline">備品のリクエスト機能</a></li>
                <li class="mb-2"><a href="#feature5" class="text-blue-500 hover:underline">通知機能</a></li>
                <li class="mb-2"><a href="#feature6" class="text-blue-500 hover:underline">権限機能</a></li>
                </ul>
            </div>
        </section> -->


        <div class="py-8" id="feature1">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">備品登録管理</h3>
                            <span class="ml-4 text-sm text-gray-400">権限　管理者</span>
                            <p class="mb-4 text-lg">
                                備品の登録や編集をすることが出来ます。<br>
                                備品の一覧を閲覧できます。<br>
                                備品の編集履歴を閲覧できます。<br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-8" id="feature2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">消耗品管理</h3>
                            <span class="ml-4 text-sm">権限　管理者、利用者</span>
                            <p class="mb-4 text-lg">
                                消耗品として登録した備品の在庫を管理できます。<br>
                                簡単に在庫の入出庫処理ができます。<br>
                                入出庫の履歴はグラフと表で確認できます。<br>
                            </p>
                            <p>
                                消耗品に対応したQRコードを読み取ることで、スムーズに入出庫が可能です。
                                ※QRコードはPDFでダウンロードできます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8" id="feature3">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">備品の点検と廃棄</h3>
                            <span class="ml-4 text-sm">権限　管理者</span>
                            <p class="mb-4 text-lg">
                                備品の点検と廃棄を記録することが出来ます。<br>
                                点検・廃棄の実施予定と実施履歴を確認することが出来ます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8" id="feature4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">備品のリクエスト機能</h3>
                            <span class="ml-4 text-sm">権限　管理者</span>
                            <p class="mb-4 text-lg">
                                備品の利用者が必要な備品をリクエスト出来ます。<br>
                                管理者はリクエストに対する対応をプルダウンで簡単に示すことが出来ます。<br>

                                また、備品の内容を採用する場合は、その内容を備品の新規登録画面に自動で入力します。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8" id="feature5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">通知機能</h3>
                            <span class="ml-4 text-sm">権限　管理者</span>
                            <p class="mb-4 text-lg">
                                以下のタイミングで通知を受け取ります。<br>
                                ●消耗品の在庫数が通知在庫数を下回ったとき<br>
                                ●点検予定日・廃棄予定日が近づいたとき<br>
                                ●備品のリクエストが登録されたとき
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-8" id="feature6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start justify-center">
                        <div class="md:w-3/5 flex justify-center">
                            <img src="/images/備品登録管理.png" alt="備品登録管理の画像" class="w-full h-auto">
                        </div>
                        <div class="md:w-2/5 p-6">
                            <h3 class="text-xl font-bold">権限機能</h3>
                            <span class="ml-4 text-sm">権限　管理者</span>
                            <p class="mb-4 text-lg">
                                管理者と備品の利用者の権限を分けることで、<br>
                                利用者は消耗品管理とリクエストのみ行うことが出来ます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-gray-400 text-center text-sm py-4">
                <p>&copy; 2024 waya. All rights reserved.</p>
                <p>Icons provided by <a href="https://heroicons.com/" target="_blank" class="text-blue-500">Heroicons</a>. Licensed under the MIT License.</p>
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
