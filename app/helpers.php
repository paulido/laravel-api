<?php


if (!function_exists('formatCurrency')) {

    function formatCurrency($amount, $currency = 'XOF') {
        $locale = app()->getLocale();
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
}
