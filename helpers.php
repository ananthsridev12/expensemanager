<?php

if (!function_exists('formatCurrency')) {
    function formatCurrency($value): string
    {
        $amount = is_numeric($value) ? (float) $value : 0;
        return '₹ ' . number_format($amount, 2);
    }
}
