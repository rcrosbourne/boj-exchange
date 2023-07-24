<?php

use App\Facades\CurrencyExchangeRateService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->baseUrl = config('app.boj_base_url');
    $this->tableId = config('app.boj_currency_exchange_table_id');
    $this->dataTableId = config('app.boj_currency_exchange_data_table_id');
    Http::fake([
        'https://boj.org.jm/market/foreign-exchange/counter-rates' => Http::response(file_get_contents(__DIR__.'/../Fixtures/foreign-exchange.html'), 200),
    ]);
});
it('can determine the data table Id', function () {
    $tableId = CurrencyExchangeRateService::getDataTableIdFromHtmlTableId($this->tableId);
    expect($tableId)->toBeString()
        ->and($tableId)->not->toBeEmpty()
        ->and($tableId)->toEqual('134');
});
it('can determine the nonce value for that table id', function () {
    $nonce = CurrencyExchangeRateService::getNonceFromDataTableId($this->dataTableId);
    expect($nonce)->toBeString()
        ->and($nonce)->not->toBeEmpty()
        ->and($nonce)->toEqual('f77c57c352');
});
