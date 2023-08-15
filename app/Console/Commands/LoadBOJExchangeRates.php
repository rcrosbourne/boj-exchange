<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\CurrencyExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class LoadBOJExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boj:load-exchange-rates {startDate?} {endDate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command loads exchange rates from the Bank of Jamaica website.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = $this->argument('startDate');
        $endDate = $this->argument('endDate');
        if ($startDate === null) {
            $startDate = (new Carbon())->startOfDay()->format('Y-m-d');
        }
        if ($endDate === null) {
            $endDate = (new Carbon())->endOfDay()->format('Y-m-d');
        }
        // Check if rates are already loaded in the database.
        $this->info('Checking if exchange rates are already loaded in the database.');
        if (CurrencyExchangeRateService::areExchangeRatesLoaded($startDate, $endDate)) {
            $this->info('Exchange rates are already loaded in the database.');

            return;
        }
        $this->info('Loading exchange rates from '.$startDate.' to '.$endDate);
        $exchangeRates = CurrencyExchangeRateService::getExchangeRates($startDate, $endDate);
        if (count($exchangeRates) === 0) {
            $this->info('No exchange rates found for date range.');

            return;
        }
        $this->info('Found '.count($exchangeRates).' exchange rates.');
        // save exchange rates to database
        $this->info('Saving exchange rates to database.');
        $status = CurrencyExchangeRateService::saveExchangeRates($exchangeRates);

        if ($status) {
            $this->info('Exchange rates saved to database.');
        } else {
            $this->error('Failed to save exchange rates to database.');
        }
    }
}
