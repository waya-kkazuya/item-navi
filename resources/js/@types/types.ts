type Link = { 
    url: string | null;
    label: string;
    active: boolean;
};

type Paginator<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
    links: Link[];
}
  
export type { Paginator };