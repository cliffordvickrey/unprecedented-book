<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCampaignCharacteristicCollectionStrategy;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

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

    /**
     * @return array<string, array<string, string>>
     */
    public static function getDescriptions(?DonorCharacteristic ...$characteristics): array
    {
        $characteristics = array_values(array_filter($characteristics));

        $cases = self::cases();

        if (0 !== \count($characteristics)) {
            $cases = array_filter($cases, static fn (DonorCharacteristic $case) => !$case->isMutuallyExclusiveOrTautologicalWith(
                ...$characteristics
            ));
        }

        $genres = DonorCharacteristicGenre::cases();
        usort(
            $genres,
            static fn (DonorCharacteristicGenre $a, DonorCharacteristicGenre $b) => $a->getOrder() <=> $b->getOrder()
        );

        $optionGroups = [];

        foreach ($genres as $genre) {
            $optionGroups[$genre->getDescription()] = [];
        }

        return array_reduce($cases, static function (array $carry, DonorCharacteristic $case): array {
            $genre = $case->getGenre()->getDescription();

            /** @var array<string, array<string, string>> $carry */
            if (!isset($carry[$genre])) {
                $carry[$genre] = [];
            }

            $carry[$genre][$case->value] = $case->getDescription();

            return $carry;
        }, $optionGroups);
    }

    public function isMutuallyExclusiveOrTautologicalWith(
        DonorCharacteristic|CampaignType|null ...$characteristics,
    ): bool {
        $characteristics = array_filter($characteristics);

        foreach ($characteristics as $characteristic) {
            if (self::areMutuallyExclusiveOrTautological($this, $characteristic)) {
                return true;
            }
        }

        return false;
    }

    public static function areMutuallyExclusiveOrTautological(
        DonorCharacteristic $characteristicA,
        DonorCharacteristic|CampaignType $characteristicB,
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
            self::cycle_2024_biden->value => [
                CampaignType::joe_biden->value => true,
            ],
            self::cycle_2024_harris->value => [
                CampaignType::kamala_harris->value => true,
            ],
            self::cycle_2020_biden->value => [self::cycle_2020_non_biden->value => true],
            self::cycle_2020_non_biden->value => [self::cycle_2020_biden->value => true],
            self::cycle_2024_trump->value => [
                self::cycle_2024_non_trump->value => true,
                CampaignType::donald_trump->value => true,
            ],
            self::cycle_2024_non_trump->value => [
                self::cycle_2024_trump->value => true,
                CampaignType::donald_trump->value => true,
            ],
            self::day_one_launch->value => [self::week_one_launch->value => true],
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
            self::cycle_2020_progressive => 'Progressive Donors',
            self::cycle_2020_non_biden => 'Non Biden Donors',
            self::cycle_2024_harris => 'Harris Donors',
            self::cycle_2024_desantis => 'DeSantis Donors',
            self::cycle_2024_haley => 'Haley Donors',
            self::cycle_2024_rfk_jr => 'RFK Jr. Donors',
            self::cycle_2024_non_trump => 'Non-Trump Donors',
        };
    }

    public function getGenre(): DonorCharacteristicGenre
    {
        return DonorCharacteristicGenre::fromCharacteristic($this);
    }

    public function getBlurb(CampaignType $campaignType): string
    {
        $params = [
            'campaign' => $campaignType->getDescription(),
            'coda' => CampaignType::kamala_harris === $campaignType
                ? " (includes recurring donations inherited from Biden's candidacy)"
                : '',
            'launchDate' => $campaignType->getLaunchDate()->format('Y-m-d'),
            'cycle' => (string) $this->getCycle(),
            'months' => (string) DonorProfileCampaignCharacteristicCollectionStrategy::DEFAULT_MONTHLY_THRESHOLD,
            'weeks' => (string) DonorProfileCampaignCharacteristicCollectionStrategy::DEFAULT_WEEKLY_THRESHOLD,
        ];

        $blurb = match ($this) {
            self::amt_up_to_1 => 'Donors who gave a total of <= $1 to %campaign% for the 2024 election cycle',
            self::amt_up_to_200 => 'Donors who gave a total of between $1.01 to $199.99 to %campaign% for the 2024 election cycle',
            self::amt_up_to_1000 => 'Donors who gave a total of between $200 to $999.99 to %campaign% for the 2024 election cycle',
            self::amt_up_to_2800 => 'Donors who gave a total of between 1,000 to $2,799.99 to %campaign% for the 2024 election cycle',
            self::amt_more_than_2800 => 'Donors who gave a total of at least $2,800.00 to %campaign% for the 2024 election cycle',
            self::prior => 'Donors who had contributed to a campaign or leadership PAC belonging to %campaign% prior to the 2024 election cycle',
            self::day_one_launch => 'Donors who contributed to %campaign% on %launchDate%',
            self::week_one_launch => 'Donors who contributed to %campaign% within a week after %launchDate%',
            self::weekly => 'Donors who contributed to %campaign% for %weeks% or more consecutive weeks%coda%',
            self::monthly => 'Donors who contributed to %campaign% for %months% or more consecutive months%coda%',
            self::cycle_2016_clinton => 'Donors who contributed to Hillary Clinton in the %cycle% election cycle',
            self::cycle_2016_sanders => 'Donors who contributed to Bernie Sanders in the %cycle% election cycle',
            self::cycle_2016_trump, self::cycle_2020_trump, self::cycle_2024_trump => 'Donors who contributed to Donald Trump in the %cycle% election cycle',
            self::cycle_2016_dem_pres => 'Donors who contributed to a Democratic presidential campaign in the %cycle% election cycle',
            self::cycle_2016_gop_pres => 'Donors who contributed to a Republican presidential campaign in the %cycle% election cycle',
            self::cycle_2016_dem_non_pres, self::cycle_2020_dem_non_pres, self::cycle_2024_dem_non_pres => 'Donors who contributed to a Democratic House or Senate campaign in the %cycle% election cycle',
            self::cycle_2016_gop_non_pres, self::cycle_2020_gop_non_pres, self::cycle_2024_gop_non_pres => 'Donors who contributed to a Republican House or Senate campaign in the %cycle% election cycle',
            self::cycle_2016_party_elite, self::cycle_2020_party_elite, self::cycle_2024_party_elite => 'Donors who contributed to a party committee (FEC committee types X, Y, and Z) in the %cycle% election cycle',
            self::cycle_2016_super_pac, self::cycle_2020_super_pac, self::cycle_2024_super_pac => 'Donors who contributed to a super PAC (independent expenditure committee; FEC committee type O) in the %cycle% election cycle',
            self::cycle_2020_biden, self::cycle_2024_biden => 'Donors who contributed to Joe Biden in the %cycle% election cycle',
            self::cycle_2020_progressive => 'Donors who contributed to either Bernie Sanders or Elizabeth Warren in the %cycle% election cycle',
            self::cycle_2020_non_biden => 'Donors who did NOT contribute to Joe Biden in the %cycle% election cycle',
            self::cycle_2024_harris => 'Donors to contributed to Kamala Harris in the %cycle% election cycle',
            self::cycle_2024_desantis => 'Donors who contributed to Ron DeSantis in the %cycle% election cycle',
            self::cycle_2024_haley => 'Donors who contributed to Nikki Haley in the %cycle% election cycle',
            self::cycle_2024_rfk_jr => 'Donors who contributed to RFK Jr. in the %cycle% election cycle',
            self::cycle_2024_non_trump => 'Donors who did NOT contribute to Donald Trump in the %cycle% election cycle',
        };

        $toReplace = array_map(static fn (string $str) => "%$str%", array_keys($params));

        return str_replace($toReplace, array_values($params), $blurb);
    }

    public function getCycle(): ?int
    {
        if (preg_match('/^cycle_(\d{4})_/', $this->value, $matches)) {
            $cycle = $matches[1];

            return CastingUtilities::toInt($cycle);
        }

        return null;
    }
}
