<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\CurrencyExchangeRateService;
use App\Models\ExchangeRate;
use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supportedCurrencies = CurrencyExchangeRateService::getSupportedCurrencies();

        return Inertia::render('Welcome', [
            'supportedCurrencies' => ['JMD', 'USD'], //$supportedCurrencies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sourceCurrencyCode = $request->input('source_currency_code');
        $sourceAmount = $request->input('source_amount');
        $targetCurrencyCode = $request->input('target_currency_code');
        $exchangeRateDate = $request->input('exchange_rate_date');
        $exchangeRate = CurrencyExchangeRateService::getExchangeRatesForCurrency($targetCurrencyCode, $sourceCurrencyCode, $exchangeRateDate);
        $sourceAsMoney = Money::of($sourceAmount, $sourceCurrencyCode);
        $targetAmount = CurrencyExchangeRateService::convertTo($targetCurrencyCode, $sourceAsMoney, $exchangeRateDate);
        //        Log::info('Exchange Rate', [
        //            'source_currency_code' => $sourceCurrencyCode,
        //            'source_amount' => $sourceAmount,
        //            'target_currency_code' => $targetCurrencyCode,
        //            'target_amount' => $targetAmount->getAmount()->toFloat(),
        //            'exchange_rate_date' => $exchangeRateDate,
        //            'exchange_rate' => $exchangeRate->getAmount()->toFloat(),
        //        ]);
        return Inertia::render('Welcome', [
            'supportedCurrencies' => CurrencyExchangeRateService::getSupportedCurrencies(),
            'sourceCurrencyCode' => $sourceCurrencyCode,
            'sourceAmount' => $sourceAmount,
            'targetCurrencyCode' => $targetCurrencyCode,
            'targetAmount' => $targetAmount->getAmount()->toFloat(),
            'exchangeRateDate' => $exchangeRateDate,
            'exchangeRate' => $exchangeRate->getAmount()->toFloat(),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(ExchangeRate $exchangeRate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExchangeRate $exchangeRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExchangeRate $exchangeRate)
    {
        //
    }
}
