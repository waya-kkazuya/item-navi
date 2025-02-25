<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Head, Link } from "@inertiajs/vue3";
import { onMounted } from "vue";

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
                        <ApplicationLogo
                            class="block h-20 w-40 fill-current text-gray-800"
                        />
                    </div>
                </div>
                <div
                    v-if="canLogin"
                    class="sm:fixed sm:top-0 sm:right-0 p-6 text-end"
                >
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="font-semibold text-white hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                    >
                        ダッシュボード
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="font-semibold text-blue-900 bg-white hover:bg-gray-100 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 px-4 py-2 rounded"
                        >
                            ログイン
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <section class="bg-white pt-20 pb-5">
            <div class="container mx-auto px-12 py-12 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center">
                    <h1 class="text-2xl font-bold leading-normal tracking-widest">
                        備品の利用者も参加できるシステムで<br/>
                        事業所の生産性と満足度を上げよう<br/>
                    </h1>
                    <div v-if="canLogin" class="mt-4 flex justify-start">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="font-semibold text-white text-xl hover:text-gray-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        >
                            ダッシュボードに移動
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="font-semibold text-blue-900 text-xl bg-white hover:bg-gray-100 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500 px-4 py-2 rounded border border-blue-900"
                            >
                                ログインする
                            </Link>
                        </template>
                    </div>
                </div>
                <div>
                    <img
                        src="/images/hero.png"
                        alt="システム紹介画像"
                        class="w-full h-auto"
                    />
                </div>
            </div>
        </section>

        <footer class="text-gray-400 text-center text-sm py-4">
            <p>&copy; 2024 waya. All rights reserved.</p>
            <p>
                Icons provided by
                <a
                    href="https://heroicons.com/"
                    target="_blank"
                    class="text-blue-500"
                    >Heroicons</a
                >. Licensed under the MIT License.
            </p>
            <p>
            Fonts provided by
            <a
                href="https://fonts.google.com/"
                target="_blank"
                class="text-blue-500"
            >Google Fonts</a>. Licensed under the 
            <a
                href="https://scripts.sil.org/cms/scripts/page.php?item_id=OFL_web"
                target="_blank"
                class="text-blue-500"
            >Open Font License</a>.
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
