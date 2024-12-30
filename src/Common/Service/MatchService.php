<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Config\MatchOptions;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class MatchService implements MatchServiceInterface
{
    private MatchOptions $options;

    public function __construct(?MatchOptions $options = null)
    {
        $this->options = $options ?? new MatchOptions();
    }

    public function areSurnamesSimilar(string $a, string $b): bool
    {
        $similar = StringUtilities::similarText($a, $b);

        return $similar >= $this->options->minimumSurnameSimilarity;
    }

    public function compare(Donor $a, Donor $b): MatchResult
    {
        $namePercent = self::compareNames($a, $b);
        $localePercent = self::compareLocales($a, $b);

        $occupationPercent = 1.0;

        if ('' !== $a->occupation && '' !== $b->occupation) {
            $occupationPercent = StringUtilities::similarText($a->occupation, $b->occupation);
        }

        $employerPercent = 1.0;

        if ('' !== $a->employer && '' !== $b->employer) {
            $employerPercent = StringUtilities::similarText($a->employer, $b->employer);
        }

        $nameFactor = $this->options->nameFactor;
        $localeFactor = $this->options->localeFactor;

        if ('' === $a->address || '' === $b->address) {
            $localeFactor = MathUtilities::multiply($localeFactor, .75);
            $nameFactor = MathUtilities::add(
                $nameFactor,
                MathUtilities::subtract($localeFactor, $this->options->localeFactor)
            );
        }

        $percent = ($namePercent * $nameFactor)
            + ($localePercent * $localeFactor)
            + ($occupationPercent * $this->options->occupationFactor)
            + ($employerPercent * $this->options->employerFactor);

        $id = $percent >= $this->options->threshold ? $a->id : null;

        return new MatchResult($a, $b, round($percent, 4), $id);
    }

    private static function compareNames(Donor $a, Donor $b): float
    {
        [$firstA, $lastA] = array_values($a->getNormalizedNameParts());
        [$firstB, $lastB] = array_values($b->getNormalizedNameParts());

        $pctFirst = StringUtilities::similarText($firstA, $firstB);
        $pctLast = StringUtilities::similarText($lastA, $lastB);

        return min($pctFirst, $pctLast);
    }

    private static function compareLocales(Donor $a, Donor $b): float
    {
        $localePercent = 0.0;

        $zipA = $a->getParsedZip();
        $zipB = $b->getParsedZip();

        $zip4Mismatch = null !== $zipA['zip4'] && null !== $zipB['zip4'] && $zipA['zip4'] !== $zipB['zip4'];

        if ($zipA['zip5'] === $zipB['zip5']) {
            $localePercent += $zip4Mismatch ? .20 : .40;
        }

        $cityPercent = StringUtilities::similarText($a->city, $b->city);

        $localePercent += ($cityPercent * .35);

        if ('' !== $a->address && '' !== $b->address) {
            $addressPercent = StringUtilities::similarText($a->address, $b->address);
            $localePercent += ($addressPercent * .25);
        } else {
            $localePercent *= (100 / 75);
        }

        return $localePercent;
    }
}
