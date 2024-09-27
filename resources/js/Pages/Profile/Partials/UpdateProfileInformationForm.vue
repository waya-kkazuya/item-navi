<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    profile_image_path: String
    // errors: Object
})

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    profile_image_file: null,
    _method: 'PATCH'
});

const save = () => {
    form.processing = true; // 送信開始時にtrueに設定
    form.post('/profile', {
        onFinish: () => form.processing = false, // 送信完了時にfalseに戻す
        onError: (errors) => {
            console.log(errors); // エラーの内容をコンソールに表示
        }
    });
};

onMounted(() => {
    console.log(props.profile_image_path)
})


const profileImagePreview = ref(props.profile_image_path);

watch(() => props.profile_image_path, (newPath) => {
    profileImagePreview.value = newPath;
});

const handleFileUpload = (event) => {
  form.profile_image_file = event.target.files[0];
  profileImagePreview.value = URL.createObjectURL(form.profile_image_file);
};

</script>

<template>
    <section>
        <FlashMessage />
        <header>
            <h2 class="text-lg font-medium text-gray-900">プロフィール編集</h2>

            <!-- <p class="mt-1 text-sm text-gray-600">
                名前とプロフィール画像を編集できます
            </p> -->
        </header>
        <form @submit.prevent="save" enctype="multipart/form-data" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="名前" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            
            <div>
                <!-- <label for="profile_image_file">プロフィール画像</label> -->
                <InputLabel for="name" value="プロフィール画像" />
                <div class="flex justify-center items-center relative">
                    <input type="file" id="profile_image_file" @change="handleFileUpload" class="hidden">
                    <label for="profile_image_file" class="cursor-pointer">
                        <img v-if="profileImagePreview" :src="profileImagePreview" alt="プロフィール画像" class="w-48 h-48 rounded-full object-cover border border-black">
                        <div v-else class="flex items-center justify-center text-4xl text-gray-300 w-48 h-48 rounded-full object-cover border border-gray-300">+</div>
                        <div v-if="profileImagePreview" class="absolute inset-0 flex justify-center items-center text-4xl text-white">+</div>
                    </label>
                    <InputError class="mt-2" :message="form.errors.profile_image_file" />
                    <!-- <div v-if="errors.profile_image_file" class="font-medium text-red-600">{{ errors.profile_image_file }}</div> -->
                </div>        
            </div>



            <!-- <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div> -->

            <!-- <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div> -->

            <div class="flex items-center gap-4">
                <!-- <PrimaryButton :disabled="form.processing">保存する</PrimaryButton> -->
                <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    保存する
                </button>

                <!-- <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">保存しました</p>
                </Transition> -->
            </div>
        </form>
    </section>
</template>

<!-- <style scoped>
.profile-image {
  width: 200px;
  height: 200px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid #000;
}
</style> -->