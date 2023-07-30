<?php

use App\Facades\CurrencyExchangeRateService;
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
        ->and($nonce)->not->toBeEmpty()
        ->and($nonce)->toEqual('f77c57c352');
});
it('can determine the exchange rates for a given date', function () {
    $date = '2023-06-01';
    $exchangeRates = CurrencyExchangeRateService::getExchangeRates($date);
    expect($exchangeRates)->toBeArray()
        ->and($exchangeRates)->not->toBeEmpty()
        ->and($exchangeRates)->toContainOnlyInstancesOf(\App\Models\ExchangeRate::class)
        // Assert that the dates returned contain any day from June 2023 but not from May 2023
        ->expect($exchangeRates)->each->date->toBeBetween('2023-06-01', '2023-06-30');
});
