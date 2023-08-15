<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\ExchangeRateProvider\BaseCurrencyProvider;
use Brick\Money\Money;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BOJCurrencyExchangeRateService
{
    const BASE_CURRENCY = 'JMD';

    private string $boj_base_url;

    private string $page;

    /**
     * Constructs a new instance of the class.
     *
     * The constructor initializes the `$boj_base_url` property with the value retrieved from the configuration file using the `config` helper function.
     * It also sets the `$page` property by calling the `getCounterRatePage` method.
     *
     * @return void
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->boj_base_url = config('app.boj_base_url');
        $this->page = $this->getCounterRatePage();
    }

    /**
     * @throws Exception
     */
    public function getExchangeRatesForCurrency(string $source, string $target, string $startDate = null, string $endDate = null): Money
    {
        $exchangeRateProvider = new BOJExchangeRateProvider([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        // Create base currency provider
        $baseCurrencyProvider = new BaseCurrencyProvider($exchangeRateProvider, self::BASE_CURRENCY);
        $exchangeRate = $baseCurrencyProvider->getExchangeRate($source, $target);

        return Money::of($exchangeRate, $target, new CustomContext(4), RoundingMode::HALF_EVEN);
    }

    /**
     * Converts the source amount to the specified target currency.
     *
     * @param  string  $targetIsoCurrency The ISO code of the target currency.
     * @param  Money  $sourceAmount The source amount to be converted.
     * @param  string|null  $date (Optional) The date for which the exchange rate should be fetched.
     *                          If not provided, the current date will be used.
     * @return Money The converted amount in the target currency.
     *
     * @throws MathException
     * @throws UnknownCurrencyException
     * @throws Exception
     */
    public function convertTo(string $targetIsoCurrency, Money $sourceAmount, string $date = null): Money
    {
        // get exchange rate
        $exchangeRate = $this->getExchangeRatesForCurrency($targetIsoCurrency, $sourceAmount->getCurrency()->getCurrencyCode(), $date);
        // convert to target currency
        return $sourceAmount->convertedTo($targetIsoCurrency, $exchangeRate->getAmount());
    }

    /**
     * Save exchange rates to the database.
     *
     * @param  array  $exchangeRates The exchange rates to save.
     * @return bool Returns true if the exchange rates were saved successfully, false otherwise.
     */
    public function saveExchangeRates(array $exchangeRates): bool
    {
        try {
            DB::beginTransaction();
            /** @var ExchangeRate $exchangeRate */
            foreach ($exchangeRates as $exchangeRate) {
                $exchangeRate->save();
            }
            DB::commit();

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();

            return false;
        }
    }

    /**
     * Check if exchange rates are loaded for the given date range
     *
     * @param  string  $startDate The start date of the exchange rates.
     * @param  string|null  $endDate The end date of the exchange rates. It is optional, default to null.
     * @return bool Returns true if exchange rates are loaded, false otherwise.
     *
     * @throws Exception if there is an error getting saved exchange rates.
     */
    public function areExchangeRatesLoaded(string $startDate, string $endDate = null): bool
    {
        $exchangeRates = $this->getSavedExchangeRates($startDate, $endDate);

        return $exchangeRates->isNotEmpty();
    }

    /**
     * Retrieves exchange rates for a given date range.
     *
     * This method retrieves exchange rates between the start date and end date.
     * The start date is a required parameter and must be in the format 'YYYY-MM-DD'.
     * The end date is an optional parameter and defaults to the current date if not provided.
     *
     * @param  string  $startDate The start date in the format 'YYYY-MM-DD'.
     * @param  string|null  $endDate (Optional) The end date in the format 'YYYY-MM-DD'. Defaults to the current date.
     * @return array An array of exchange rates for the given date range.
     *
     * @throws Exception If there is an error during the retrieval of exchange rates.
     */
    public function getExchangeRates(string $startDate, string $endDate = null): array
    {
        $searchDates = $this->prepareSearchDates($startDate, $endDate);

        $response = $this->fetchExchangeRatesData($searchDates);

        return $this->processResponseAndGetExchanges($response);
    }

    /**
     * Get the list of supported currencies ISO values.
     *
     * @return array The array containing the list of supported currencies.
     */
    public function getSupportedCurrencies(): array
    {
        // Get a distinct list of currencies
        // from the exchange rates table
        return ExchangeRate::distinct('currency')
            ->pluck('currency')
            ->push('JMD')
            ->sort()->values()->toArray();
    }

    /**
     * Prepares the search dates for querying.
     *
     * This method takes the start date and end date as input, and converts them to the desired format for search query.
     * It uses the `Carbon` library to parse the input dates and formats them as 'd M Y'.
     * If the end date is provided, it concatenates the formatted start date and end date with a '|' separator.
     * If the end date is not provided, it returns only the formatted start date.
     *
     * @param  string  $startDate The start date in 'Y-m-d' format.
     * @param  string|null  $endDate The end date in 'Y-m-d' format or null.
     * @return string The formatted search dates.
     */
    private function prepareSearchDates(string $startDate, string $endDate = null): string
    {
        $searchStartDate = Carbon::createFromFormat('Y-m-d', $startDate)->format('d M Y');
        $searchEndDate = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->format('d M Y') : null;

        return $endDate ? "{$searchStartDate}|{$searchEndDate}" : $searchStartDate;
    }

    /**
     * Fetches exchange rate data based on search dates.
     *
     * @param  string  $searchDates The search dates.
     * @return PromiseInterface|Response The promise interface or response object.
     *
     * @throws Exception
     */
    private function fetchExchangeRatesData(string $searchDates): PromiseInterface|Response
    {
        $nonce = $this->getNonceFromDataTableId($this->getDataTableIdFromHtmlTableId(config('app.boj_currency_exchange_table_id')));

        return Http::asForm()->post("{$this->boj_base_url}/wp-admin/admin-ajax.php?action=get_wdtable&table_id="
            .config('app.boj_currency_exchange_data_table_id'), [
                'draw' => '1',
                'start' => '0',
                'length' => '-1',
                'wdtNonce' => $nonce,
                'sRangeSeparator' => '|',
                'columns[0][data]' => '0',
                'columns[0][searchable]' => 'true',
                'columns[0][orderable]' => 'true',
                'columns[0][search][value]' => $searchDates,
                'columns[0][search][regex]' => 'false',
                'order[0][column]' => '0',
                'order[0][dir]' => 'asc',
                'search[value]' => '',
                'search[regex]' => false,
            ]);
    }

    /**
     * Process response and get exchanges.
     *
     * @param  PromiseInterface|Response  $response The response to process.
     * @return array The list of exchange rates.
     *
     * @throws Exception If unable to get exchange rates from BOJ website.
     */
    private function processResponseAndGetExchanges(PromiseInterface|Response $response): array
    {
        if ($response->failed()) {
            Log::critical('Unable to get exchange rates from BOJ website', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new Exception('Unable to get exchange rates from BOJ website');
        }

        $data = $response->json();
        $rates = Arr::get($data, 'data');
        $exchanges = [];

        foreach ($rates as $rate) {
            $exchange = new ExchangeRate([
                'date' => Arr::get($rate, 0),
                'currency' => $this->convertBOJCurrencyToISOCurrency(Str::of(Arr::get($rate, 1))->trim()),
                'buy_price' => Arr::get($rate, 2),
                'notes' => Arr::get($rate, 3),
                'coins' => Arr::get($rate, 4),
                'sell_price' => Arr::get($rate, 5),
            ]);

            $exchanges[] = $exchange;
        }

        return $exchanges;
    }

    /**
     * Converts a currency code from BOJ currency to ISO currency.
     *
     * @param  string  $bojCurrency The BOJ currency code.
     * @return string The ISO currency code.
     *
     * @throws Exception If the BOJ currency is not found in the currency map.
     */
    public function convertBOJCurrencyToISOCurrency(string $bojCurrency): string
    {
        $currencyMap = config('app.boj_currency_to_iso_currency_map');
        // throw error if the currency is not found in the map
        if (! Arr::has($currencyMap, $bojCurrency)) {
            Log::error("Currency '$bojCurrency' not found in currency map");
            throw new Exception("Currency '$bojCurrency' not found in currency map");
        }
        // return the ISO currency
        return Arr::get($currencyMap, $bojCurrency);
    }

    /**
     * Returns the nonce value from the given data table ID.
     *
     * @param  string  $dataTableId The data table ID.
     * @return string|null The nonce value, or null if not found.
     */
    public function getNonceFromDataTableId(string $dataTableId): ?string
    {
        // This pattern is used to extract nonce value (f77c57c352) from the html hidden input element id (wdtNonceFrontendEdit_134)
        // for the data table id (134)
        // <input type="hidden" id="wdtNonceFrontendEdit_134" name="wdtNonceFrontendEdit_134" value="f77c57c352" />
        $pattern = '/<input[^>]*type="hidden"[^>]*id="([^"]*)"[^>]*value="([^"]*)"[^>]*>/s';

        return Arr::get($this->getGroupedMatches($pattern), "wdtNonceFrontendEdit_$dataTableId");
    }

    /**
     * Retrieves the data table ID from an HTML table ID.
     *
     * @param  string  $htmlTableId The HTML table ID.
     * @return string|null The data table ID.
     */
    public function getDataTableIdFromHtmlTableId(string $htmlTableId): ?string
    {
        // This pattern is used to extract the data table id from the html table element
        // <table id="table_2" class="wpDataTable" data-wpdatatable_id="134">
        $pattern = '/<table[^>]*id="([^"]*)"[^>]*data-wpdatatable_id="([^"]*)"[^>]*>/s';

        return Arr::get($this->getGroupedMatches($pattern), $htmlTableId);
    }

    /**
     * Get the counter rate page.
     *
     * @return string The HTML body of the counter rate page.
     *
     * @throws Exception If failed to get the counter rates.
     */
    private function getCounterRatePage(): string
    {
        // Do an HTTP request to the foreign exchange html page
        $response = Http::get("{$this->boj_base_url}/market/foreign-exchange/counter-rates");
        if ($response->failed()) {
            throw new Exception('Failed to get the counter rates');
        }

        return $response->body();
    }

    private function getGroupedMatches(string $pattern): array
    {
        $matches = [];
        preg_match_all($pattern, $this->page, $matches, PREG_SET_ORDER);

        return $this->groupMatches($matches);
    }

    /**
     * Group matches.
     *
     * @param  array  $matches The matches to group.
     * @return array The grouped matches.
     */
    private function groupMatches(array $matches): array
    {
        $result = [];
        foreach ($matches as $match) {
            $table_id = $match[1];
            $data_wp_data_table_id = $match[2];
            $result[$table_id] = $data_wp_data_table_id;
        }

        return $result;
    }

    /**
     * Get saved exchange rates from the database based on the start date and optional end date.
     *
     * @param  string|null  $startDate The start date to search for exchange rates (>=).
     * @param  string|null  $endDate The optional end date to search for exchange rates (<=).
     * @return Collection The collection of saved exchange rates.
     */
    private function getSavedExchangeRates(string $startDate = null, string $endDate = null): Collection
    {
        // if no start date use the most recent date in the database
        if (! $startDate) {
            //TODO: Create an index on date column
            $startDate = ExchangeRate::max('date');
        }
        $rates = ExchangeRate::where('date', '>=', $startDate);
        if ($endDate) {
            $rates->where('date', '<=', $endDate);
        }

        return $rates->get();
    }
}
