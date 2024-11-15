export {};

declare global {
    interface Window {
        axios: typeof axios;
    }
}