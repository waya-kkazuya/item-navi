import type { StockTransactionType } from "./model";

export type GraphData = {
    stockTransactions: StockTransactionType[];
    labels: string[];
    stocks: number[];
    transaction_types: string[];
};