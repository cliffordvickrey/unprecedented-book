<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Geo;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\Aggregate;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;

class County extends Aggregate
{
    #[PropMeta(1)]
    public string $name = '';
    #[PropMeta(2)]
    public ?\DateTimeImmutable $date = null;
    #[PropMeta(3)]
    public string $state = '';
    #[PropMeta(4)]
    public ?int $fips = null;
    #[PropMeta(5)]
    public int $covid_cases = 0;
    #[PropMeta(6)]
    public int $covid_deaths = 0;
    #[PropMeta(7)]
    public ?float $mask_never = null;
    #[PropMeta(8)]
    public ?float $mask_rarely = null;
    #[PropMeta(9)]
    public ?float $mask_sometimes = null;
    #[PropMeta(10)]
    public ?float $mask_frequently = null;
    #[PropMeta(11)]
    public ?float $mask_always = null;
}
