import type { AxiosInstance } from "axios";

declare module '@/apiClient' {
    const apiClient: AxiosInstance;
}