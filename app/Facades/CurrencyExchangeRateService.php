<?php

namespace App\Facades;

use App\Services\BOJCurrencyExchangeRateService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDataTableIdFromHtmlTableId(string $htmlTableId)
 * @method static string getNonceFromDataTableId(string $dataTableId)
 * @method static array getExchangeRates(string $startDate, string $endDate = null)
 * @method static string convertBOJCurrencyToISOCurrency(string $bojCurrency)
 * @method static bool areExchangeRatesLoaded(string $startDate, string $endDate = null)
 * @method static bool saveExchangeRates(array  $exchangeRates)
 */
class CurrencyExchangeRateService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BOJCurrencyExchangeRateService::class;
    }
}
