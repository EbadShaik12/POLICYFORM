<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class PremiumCalculator
{
    public const RATES = [
        'BASIC' => 12,
        'PLUS' => 20,
        'PREMIUM' => 30,
    ];

    public const COVERAGE_MULTIPLIERS = [
        100000 => 100, // 1.00
        300000 => 125, // 1.25
        500000 => 150, // 1.50
    ];

    /**
     * Calculate premium and return detailed breakdown.
     *
     * @return array{
     *     premium: float,
     *     days: int,
     *     age: int,
     *     base_rate: int,
     *     age_multiplier: float,
     *     destination_multiplier: float,
     *     coverage_multiplier: float
     * }
     */
    public static function calculate(
        string $planCode,
        string|Carbon $dateOfBirth,
        string|Carbon $tripStart,
        string|Carbon $tripEnd,
        string $destination,
        int $sumInsured
    ): array {
        $planCode = strtoupper(trim($planCode));
        if (!isset(self::RATES[$planCode])) {
            throw new InvalidArgumentException("Invalid plan code: {$planCode}");
        }

        $dob = $dateOfBirth instanceof Carbon ? $dateOfBirth : Carbon::parse($dateOfBirth);
        $start = $tripStart instanceof Carbon ? $tripStart : Carbon::parse($tripStart);
        $end = $tripEnd instanceof Carbon ? $tripEnd : Carbon::parse($tripEnd);

        if ($end->lt($start)) {
            throw new InvalidArgumentException('Trip end date cannot be earlier than trip start date.');
        }

        // Age at trip start
        $age = $dob->diffInYears($start);

        $ageMultiplierInt = match (true) {
            $age <= 17 => 110,
            $age <= 60 => 100,
            $age <= 70 => 150,
            default => 200,
        };

        $destNormalized = strtoupper(trim($destination));
        $destinationMultiplierInt = match ($destNormalized) {
            'DOMESTIC' => 100,
            'INTERNATIONAL' => 180,
            default => throw new InvalidArgumentException("Invalid destination: {$destination}"),
        };

        if (!isset(self::COVERAGE_MULTIPLIERS[$sumInsured])) {
            throw new InvalidArgumentException("Invalid sum insured: {$sumInsured}");
        }
        $coverageMultiplierInt = self::COVERAGE_MULTIPLIERS[$sumInsured];

        $days = max(1, $start->diffInDays($end) + 1);

        // Safe integer arithmetic:
        // Base rate * days * (ageMult/100) * (destMult/100) * (covMult/100)
        // Multiplied by 100 to get cents:
        // numerator = base * days * ageInt * destInt * covInt
        // divisor = 100 * 100 * 100 / 100 = 10,000
        $numerator = self::RATES[$planCode]
            * $days
            * $ageMultiplierInt
            * $destinationMultiplierInt
            * $coverageMultiplierInt;

        $premiumCents = (int) round($numerator / 10000);
        $premium = $premiumCents / 100;

        return [
            'premium' => $premium,
            'days' => $days,
            'age' => $age,
            'base_rate' => self::RATES[$planCode],
            'age_multiplier' => $ageMultiplierInt / 100,
            'destination_multiplier' => $destinationMultiplierInt / 100,
            'coverage_multiplier' => $coverageMultiplierInt / 100,
        ];
    }
}
