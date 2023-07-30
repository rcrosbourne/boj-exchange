<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BOJCurrencyExchangeRateService
{
    private string $boj_base_url;

    private string $page;

    /**
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
    public function getExchangeRates(string $startDate, string $endDate = null): array
    {
        // Convert date into a format that the BOJ website expects using Carbon
        $searchStartDate = Carbon::createFromFormat('Y-m-d', $startDate)->format('d M Y');
        $searchEndDate = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->format('d M Y') : null;
        // If we have an end date, we need to search for a range of dates.
        $searchString = $endDate ? "{$searchStartDate}|{$searchEndDate}" : $searchStartDate;
        $nonce = $this->getNonceFromDataTableId($this->getDataTableIdFromHtmlTableId(config('app.boj_currency_exchange_table_id')));
        $response = Http::asForm()->post("{$this->boj_base_url}/wp-admin/admin-ajax.php?action=get_wdtable&table_id=".config('app.boj_currency_exchange_data_table_id'), [
            'draw' => '1',
            'start' => '0',
            'length' => '-1',
            'wdtNonce' => $nonce,
            'sRangeSeparator' => '|',
            'columns[0][data]' => '0',
            'columns[0][searchable]' => 'true',
            'columns[0][orderable]' => 'true',
            'columns[0][search][value]' => $searchString,
            'columns[0][search][regex]' => 'false',
            'order[0][column]' => '0',
            'order[0][dir]' => 'asc',
            'search[value]' => '',
            'search[regex]' => false,
        ]);

        // if the response is not successful, throw an exception
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
        // Create a new Exchange object
        foreach ($rates as $rate) {
            $exchange = new ExchangeRate([
                'date' => Arr::get($rate, 0),
                'currency' => Arr::get($rate, 1),
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
     * @throws Exception
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
     * @throws Exception
     */
    public function getDataTableIdFromHtmlTableId(string $htmlTableId): ?string
    {
        // This pattern is used to extract the data table id from the html table element
        // <table id="table_2" class="wpDataTable" data-wpdatatable_id="134">
        $pattern = '/<table[^>]*id="([^"]*)"[^>]*data-wpdatatable_id="([^"]*)"[^>]*>/s';

        return Arr::get($this->getGroupedMatches($pattern), $htmlTableId);
    }

    private function groupMatches($matches): array
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
     * @throws Exception
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
}
