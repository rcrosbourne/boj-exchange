<?php

namespace App\Enums;

enum ExchangeRateType
{
    case CASH_BUYING_RATE;
    case CHEQUE_BUYING_RATE;
    case SELLING_RATE;

    public function asString(): string
    {
        return match ($this) {
            self::CASH_BUYING_RATE => 'cash_buying_rate',
            self::CHEQUE_BUYING_RATE => 'cheque_buying_rate',
            self::SELLING_RATE => 'selling_rate',
        };
    }

    /**
     * @throws \Exception
     */
    public static function fromString(string $exchangeRateType): self
    {
        return match ($exchangeRateType) {
            'cash_buying_rate' => self::CASH_BUYING_RATE,
            'cheque_buying_rate' => self::CHEQUE_BUYING_RATE,
            'selling_rate' => self::SELLING_RATE,
            default => throw new \Exception('Invalid exchange rate type'),
        };
    }
}
