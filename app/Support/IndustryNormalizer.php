<?php

namespace App\Support;

class IndustryNormalizer
{
    public static function normalize(?string $industry): string
    {
        return match (strtolower(trim((string) $industry))) {
            'restaurant' => 'restaurant',
            'cafe' => 'cafe',
            'bakery' => 'bakery',
            'retail' => 'retail',
            'salon', 'other', 'service', 'services' => 'services',
            default => 'services',
        };
    }

    public static function isSimpleBilling(?string $industry): bool
    {
        return self::normalize($industry) === 'services';
    }

    public static function productIndustries(): array
    {
        return [
            'restaurant',
            'bakery',
            'cafe',
            'retail',
            'salon',
            'other',
            'service',
            'services',
        ];
    }
}
