<?php

declare(strict_types=1);

namespace App\Facades;

use App\Enums\ExchangeRateType;
use App\Services\BOJCurrencyExchangeRateService;
use Brick\Money\Money;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getDataTableIdFromHtmlTableId(string $htmlTableId)
 * @method static string getNonceFromDataTableId(string $dataTableId)
 * @method static array getExchangeRates(string $startDate, string $endDate = null)
 * @method static string convertBOJCurrencyToISOCurrency(string $bojCurrency)
 * @method static bool areExchangeRatesLoaded(string $startDate, string $endDate = null)
 * @method static bool saveExchangeRates(array  $exchangeRates)
 * @method static array getSupportedCurrencies()
 * @method static Money getExchangeRatesForCurrency(string $source, string $target, ?string $startDate = null, ?string $endDate = null, ExchangeRateType $exchangeRateType = ExchangeRateType::SELLING_RATE)
 * @method static Money convertTo(string $targetIsoCurrency, Money $sourceAmount, ?string $date = null, ExchangeRateType $exchangeRateType = ExchangeRateType::SELLING_RATE)
 */
class CurrencyExchangeRateService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BOJCurrencyExchangeRateService::class;
    }
}
