export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    supportedCurrencies: string[];
    sourceCurrencyCode: string;
    sourceAmount: string;
    targetCurrencyCode: string;
    targetAmount: number;
    exchangeRateDate: string;
    exchangeRate: number;
    auth: {
        user: User;
    };
};
