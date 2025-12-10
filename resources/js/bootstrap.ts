/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import type { AxiosError, InternalAxiosRequestConfig } from 'axios';

import axios from 'axios';

window.axios = axios;

// X-Requested-Withヘッダーを追加
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Cookieを自動的に送信（Sanctum認証に必須）
window.axios.defaults.withCredentials = true;

// CSRF Cookie初期化フラグ
let csrfInitialized = false;

// リクエストインターセプター（初回APIリクエスト前にCSRF Cookie取得）
window.axios.interceptors.request.use(
    async (config: InternalAxiosRequestConfig) => {
        if (!csrfInitialized && config.url?.startsWith('/api/')) {
            try {
                await axios.get('/sanctum/csrf-cookie');
                csrfInitialized = true;
            } catch (error) {
                console.error('CSRF token initialization failed:', error);
            }
        }
        return config;
    },
    (error: AxiosError) => {
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
