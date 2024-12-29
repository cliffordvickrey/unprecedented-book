<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Config\MatchOptions;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
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
        $namePct = StringUtilities::similarText($a->name, $b->name);

        $percent = ($namePct / 100.0) * $this->options->nameFactor;

        $localePercent = self::compareLocales($a, $b);

        $percent += ($localePercent * $this->options->localeFactor);

        if ('' !== $a->occupation && '' !== $b->occupation) {
            $occupationPercent = StringUtilities::similarText($a->occupation, $b->occupation);
            $percent += (($occupationPercent / 100.0) * $this->options->occupationFactor);
        } else {
            $percent *= (1 / (1 - $this->options->occupationFactor));
        }

        if ('' !== $a->employer && '' !== $b->employer) {
            $employerPercent = StringUtilities::similarText($a->employer, $b->employer);
            $percent += (($employerPercent / 100) * $this->options->employerFactor);
        } else {
            $percent *= (1 / (1 - $this->options->employerFactor));
        }

        $id = $percent >= $this->options->threshold ? $a->id : null;

        return new MatchResult($a, $b, round($percent, 4), $id);
    }

    private static function compareLocales(Donor $a, Donor $b): float
    {
        $localePercent = 0.0;

        $zipA = StringUtilities::parseZip($a->zip);
        $zipB = StringUtilities::parseZip($b->zip);

        $zip4Mismatch = null !== $zipA['zip4'] && null !== $zipB['zip4'] && $zipA['zip4'] !== $zipB['zip4'];

        if ($zipA['zip5'] === $zipB['zip5']) {
            $localePercent += $zip4Mismatch ? .20 : .40;
        }

        $cityPercent = StringUtilities::similarText($a->city, $b->city);

        $localePercent += (($cityPercent / 100.0) * .35);

        if ('' !== $a->address && '' !== $b->address) {
            $addressPercent = StringUtilities::similarText($a->address, $b->address);
            $localePercent += (($addressPercent / 100) * 0.25);
        } else {
            $localePercent *= (100 / 75);
        }

        return $localePercent;
    }
}
