<?php

namespace App\Facades;

use App\Services\BOJCurrencyExchangeRateService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDataTableIdFromHtmlTableId(string $htmlTableId)
 * @method static string getNonceFromDataTableId(string $dataTableId)
 */
class CurrencyExchangeRateService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BOJCurrencyExchangeRateService::class;
    }
}
