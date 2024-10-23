<?php

use Illuminate\Support\Facades\App;


if (!function_exists('formatCurrency2')) {

    function formatCurrency2($amount)
    {
        $currency = '';
        $local = App::getLocale();
        $currencyMap = [
            'en' => 'USD',
            'fr' => 'XOF',
        ];
        $currency = $currencyMap[$local] ?? 'XOF';
        $currencies = config('currencies');
        $symbol = $currencies[$currency]['symbol'] ?? '$';
        return $symbol . number_format($amount, 2);
    }
}
