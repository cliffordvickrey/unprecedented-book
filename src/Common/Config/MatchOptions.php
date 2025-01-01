<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Config;

final readonly class MatchOptions
{
    public MatchLocaleOptions $localeOptions;

    public function __construct(
        /**
         * Minimum % name surname similarity for contributors to be a match.
         *
         * @var float
         */
        public float $minimumSurnameSimilarity = 0.70,
        /**
         * Minimum % name similarity for contributors to be a match.
         *
         * @var float
         */
        public float $minimumNameSimilarity = 0.80,
        /**
         * Factor by which to multiply name similarity.
         *
         * @var float
         */
        public float $nameFactor = 0.50,
        /**
         * Factor by which to multiply locale similarity.
         *
         * @var float
         */
        public float $localeFactor = 0.40,
        /**
         * Factor by which to multiply occupation similarity.
         *
         * @var float
         */
        public float $occupationFactor = 0.05,
        /**
         * Factor by which to multiply occupation similarity.
         *
         * @var float
         */
        public float $employerFactor = 0.05,
        /**
         * Minimum score to be considered a match.
         *
         * @var float
         */
        public float $threshold = .70,
        ?MatchLocaleOptions $localeOptions = null,
    ) {
        $this->localeOptions = $localeOptions ?? new MatchLocaleOptions();
    }
}
