<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BOJCurrencyExchangeRateService
{
    private string $boj_base_url;

    private string $page;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->boj_base_url = config('app.boj_base_url');
        $this->page = $this->getCounterRatePage();
    }

    /**
     * @throws \Exception
     */
    public function getNonceFromDataTableId(string $dataTableId): ?string
    {
        $pattern = '/<input[^>]*type="hidden"[^>]*id="([^"]*)"[^>]*value="([^"]*)"[^>]*>/s';

        return Arr::get($this->getGroupedMatches($pattern), "wdtNonceFrontendEdit_$dataTableId");
    }

    /**
     * @throws \Exception
     */
    public function getDataTableIdFromHtmlTableId(string $htmlTableId): ?string
    {
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
     * @throws \Exception
     */
    private function getCounterRatePage(): string
    {
        // Do an HTTP request to the foreign exchange html page
        $response = Http::get("{$this->boj_base_url}/market/foreign-exchange/counter-rates");
        if ($response->failed()) {
            throw new \Exception('Failed to get the counter rates');
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
