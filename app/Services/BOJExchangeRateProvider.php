<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Math\BigNumber;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\ExchangeRateProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class BOJExchangeRateProvider implements ExchangeRateProvider
{
    protected Carbon $startDate;

    protected ?Carbon $endDate;

    /**
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        if (! Arr::has($config, 'start_date') || Arr::get($config, 'start_date') === null) {
            // use the date of the last record in the database
            $this->startDate = Carbon::parse(ExchangeRate::max('date'))->startOfDay();
        } else {
            $dateEntered = Carbon::createFromFormat('Y-m-d', $config['start_date']);
            $this->startDate = $dateEntered->startOfDay();
        }
        $this->endDate = Arr::has($config, 'end_date') && Arr::get($config, 'end_date') !== null
            ? Carbon::createFromFormat('Y-m-d', Arr::get($config, 'end_date'))->endOfDay()
            : $this->startDate->copy()->endOfDay();
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): BigNumber|int|float|string
    {
        // All exchange rates are relative to JMD (Jamaican Dollar).
        if ($sourceCurrencyCode === 'JMD') {
            $exchangeRate = ExchangeRate::where('date', '>=', $this->startDate)->where('currency', $targetCurrencyCode)->first();
            if (! $exchangeRate) {
                throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$targetCurrencyCode.' on '.$this->startDate->format('Y-m-d'));
            }

            return $exchangeRate->sell_price;
        }
        // throw exception we are only supporting JMD as the base currency
        throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$sourceCurrencyCode.' on '.$this->startDate);
    }
}
