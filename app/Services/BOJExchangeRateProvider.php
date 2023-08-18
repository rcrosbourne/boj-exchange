<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Math\BigNumber;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\ExchangeRateProvider;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class BOJExchangeRateProvider implements ExchangeRateProvider
{
    protected Carbon $startDate;

    protected ?Carbon $endDate;

    /**
     * @throws Exception|InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        if (! Arr::has($config, 'start_date') || Arr::get($config, 'start_date') === null) {
            // use the date of the last record in the database
            // Cache max date
            if (Cache::store('redis')->has('max_date')) {
                $this->startDate = Cache::store('redis')->get('max_date');
            } else {
                $this->startDate = Carbon::parse(ExchangeRate::max('date'))->startOfDay();
                // Cache max date
                Cache::store('redis')->put('max_date', $this->startDate, 60 * 60 * 24);
            }
        } else {
            $dateEntered = Carbon::createFromFormat('Y-m-d', $config['start_date']);
            $this->startDate = $dateEntered->startOfDay();
        }
        $this->endDate = Arr::has($config, 'end_date') && Arr::get($config, 'end_date') !== null
            ? Carbon::createFromFormat('Y-m-d', Arr::get($config, 'end_date'))->endOfDay()
            : $this->startDate->copy()->endOfDay();
    }

    /**
     * Get the exchange rate between two currencies.
     *
     * @param  string  $sourceCurrencyCode The currency code of the source currency.
     * @param  string  $targetCurrencyCode The currency code of the target currency.
     * @return BigNumber|int|float|string The exchange rate between the two currencies.
     *
     * @throws CurrencyConversionException If the exchange rate is not available.
     * @throws Exception If an error occurs while retrieving the exchange rate.
     */
    public function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): BigNumber|int|float|string
    {
        // All exchange rates are relative to JMD (Jamaican Dollar).
        $cacheKey = 'exchange_rate_'.$sourceCurrencyCode.'_'.$targetCurrencyCode.'_'.$this->startDate->format('Y-m-d');
        Log::debug('Cache Key', [
            'cacheKey' => $cacheKey,
        ]);
        if ($sourceCurrencyCode === 'JMD') {
            // Check if exchange rate is cached for the data
            $exchangeRate = null;
            if (Cache::has($cacheKey)) {
                $exchangeRate = Cache::get($cacheKey);
            } else {
                Log::debug('Cache Miss', [
                    'cacheKey' => $cacheKey,
                ]);
                $exchangeRate = ExchangeRate::where('date', '>=', $this->startDate)->where('currency', $targetCurrencyCode)->first();
                // Cache exchangeRate
                Cache::put($cacheKey, $exchangeRate, 60 * 60 * 24);
            }
            if (! $exchangeRate) {
                throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$targetCurrencyCode.' on '.$this->startDate->format('Y-m-d'));
            }

            return $exchangeRate->sell_price;
        }
        // throw exception we are only supporting JMD as the base currency
        throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$sourceCurrencyCode.' on '.$this->startDate);
    }
}
