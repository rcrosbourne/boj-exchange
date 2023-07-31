<?php

use App\Facades\CurrencyExchangeRateService;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;

/** @var Tests\TestCase $this */
beforeEach(function () {
    $this->baseUrl = config('app.boj_base_url');
    $this->tableId = config('app.boj_currency_exchange_table_id');
    $this->dataTableId = config('app.boj_currency_exchange_data_table_id');
    Http::fake(function ($request) {
        if ($request->url() === "{$this->baseUrl}/market/foreign-exchange/counter-rates") {
            return Http::response(file_get_contents(__DIR__.'/../Fixtures/foreign-exchange.html'), 200);
        }
        // If we have a date range, return the rates for that date range.
        if ($request->data()['columns[0][search][value]'] === '01 Jun 2023') {
            return Http::response(file_get_contents(__DIR__.'/../Fixtures/boj-rates-with-date-range-2023-06-01-to-2023-06-30.json'), 200);
        }
        if ($request->data()['columns[0][search][value]'] === '01 Jun 2023|10 Jun 2023') {
            return Http::response(file_get_contents(__DIR__.'/../Fixtures/boj-rates-with-date-range-2023-06-01-to-2023-06-10.json'), 200);
        }

        return Http::response(file_get_contents(__DIR__.'/../Fixtures/boj-rates.json'), 200);
    });
});

it('can determine the data table Id containing the rates', function () {
    $tableId = CurrencyExchangeRateService::getDataTableIdFromHtmlTableId($this->tableId);
    expect($tableId)->toBeString()
        ->and($tableId)->not->toBeEmpty()
        ->and($tableId)->toEqual('134');
});
it('can determine the nonce value for that table id containing the rates', function () {
    $nonce = CurrencyExchangeRateService::getNonceFromDataTableId($this->dataTableId);
    expect($nonce)->toBeString()
        ->and($nonce)->not->toBeEmpty();
});
it('can determine the exchange rates for a given date up to current', function () {
    $date = '2023-06-01';
    $exchangeRates = CurrencyExchangeRateService::getExchangeRates($date);
    expect($exchangeRates)->each(fn ($rate) => $rate->date->isBetween('2023-06-01', '2023-06-30'))
        ->and($exchangeRates)->not->toBeEmpty()
        ->and($exchangeRates)->toContainOnlyInstancesOf(ExchangeRate::class);
});
it('can determine the exchange rate for a given date range', function () {
    $startDate = '2023-06-01';
    $endDate = '2023-06-10';
    $exchangeRates = CurrencyExchangeRateService::getExchangeRates($startDate, $endDate);
    $outOfRange = collect($exchangeRates)->filter(fn ($rate) => $rate->date->isAfter($endDate));
    expect($outOfRange)->toBeEmpty()
        ->and($exchangeRates)->not->toBeEmpty()
        ->and($exchangeRates)->toContainOnlyInstancesOf(ExchangeRate::class);
});
it('can convert BOJ non-standard currency to ISO 4217 standard currency', function (string $bojCurrency, string $expectedISOCurrency) {
    $isoCurrency = CurrencyExchangeRateService::convertBOJCurrencyToISOCurrency($bojCurrency);
    expect($isoCurrency)->toBeString()
        ->and($isoCurrency)->not->toBeEmpty()
        ->and($isoCurrency)->toEqual($expectedISOCurrency);
})->with([
    ['AUSTRALIAN DOLLAR', 'AUD'],
    ['BAHAMAS DOLLAR', 'BSD'],
    ['BARBADOS DOLLAR', 'BBD'],
    ['BELIZE DOLLAR', 'BZD'],
    ['CANADA DOLLAR', 'CAD'],
    ['CAYMAN DOLLAR', 'KYD'],
    ['DANISH KRONA', 'DKK'],
    ['DANISH KRONE', 'DKK'],
    ['DOMINICAN REP. PESO', 'DOP'],
    ['E. C. DOLLAR', 'XCD'],
    ['EURO', 'EUR'],
    ['GIBRALTAR POUND', 'GIP'],
    ['GREAT BRITAIN POUND', 'GBP'],
    ['GUYANA DOLLAR', 'GYD'],
    ['HONG KONG DOLLAR', 'HKD'],
    ['JAMAICA DOLLAR', 'JMD'],
    ['JAPANESE YEN', 'JPY'],
    ['NORTHERN IRELAND POUND', 'GBP'],
    ['NORWEGIAN KRONE', 'NOK'],
    ['SWEDISH KRONA', 'SEK'],
    ['SWISS FRANC', 'CHF'],
    ['T&T DOLLAR', 'TTD'],
    ['U.S. DOLLAR', 'USD'],
]);
it('uses ISO currencies when getting exchange rates', function () {
    $date = '2023-06-01';
    $exchangeRates = CurrencyExchangeRateService::getExchangeRates($date);
    // expect currency to be a valid ISO currency from the list of currencies
    // returned by the BOJ.
    $currencies = collect($exchangeRates)->pluck('currency')->unique()->toArray();
    $validIsoCurrencies = collect(config('app.boj_currency_to_iso_currency_map'))->values()->toArray();
    expect($currencies)->toBeArray()
        ->and($currencies)->not->toBeEmpty()
        ->and($currencies)->toBeSubsetOf($validIsoCurrencies);
});
it('can determine if rates are already loaded', function () {
    //set up exchange rates for 4 days
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-02',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-03',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    $startDate = '2022-06-01';
    $endDate = '2022-06-03';
    $ratesLoaded = CurrencyExchangeRateService::areExchangeRatesLoaded($startDate, $endDate);
    expect($ratesLoaded)->toBe(true);
    $startDate = '2022-06-04';
    $ratesLoaded = CurrencyExchangeRateService::areExchangeRatesLoaded($startDate);
    expect($ratesLoaded)->toBe(false);
});
it('can persist a collection of exchange rates to the database', function () {
    $exchangeRates = [
        new ExchangeRate([
            'date' => '2022-06-01',
            'currency' => 'USD',
            'buy_price' => '153.3627',
            'sell_price' => '155.8292',
        ]),
        new ExchangeRate([
            'date' => '2022-06-02',
            'currency' => 'USD',
            'buy_price' => '153.3627',
            'sell_price' => '155.8292',
        ]),
        new ExchangeRate([
            'date' => '2022-06-03',
            'currency' => 'USD',
            'buy_price' => '153.3627',
            'sell_price' => '155.8292',
        ]),
    ];
    $savedExchangeRates = CurrencyExchangeRateService::saveExchangeRates($exchangeRates);
    expect($savedExchangeRates)->toBe(true);
});
