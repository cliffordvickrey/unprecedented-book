<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Geo\County;
use CliffordVickrey\Book2024\Common\Entity\Geo\ZipCode;
use CliffordVickrey\Book2024\Common\Entity\Geo\ZipCodeType;

class ZipReportRow extends Entity
{
    use ProfileReportTrait;

    public string $zip = '';
    public string $city = '';
    public string $state = '';
    public ?string $county_id = null;
    public ?string $county_name = null;
    public ?int $county_fips = null;
    public ZipCodeType $zip_type = ZipCodeType::Standard;
    public ?int $population = null;
    public ?int $population_density = null;
    public ?float $land_area_in_sqmi = null;
    public ?float $water_area_in_sqmi = null;
    public ?int $housing_units = null;
    public ?int $occupied_housing_units = null;
    public ?int $median_home_value = null;
    public ?int $median_household_income = null;
    public float $biden_amt = 0.0;
    public int $biden_donors = 0;
    public int $biden_receipts = 0;
    public float $harris_amt = 0.0;
    public int $harris_donors = 0;
    public int $harris_receipts = 0;
    public float $trump_amt = 0.0;
    public int $trump_donors = 0;
    public int $trump_receipts = 0;

    public static function fromZipCodeAndCounty(ZipCode $zipCode, ?County $county = null): self
    {
        $self = new self();
        $self->zip = $zipCode->zipcode;
        $self->city = $zipCode->major_city;
        $self->state = $zipCode->state;
        $self->county_id = $county?->slug;
        $self->county_name = $county?->name;
        $self->county_fips = $county?->fips;
        $self->zip_type = $zipCode->zipcode_type;
        $self->population = $zipCode->population;
        $self->population_density = $zipCode->population_density;
        $self->land_area_in_sqmi = $zipCode->land_area_in_sqmi;
        $self->water_area_in_sqmi = $zipCode->water_area_in_sqmi;
        $self->housing_units = $zipCode->housing_units;
        $self->occupied_housing_units = $zipCode->occupied_housing_units;
        $self->median_home_value = $zipCode->median_home_value;
        $self->median_household_income = $zipCode->median_household_income;

        return $self;
    }
}
