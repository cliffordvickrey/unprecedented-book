<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum DonorCharacteristic: string
{
    case amt_up_to_1 = 'amt_up_to_1';
    case amt_up_to_200 = 'amt_up_to_200';
    case amt_up_to_1000 = 'amt_up_to_1000';
    case amt_up_to_2800 = 'amt_up_to_2800';
    case amt_more_than_2800 = 'amt_more_than_2800';
    case prior = 'prior';
    case day_one_launch = 'day_one_launch';
    case week_one_launch = 'week_one_launch';
    case weekly = 'weekly';
    case monthly = 'monthly';
    case cycle_2016_clinton = 'cycle_2016_clinton';
    case cycle_2016_sanders = 'cycle_2016_sanders';
    case cycle_2016_trump = 'cycle_2016_trump';
    case cycle_2016_dem_pres = 'cycle_2016_dem_pres';
    case cycle_2016_gop_pres = 'cycle_2016_gop_pres';
    case cycle_2016_dem_non_pres = 'cycle_2016_dem_non_pres';
    case cycle_2016_gop_non_pres = 'cycle_2016_gop_non_pres';
    case cycle_2016_party_elite = 'cycle_2016_party_elite';
    case cycle_2016_super_pac = 'cycle_2016_super_pac';
    case cycle_2020_trump = 'cycle_2020_trump';
    case cycle_2020_biden = 'cycle_2020_biden';
    case cycle_2020_progressive = 'cycle_2020_progressive';
    case cycle_2020_non_biden = 'cycle_2020_non_biden';
    case cycle_2020_dem_non_pres = 'cycle_2020_dem_non_pres';
    case cycle_2020_gop_non_pres = 'cycle_2020_gop_non_pres';
    case cycle_2020_party_elite = 'cycle_2020_party_elite';
    case cycle_2020_super_pac = 'cycle_2020_super_pac';
    case cycle_2024_biden = 'cycle_2024_biden';
    case cycle_2024_harris = 'cycle_2024_harris';
    case cycle_2024_trump = 'cycle_2024_trump';
    case cycle_2024_desantis = 'cycle_2024_desantis';
    case cycle_2024_haley = 'cycle_2024_haley';
    case cycle_2024_rfk_jr = 'cycle_2024_rfk_jr';
    case cycle_2024_non_trump = 'cycle_2024_non_trump';
    case cycle_2024_dem_non_pres = 'cycle_2024_dem_non_pres';
    case cycle_2024_gop_non_pres = 'cycle_2024_gop_non_pres';
    case cycle_2024_party_elite = 'cycle_2024_party_elite';
    case cycle_2024_super_pac = 'cycle_2024_super_pac';

    public static function areMutuallyExclusive(
        DonorCharacteristic $characteristicA,
        DonorCharacteristic $characteristicB,
    ): bool {
        /** @phpstan-var array<string, list<DonorCharacteristic>>|null $mutuallyExclusive */
        static $mutuallyExclusive = null;

        if ($characteristicA === $characteristicB) {
            return true;
        }

        if (null == $mutuallyExclusive) {
            $mutuallyExclusive = self::mutuallyExclusiveCharacteristics();
        }

        return isset($mutuallyExclusive[$characteristicA->value][$characteristicB->value]);
    }

    /**
     * @return array<string, array<string, true>>
     */
    private static function mutuallyExclusiveCharacteristics(): array
    {
        $cases = self::cases();

        $mutuallyExclusive = [
            self::cycle_2020_biden->value => [self::cycle_2020_non_biden->value => true],
            self::cycle_2020_non_biden->value => [self::cycle_2020_biden->value => true],
            self::cycle_2024_trump->value => [self::cycle_2024_non_trump->value => true],
            self::cycle_2024_non_trump->value => [self::cycle_2024_trump->value => true],
        ];

        foreach ($cases as $case) {
            if (str_starts_with($case->value, 'amt')) {
                $mutuallyExclusive[$case->value] = array_filter([
                    self::amt_up_to_1->value => true,
                    self::amt_up_to_200->value => true,
                    self::amt_up_to_1000->value => true,
                    self::amt_up_to_2800->value => true,
                    self::amt_more_than_2800->value => true,
                ], static fn ($key) => $key !== $case->value, \ARRAY_FILTER_USE_KEY);
            }
        }

        return $mutuallyExclusive;
    }

    public function getGenre(): DonorCharacteristicGenre
    {
        return DonorCharacteristicGenre::fromCharacteristic($this);
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::amt_up_to_1 => '$1 Donors',
            self::amt_up_to_200 => '$1.01-$199.99 Donors',
            self::amt_up_to_1000 => '$200-$999.999 Donors',
            self::amt_up_to_2800 => '$1000-$2799.99 Donors',
            self::amt_more_than_2800 => '$2800 Donors',
            self::prior => 'Prior Donors',
            self::day_one_launch => 'Day One Launch Donors',
            self::week_one_launch => 'Week One Launch Donors',
            self::weekly => 'Weekly Donors',
            self::monthly => 'Monthly Donors',
            self::cycle_2016_clinton => 'Clinton Donors',
            self::cycle_2016_sanders => 'Sanders Donors',
            self::cycle_2016_trump, self::cycle_2020_trump, self::cycle_2024_trump => 'Trump Donors',
            self::cycle_2016_dem_pres => 'Democratic Presidential Candidate Donors',
            self::cycle_2016_gop_pres => 'Republican Presidential Candidate Donors',
            self::cycle_2016_dem_non_pres, self::cycle_2020_dem_non_pres, self::cycle_2024_dem_non_pres => 'Democratic House/Senate Donors',
            self::cycle_2016_gop_non_pres, self::cycle_2020_gop_non_pres, self::cycle_2024_gop_non_pres => 'Republican House/Senate Donors',
            self::cycle_2016_party_elite, self::cycle_2020_party_elite, self::cycle_2024_party_elite => 'Party Committee Donors',
            self::cycle_2016_super_pac, self::cycle_2020_super_pac, self::cycle_2024_super_pac => 'Super PAC Donors',
            self::cycle_2020_biden, self::cycle_2024_biden => 'Biden Donors',
            self::cycle_2020_progressive => 'Progress Donors',
            self::cycle_2020_non_biden => 'Non Biden Donors',
            self::cycle_2024_harris => 'Harris Donors',
            self::cycle_2024_desantis => 'DeSantis Donors',
            self::cycle_2024_haley => 'Haley Donors',
            self::cycle_2024_rfk_jr => 'RFK Jr. Donors',
            self::cycle_2024_non_trump => 'Non-Trump Donors',
        };
    }
}
