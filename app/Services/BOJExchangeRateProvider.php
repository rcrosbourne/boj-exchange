<?php

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
    public function __construct(array $config)
    {
        if (! Arr::has($config, 'start_date')) {
            // use the date of the last record in the database
            $last = ExchangeRate::orderBy('date', 'desc')->first();
            if ($last) {
                $this->startDate = $last->date->format('Y-m-d');
            } else {
                throw new \Exception('Missing start_date in config');
            }
            throw new \Exception('Missing start_date in config');
        }
        $this->startDate = Carbon::createFromFormat('Y-m-d', $config['start_date'])->startOfDay();
        // the start date need to be a weekday if not it should be adjusted to the next weekday
        // we will ignore public holidays for now
        $this->startDate = $this->startDate->isWeekday() ? $this->startDate : $this->startDate->nextWeekday();
        $this->endDate = Arr::has($config, 'end_date') ? Carbon::createFromFormat('Y-m-d', Arr::get($config, 'end_date'))->endOfDay() : null;
        if ($this->endDate) {
            $this->endDate = $this->endDate->isWeekday() ? $this->endDate : $this->endDate->nextWeekday();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): BigNumber|int|float|string
    {
        // All exchange rates are relative to JMD (Jamaican Dollar).
        if ($sourceCurrencyCode === 'JMD') {
            $exchangeRate = ExchangeRate::where('date', $this->startDate)->where('currency', $targetCurrencyCode)->first();
            if (! $exchangeRate) {
                throw throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$targetCurrencyCode.' on '.$this->startDate->format('Y-m-d'));
            }

            return $exchangeRate->sell_price;
        }
        // throw exception we are only supporting JMD as the base currency
        throw CurrencyConversionException::exchangeRateNotAvailable($sourceCurrencyCode, $targetCurrencyCode, 'Missing exchange rate for '.$sourceCurrencyCode.' on '.$this->startDate);
    }
}
