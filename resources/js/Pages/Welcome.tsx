import React from "react";
import {PageProps} from "@/types";
import CurrencySwitcher from "@/Components/ShadcnUI/CurrencySwitcher";

export default function Welcome({supportedCurrencies}: PageProps) {
    const [sourceCurrency, setSourceCurrency] = React.useState('JMD')
    const [targetCurrency, setTargetCurrency] = React.useState('USD')
    return (
        <>
            <div className="hidden flex-col md:flex">
                <div className="flex-1 space-y-4 p-8 pt-6">
                    <div className="flex items-center justify-between space-y-2">
                        <h2 className="text-3xl font-bold tracking-tight">Bank of Jamaica - Exchange Rates</h2>
                    </div>
                    <CurrencySwitcher supportedCurrencies={supportedCurrencies}
                                      defaultCurrency={'JMD'}
                                      selectedCurrency={sourceCurrency}
                                      setSelectedCurrency={setSourceCurrency}/>
                    <CurrencySwitcher supportedCurrencies={supportedCurrencies}
                                      defaultCurrency={'USD'}
                                      selectedCurrency={targetCurrency}
                                      setSelectedCurrency={setTargetCurrency}/>
                </div>
            </div>
        </>
    )
}
