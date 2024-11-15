export {};

declare global {
    interface Window {
        axios: typeof axios;
        MSStream: unknown;
        Ziggy: Record<string, any>;
    }
}