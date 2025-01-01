<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Config;

final readonly class MatchLocaleOptions
{
    public function __construct(
        public float $zip5Factor = 0.20,
        public float $zip4Factor = 0.20,
        public float $cityFactor = 0.35,
        public float $addressFactor = 0.25,
    ) {
    }
}
