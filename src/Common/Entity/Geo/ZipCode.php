<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Geo;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\Aggregate;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

class ZipCode extends Aggregate
{
    #[PropMeta(1)]
    public string $zipcode = '';
    #[PropMeta(2)]
    public ZipCodeType $zipcode_type = ZipCodeType::Standard;
    #[PropMeta(3)]
    public string $major_city = '';
    #[PropMeta(4)]
    public ?string $post_office_city = null;
    #[PropMeta(5)]
    public ?string $county = null;
    #[PropMeta(6)]
    public string $state = '';
    #[PropMeta(7)]
    public ?float $lat = null;
    #[PropMeta(8)]
    public ?float $lng = null;
    #[PropMeta(9)]
    public ?TimeZone $timezone = null;
    #[PropMeta(10)]
    public ?float $radius_in_miles = null;
    #[PropMeta(11)]
    public ?int $population = null;
    #[PropMeta(12)]
    public ?int $population_density = null;
    #[PropMeta(13)]
    public ?float $land_area_in_sqmi = null;
    #[PropMeta(14)]
    public ?float $water_area_in_sqmi = null;
    #[PropMeta(15)]
    public ?int $housing_units = null;
    #[PropMeta(16)]
    public ?int $occupied_housing_units = null;
    #[PropMeta(17)]
    public ?int $median_home_value = null;
    #[PropMeta(18)]
    public ?int $median_household_income = null;
    #[PropMeta(19)]
    public ?float $bounds_west = null;
    #[PropMeta(20)]
    public ?float $bounds_east = null;
    #[PropMeta(21)]
    public ?float $bounds_north = null;
    #[PropMeta(22)]
    public ?float $bounds_south = null;

    public static function slugifyZip(mixed $zip): string
    {
        $zip = self::normalizeZip($zip);

        $firstDigit = (int) CastingUtilities::toInt(substr($zip, 0, 1));

        return \chr(\ord('a') + $firstDigit).$zip;
    }

    public static function normalizeZip(mixed $zip): string
    {
        if (!\is_string($zip)) {
            $zip = (string) CastingUtilities::toString($zip);
        }

        if (\strlen($zip) < 5) {
            $zip = str_pad($zip, 5, '0');
        }

        return $zip;
    }
}
