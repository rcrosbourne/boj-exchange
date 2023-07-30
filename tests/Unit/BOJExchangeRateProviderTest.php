<?php

use Brick\Money\Context\DefaultContext;
use Brick\Money\Money;

it('can covert USD to JMD and USD to JMD', function () {
    // Set up Exchange Rates
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    // Set up provider
    $exchangeRateProvider = new \App\Services\BOJExchangeRateProvider([
        'start_date' => '2022-06-01',
    ]);
    // Create base currency provider
    $baseCurrencyProvider = new \Brick\Money\ExchangeRateProvider\BaseCurrencyProvider($exchangeRateProvider, 'JMD');

    // Convert USD to JMD
    $exchangeRateJMDToUSD = $baseCurrencyProvider->getExchangeRate('JMD', 'USD');
    $exchangeRateUSDToJMD = $baseCurrencyProvider->getExchangeRate('USD', 'JMD');

    expect($exchangeRateJMDToUSD->toFloat())->toBe(155.8292)
        ->and($exchangeRateUSDToJMD->toScale(4, \Brick\Math\RoundingMode::HALF_EVEN)->toFloat())->toBe(0.0064);
    // Now lets try convert USD to JMD based on the exchange rate
    $usd = Money::of(100, 'USD');
    $jmd = Money::of(100, 'JMD');
    $asJmd = $usd->convertedTo('JMD', $exchangeRateJMDToUSD, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    $asUsd = $jmd->convertedTo('USD', $exchangeRateUSDToJMD, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    expect($asJmd->getAmount()->toFloat())->toBe(15582.92)
        ->and($asUsd->getAmount()->toFloat())->toBe(0.64);
});
it('can convert between USD and GBP through JMD', function () {
    // Set up Exchange Rates
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'USD',
        'buy_price' => '153.3627',
        'sell_price' => '155.8292',
    ]);
    \App\Models\ExchangeRate::create([
        'date' => '2022-06-01',
        'currency' => 'GBP',
        'buy_price' => '186.5375',
        'sell_price' => '193.3157',
    ]);
    // Set up provider
    $exchangeRateProvider = new \App\Services\BOJExchangeRateProvider([
        'start_date' => '2022-06-01',
    ]);
    // Create base currency provider
    $baseCurrencyProvider = new \Brick\Money\ExchangeRateProvider\BaseCurrencyProvider($exchangeRateProvider, 'JMD');

    // Convert USD to JMD
    $exchangeRateJMDToUSD = $baseCurrencyProvider->getExchangeRate('JMD', 'USD');
    $exchangeRateUSDToJMD = $baseCurrencyProvider->getExchangeRate('USD', 'JMD');
    $exchangeRateJMDToGBP = $baseCurrencyProvider->getExchangeRate('JMD', 'GBP');
    $exchangeRateGBPToJMD = $baseCurrencyProvider->getExchangeRate('GBP', 'JMD');
    $exchangeRateUSDToGBP = $baseCurrencyProvider->getExchangeRate('USD', 'GBP');
    $exchangeRateGBPToUSD = $baseCurrencyProvider->getExchangeRate('GBP', 'USD');
    expect($exchangeRateJMDToUSD->toFloat())->toBe(155.8292)
        ->and($exchangeRateUSDToJMD->toScale(4, \Brick\Math\RoundingMode::HALF_EVEN)->toFloat())->toBe(0.0064);

    // Now lets try convert USD to JMD based on the exchange rate
    $usd = Money::of('100.00', 'USD');
    $jmd = Money::of('100.00', 'JMD');
    $asJmd = $usd->convertedTo('JMD', $exchangeRateJMDToUSD, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    $asUsd = $jmd->convertedTo('USD', $exchangeRateUSDToJMD, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    expect($asJmd->getAmount()->toFloat())->toBe(15582.92)
        ->and($asUsd->getAmount()->toFloat())->toBe(0.64)
        ->and($exchangeRateJMDToGBP->toFloat())->toBe(193.3157)
        ->and($exchangeRateGBPToJMD->toScale(4, \Brick\Math\RoundingMode::HALF_EVEN)->toFloat())->toBe(0.0052)
//        USD to GBP through JMD
        ->and($exchangeRateUSDToGBP->toScale(4, \Brick\Math\RoundingMode::HALF_EVEN)->toFloat())->toBe(1.2406)
//        GBP to USD through JMD
        ->and($exchangeRateGBPToUSD->toScale(4, \Brick\Math\RoundingMode::HALF_EVEN)->toFloat())->toBe(0.8061);

    $usd = Money::of('100.00', 'USD');
    $gbp = Money::of('100.00', 'GBP');
    $asGBP = $usd->convertedTo('GBP', $exchangeRateUSDToGBP, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    $asUSD = $gbp->convertedTo('USD', $exchangeRateGBPToUSD, new DefaultContext(), \Brick\Math\RoundingMode::HALF_EVEN);
    expect($asGBP->getAmount()->toFloat())->toBe(124.06)
        ->and($asUSD->getAmount()->toFloat())->toBe(80.61);
});
