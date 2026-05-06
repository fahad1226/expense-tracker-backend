<?php

namespace App\Support;

/**
 * ISO 4217 codes allowed for user preference. Keep in sync with frontend.
 */
final class SupportedCurrencies
{
    /**
     * @return list<array{code: string, label: string}>
     */
    public static function options(): array
    {
        return [
            ['code' => 'BDT', 'label' => 'Bangladeshi Taka'],
            ['code' => 'USD', 'label' => 'US Dollar'],
            ['code' => 'EUR', 'label' => 'Euro'],
            ['code' => 'GBP', 'label' => 'British Pound'],
            ['code' => 'INR', 'label' => 'Indian Rupee'],
            ['code' => 'AUD', 'label' => 'Australian Dollar'],
            ['code' => 'CAD', 'label' => 'Canadian Dollar'],
            ['code' => 'JPY', 'label' => 'Japanese Yen'],
            ['code' => 'SGD', 'label' => 'Singapore Dollar'],
            ['code' => 'AED', 'label' => 'UAE Dirham'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_column(self::options(), 'code');
    }
}
