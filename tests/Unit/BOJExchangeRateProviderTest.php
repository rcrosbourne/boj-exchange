<?php

declare(strict_types=1);

use App\Facades\CurrencyExchangeRateService;
use App\Models\ExchangeRate;
use Brick\Money\Exception\CurrencyConversionException;
use Brick\Money\Money;

it('can covert USD to JMD and USD to JMD', function () {
    // Set up Exchange Rates
    ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    $exchangeRateJMDToUSD = CurrencyExchangeRateService::getExchangeRatesForCurrency('JMD', 'USD');
    $exchangeRateUSDToJMD = CurrencyExchangeRateService::getExchangeRatesForCurrency('USD', 'JMD', '2022-06-01');
    expect($exchangeRateJMDToUSD->getAmount()->toFloat())->toBe(155.8292)
        ->and($exchangeRateUSDToJMD->getAmount()->toFloat())->toBe(0.0064);
    $usd = Money::of(1000, 'USD');
    $jmd = Money::of(1000, 'JMD');
    $convertToJmd = CurrencyExchangeRateService::convertTo('JMD', $usd, '2022-06-01');
    $convertToUsd = CurrencyExchangeRateService::convertTo('USD', $jmd);
    expect($convertToJmd->getAmount()->toFloat())->toBe(155829.200)
        ->and($convertToUsd->getAmount()->toFloat())->toBe(6.40);
});
it('throws an exception if the exchange rate is not available', function () {
    ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    $usd = Money::of(1000, 'USD');
    CurrencyExchangeRateService::convertTo('CAD', $usd);
})->throws(CurrencyConversionException::class, 'No exchange rate available to convert JMD to CAD (Missing exchange rate for CAD on 2022-06-01)');

it('can convert between USD and GBP through JMD', function () {
    // Set up Exchange Rates
    ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'GBP',
        'buy_price' => '186.5375',
        'sell_price' => '193.3157',
    ]);
    // Convert USD to JMD
    $exchangeRateJMDToUSD = CurrencyExchangeRateService::getExchangeRatesForCurrency('JMD', 'USD');
    $exchangeRateUSDToJMD = CurrencyExchangeRateService::getExchangeRatesForCurrency('USD', 'JMD');
    $exchangeRateJMDToGBP = CurrencyExchangeRateService::getExchangeRatesForCurrency('JMD', 'GBP');
    $exchangeRateGBPToJMD = CurrencyExchangeRateService::getExchangeRatesForCurrency('GBP', 'JMD');
    $exchangeRateUSDToGBP = CurrencyExchangeRateService::getExchangeRatesForCurrency('USD', 'GBP');
    $exchangeRateGBPToUSD = CurrencyExchangeRateService::getExchangeRatesForCurrency('GBP', 'USD');
    expect($exchangeRateJMDToUSD->getAmount()->toFloat())->toBe(155.8292)
        ->and($exchangeRateUSDToJMD->getAmount()->toFloat())->toBe(0.0064)
        ->and($exchangeRateJMDToGBP->getAmount()->toFloat())->toBe(193.3157)
        ->and($exchangeRateGBPToJMD->getAmount()->toFloat())->toBe(0.0052)
        ->and($exchangeRateUSDToGBP->getAmount()->toFloat())->toBe(1.2406)
        ->and($exchangeRateGBPToUSD->getAmount()->toFloat())->toBe(0.8061);

    // Now lets try convert USD to GBP based on the exchange rate
    $usd = Money::of(100, 'USD');
    $gbp = Money::of(100, 'GBP');
    $convertToGbp = CurrencyExchangeRateService::convertTo('GBP', $usd);
    $convertToUsd = CurrencyExchangeRateService::convertTo('USD', $gbp);
    expect($convertToGbp->getAmount()->toFloat())->toBe(80.61)->and($convertToUsd->getAmount()->toFloat())->toBe(124.06);
});
it('can retrieves supported currencies', function () {
    // Given I have some exchange records
    $usdExchangeRate = ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    $gbpExchangeRate = ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'GBP',
        'buy_price' => '186.5375',
        'sell_price' => '193.3157',

    ]);
    $cadExchangeRate = ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'CAD',
        'buy_price' => '120.5375',
        'sell_price' => '123.3157',
    ]);
    // When I retrieve the supported currencies
    $supportedCurrencies = CurrencyExchangeRateService::getSupportedCurrencies();
    // Then I should get a list of supported currencies
    expect($supportedCurrencies)->toBeArray()
        ->and($supportedCurrencies)->toContain('USD')
        ->and($supportedCurrencies)->toContain('GBP')
        ->and($supportedCurrencies)->toContain('CAD')
        ->and($supportedCurrencies)->toContain('JMD')
        ->and($supportedCurrencies)->not()->toContain('EUR');
});
