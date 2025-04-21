<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Geo\County;

class CountyReportRow extends Entity
{
    use ProfileReportTrait;

    public string $county_id = '';
    public string $county_name = '';
    public string $county_state = '';
    public ?int $county_fips = null;
    public int $covid_cases = 0;
    public int $covid_deaths = 0;
    public ?float $mask_never = null;
    public ?float $mask_rarely = null;
    public ?float $mask_sometimes = null;
    public ?float $mask_frequently = null;
    public ?float $mask_always = null;
    public float $biden_amt = 0.0;
    public int $biden_donors = 0;
    public int $biden_receipts = 0;
    public float $harris_amt = 0.0;
    public int $harris_donors = 0;
    public int $harris_receipts = 0;
    public float $trump_amt = 0.0;
    public int $trump_donors = 0;
    public int $trump_receipts = 0;

    public static function fromCounty(County $county): self
    {
        $self = new self();
        $self->county_id = $county->slug;
        $self->county_name = $county->name;
        $self->county_state = $county->state;
        $self->county_fips = $county->fips;
        $self->covid_cases = $county->covid_cases;
        $self->covid_deaths = $county->covid_deaths;
        $self->mask_never = $county->mask_never;
        $self->mask_rarely = $county->mask_rarely;
        $self->mask_sometimes = $county->mask_sometimes;
        $self->mask_frequently = $county->mask_frequently;
        $self->mask_always = $county->mask_always;

        return $self;
    }
}
