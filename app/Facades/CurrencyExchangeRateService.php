<?php

namespace App\Facades;

use App\Services\BOJCurrencyExchangeRateService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDataTableIdFromHtmlTableId(string $htmlTableId)
 * @method static string getNonceFromDataTableId(string $dataTableId)
 * @method static array getExchangeRates(string $startDate, string $endDate = null)
 * @method static string convertBOJCurrencyToISOCurrency(string $bojCurrency)
 */
class CurrencyExchangeRateService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BOJCurrencyExchangeRateService::class;
    }
}
