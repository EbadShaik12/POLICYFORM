<?php

namespace Tests\Unit;

use App\Services\PremiumCalculator;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PremiumCalculatorTest extends TestCase
{
    /**
     * Test base pricing for domestic, standard age (18-60), coverage 100k, 1 day.
     * BASIC = 12 * 1 day * 1.00 (age) * 1.00 (dest) * 1.00 (cov) = 12.00
     */
    public function test_basic_domestic_standard_adult(): void
    {
        $res = PremiumCalculator::calculate(
            planCode: 'BASIC',
            dateOfBirth: '1995-01-01',
            tripStart: '2026-11-01',
            tripEnd: '2026-11-01',
            destination: 'DOMESTIC',
            sumInsured: 100000
        );

        $this->assertEquals(12.00, $res['premium']);
        $this->assertEquals(1, $res['days']);
        $this->assertEquals(1.00, $res['age_multiplier']);
        $this->assertEquals(1.00, $res['destination_multiplier']);
        $this->assertEquals(1.00, $res['coverage_multiplier']);
    }

    /**
     * Test youth age multiplier (0-17: 1.10).
     * 16 years old: PLUS plan (20), 5 days, Domestic (1.00), 100k (1.00).
     * 20 * 5 * 1.10 * 1.00 * 1.00 = 110.00
     */
    public function test_youth_age_multiplier(): void
    {
        $res = PremiumCalculator::calculate(
            planCode: 'PLUS',
            dateOfBirth: '2010-05-01',
            tripStart: '2026-11-01', // age 16
            tripEnd: '2026-11-05',   // 5 days
            destination: 'DOMESTIC',
            sumInsured: 100000
        );

        $this->assertEquals(110.00, $res['premium']);
        $this->assertEquals(5, $res['days']);
        $this->assertEquals(1.10, $res['age_multiplier']);
    }

    /**
     * Test senior multipliers (61-70: 1.50 and 71+: 2.00).
     */
    public function test_senior_and_elderly_multipliers(): void
    {
        // 65 years old
        $res65 = PremiumCalculator::calculate(
            planCode: 'BASIC',
            dateOfBirth: '1961-01-01',
            tripStart: '2026-11-01',
            tripEnd: '2026-11-01',
            destination: 'DOMESTIC',
            sumInsured: 100000
        );
        $this->assertEquals(1.50, $res65['age_multiplier']);
        $this->assertEquals(18.00, $res65['premium']); // 12 * 1 * 1.50 = 18

        // 75 years old
        $res75 = PremiumCalculator::calculate(
            planCode: 'BASIC',
            dateOfBirth: '1951-01-01',
            tripStart: '2026-11-01',
            tripEnd: '2026-11-01',
            destination: 'DOMESTIC',
            sumInsured: 100000
        );
        $this->assertEquals(2.00, $res75['age_multiplier']);
        $this->assertEquals(24.00, $res75['premium']); // 12 * 1 * 2.00 = 24
    }

    /**
     * Test international destination multiplier (1.80) and coverage 500k (1.50).
     * PREMIUM plan (30), 10 days, age 30 (1.00), INTERNATIONAL (1.80), 500000 (1.50).
     * 30 * 10 * 1.00 * 1.80 * 1.50 = 810.00
     */
    public function test_international_premium_plan_max_coverage(): void
    {
        $res = PremiumCalculator::calculate(
            planCode: 'PREMIUM',
            dateOfBirth: '1996-01-01',
            tripStart: '2026-12-01',
            tripEnd: '2026-12-10', // 10 days
            destination: 'INTERNATIONAL',
            sumInsured: 500000
        );

        $this->assertEquals(810.00, $res['premium']);
        $this->assertEquals(10, $res['days']);
        $this->assertEquals(1.80, $res['destination_multiplier']);
        $this->assertEquals(1.50, $res['coverage_multiplier']);
    }

    /**
     * Test coverage 300k (1.25 multiplier).
     */
    public function test_coverage_300k_multiplier(): void
    {
        $res = PremiumCalculator::calculate(
            planCode: 'BASIC',
            dateOfBirth: '1990-01-01',
            tripStart: '2026-11-01',
            tripEnd: '2026-11-01',
            destination: 'DOMESTIC',
            sumInsured: 300000
        );

        $this->assertEquals(1.25, $res['coverage_multiplier']);
        $this->assertEquals(15.00, $res['premium']); // 12 * 1 * 1.25 = 15.00
    }

    /**
     * Test invalid plan throws InvalidArgumentException.
     */
    public function test_invalid_plan_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PremiumCalculator::calculate(
            planCode: 'ULTRA',
            dateOfBirth: '1990-01-01',
            tripStart: '2026-11-01',
            tripEnd: '2026-11-02',
            destination: 'DOMESTIC',
            sumInsured: 100000
        );
    }
}
