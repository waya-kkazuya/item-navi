interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    // 他の環境変数を追加
}
  
interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: (pattern: string) => Record<string, () => Promise<unknown>>;
}
  