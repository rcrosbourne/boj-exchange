import React, {useEffect} from "react";
import {PageProps} from "@/types";
import CurrencySwitcher from "@/Components/ShadcnUI/CurrencySwitcher";
import {Input} from "@/Components/ShadcnUI/Input";
import {DatePicker} from "@/Components/ShadcnUI/DatePicker";
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/Components/ShadcnUI/Card";
import {router, usePage} from '@inertiajs/react'
import {Skeleton} from "@/Components/ShadcnUI/Skeleton";
import {RadioGroup, RadioGroupItem} from "@/Components/ShadcnUI/RadioGroup";
import {Label} from "@/Components/ShadcnUI/Label";
type ExchangeRateType = 'selling_rate' | 'cheque_buying_rate' | 'cash_buying_rate';
export default function Welcome({supportedCurrencies, targetAmount, exchangeRate}: PageProps) {
    const {errors} = usePage().props

    const [sourceCurrency, setSourceCurrency] = React.useState('USD')
    const [sourceAmount, setSourceAmount] = React.useState("")
    const [targetCurrency, setTargetCurrency] = React.useState('JMD')
    const [exchangeRateDate, setExchangeRateDate] = React.useState<Date | undefined>(new Date())
    const [isLoading, setIsLoading] = React.useState(false);
    const [exchangeRateType, setExchangeRateType] = React.useState<ExchangeRateType>('selling_rate');
    // const [conversionRate, setConversionRate] = React.useState("");
    // const [targetAmount, setTargetAmount] = React.useState("");

    router.on('start', (event) => {
        setIsLoading(true)
    });
    router.on('finish', (event) => {
        setIsLoading(false)
    });
    function formatExchangeRateType(type: ExchangeRateType) {
        switch (type) {
            case "selling_rate":
                return "selling";
            case "cheque_buying_rate":
                return "buy (cheque)";
            case "cash_buying_rate":
                return "buy (cash)";
        }
    }

    async function update() {
        if (!sourceAmount) return;
        console.log({sourceAmount, sourceCurrency, targetCurrency, exchangeRateDate});
        router.post(route('convert'), {
            'source_currency_code': sourceCurrency,
            'source_amount': sourceAmount,
            'target_currency_code': targetCurrency,
            'exchange_rate_date': exchangeRateDate?.toISOString().split('T')[0],
            'exchange_rate_type': exchangeRateType,
        });
    }

    useEffect(() => {
        void update();
    }, [sourceAmount, sourceCurrency, targetCurrency, exchangeRateDate, exchangeRateType])

    function currencyFormatter(currency: string) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency,
        });
    }

    return (
        <>
            <div className="flex-col md:flex max-w-4xl">
                <div className="flex-1 space-y-4 p-8 pt-6">
                    <div className="flex items-center justify-between space-y-2">
                        <h2 className="text-3xl font-bold tracking-tight">Bank of Jamaica - Exchange Rates</h2>
                    </div>
                    <div className="flex gap-2">
                        <Input value={sourceAmount}
                               onChange={(e) => setSourceAmount(e.target.value)}
                               placeholder={'Enter amount'}/>

                        <CurrencySwitcher supportedCurrencies={supportedCurrencies}
                                          defaultCurrency={'JMD'}
                                          selectedCurrency={sourceCurrency}
                                          setSelectedCurrency={setSourceCurrency}/>
                    </div>
                    <div className="grid grid-cols-2 gap-2 md:grid-cols-4 items-center">
                        <div className="flex col-span-2 items-center gap-2">
                            <p className="flex-1">Converted to</p>
                            <CurrencySwitcher supportedCurrencies={supportedCurrencies}
                                              defaultCurrency={'USD'}
                                              selectedCurrency={targetCurrency}
                                              setSelectedCurrency={setTargetCurrency}/>
                        </div>
                        <div className="border-2 border-b border-slate-100 col-span-2 mt-3 rounded md:hidden" />
                        <div className="col-span-2 md:col-span-1 md:mx-auto"> <p className="text-lg">using</p></div>
                        <RadioGroup className="col-span-2 md:col-span-1" defaultValue="selling_rate" onValueChange={ e =>  setExchangeRateType(e as ExchangeRateType)}>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="selling_rate" id="selling_rate"/>
                                <Label htmlFor="selling_rate">Selling Rate</Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="cheque_buying_rate" id="cheque_buying_rate"/>
                                <Label htmlFor="cheque_buying_rate">Buying Rate (Cheque)</Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="cash_buying_rate" id="cash_buying_rate"/>
                                <Label htmlFor="cash_buying_rate">Buying Rate (Cash)</Label>
                            </div>
                        </RadioGroup>
                        <div className="border-2 border-b border-slate-100 col-span-2 mt-3 rounded md:hidden" />
                        <div className="col-span-2 md:col-span-1 w-full flex items-center gap-2 mt-3">
                                <p>on</p>
                            <DatePicker className="w-full" date={exchangeRateDate} setDate={setExchangeRateDate}/>
                        </div>
                    </div>
                    {isLoading &&
                        <div className="border rounded-lg p-6">
                            <div className="flex items-center space-x-4">
                                <div className="space-y-2">
                                    <Skeleton className="h-8 w-[90px] md:w-[180px]"/>
                                    <Skeleton className="h-4 w-[150px] md:w-[500px]"/>
                                    <Skeleton className="h-4 w-[150px] md:w-[500px]"/>
                                </div>
                            </div>
                            <Skeleton className="h-4 w-full mt-10"/>
                        </div>
                    }
                    {!isLoading && sourceAmount && <Card>
                        <CardHeader>
                            <CardTitle>{currencyFormatter(targetCurrency).format(targetAmount)}</CardTitle>
                            <CardDescription>
                                <p>{currencyFormatter(sourceCurrency).format(parseFloat(sourceAmount))} {sourceCurrency} converted
                                    to {targetCurrency} using the BOJ {formatExchangeRateType(exchangeRateType)} rates
                                    for {exchangeRateDate && exchangeRateDate >= new Date() ? "today" : exchangeRateDate?.toDateString()}</p>
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-col space-y-2">
                                <div className="flex justify-between">
                                    <p>Exchange Rate</p>
                                    <p>1 {sourceCurrency} = {currencyFormatter(targetCurrency).format(exchangeRate)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    }
                </div>
            </div>
        </>
    )
}
