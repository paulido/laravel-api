<?php

use Illuminate\Support\Facades\App;

function formatCurrency($amount)
{
    $currency = '';
    $local = App::getLocale();
    switch ($local) {
        case 'en':
            $currency = 'USD';
            break;
        case 'fr':
            $currency = 'XOF';
            break;
        default:
            $currency = 'XOF';
    }

    $currencies = config('currencies');
    $symbol = $currencies[$currency]['symbol'] ?? '$';

    return $symbol . number_format($amount, 2);
}
