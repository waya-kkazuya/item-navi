export type LinkType = { 
    url: string | null;
    label: string;
    active: boolean;
};

type Paginator<T> = {
    current_page: number;
    data: T[];
    from: number;
    first_page_url: string;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
    links: LinkType[];
}
  
export type { Paginator };

export type ValidationErrors = {
    [key: string]: string[]
};