<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Config\MatchOptions;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

/**
 * @phpstan-type NameParts array{0: string, 1: string}
 *
 * @phpstan-import-type ZipCode from StringUtilities
 */
class MatchService implements MatchServiceInterface
{
    public const int GC_COUNT = 100000;
    public const int MAX_MEMORY = 1073741824;

    private MatchOptions $options;
    /** @var array<string, float> */
    private array $similarTextMemo = [];
    /** @var \WeakMap<Donor, NameParts> */
    private \WeakMap $nameParts;
    /** @var \WeakMap<Donor, ZipCode> */
    private \WeakMap $zipCodes;
    /** @var int<0, max> */
    private int $gcCounter = 0;

    public function __construct(
        ?MatchOptions $options = null,
        private readonly int $gcCount = self::GC_COUNT,
        private readonly int $maxMemory = self::MAX_MEMORY,
    ) {
        $this->options = $options ?? new MatchOptions();
        $this->nameParts = new \WeakMap();
        $this->zipCodes = new \WeakMap();
    }

    public function areSurnamesSimilar(string $a, string $b): bool
    {
        if (++$this->gcCounter > $this->gcCount) {
            $this->gc();
        }

        $similar = $this->similarText($a, $b);

        return $similar >= $this->options->minimumSurnameSimilarity;
    }

    private function gc(): void
    {
        $this->gcCounter = 0;

        if (memory_get_usage() > $this->maxMemory) {
            $this->similarTextMemo = [];
        }
    }

    private function similarText(string $a, string $b): float
    {
        $key = "$a|$b";

        if (isset($this->similarTextMemo[$key])) {
            return $this->similarTextMemo[$key];
        }

        $similarText = StringUtilities::similarText($a, $b);

        $this->similarTextMemo[$key] = $similarText;
        $this->similarTextMemo["$b|$a"] = $similarText;

        return $similarText;
    }

    public function compare(Donor $a, Donor $b): MatchResult
    {
        if (++$this->gcCounter > $this->gcCount) {
            $this->gc();
        }

        $namePercent = $this->compareNames($a, $b);
        $localePercent = $this->compareLocales($a, $b);

        $occupationPercent = 1.0;

        if ('' !== $a->occupation && '' !== $b->occupation) {
            $occupationPercent = $this->similarText($a->occupation, $b->occupation);
        }

        $employerPercent = 1.0;

        if ('' !== $a->employer && '' !== $b->employer) {
            $employerPercent = $this->similarText($a->employer, $b->employer);
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

    private function compareNames(Donor $a, Donor $b): float
    {
        [$firstA, $lastA] = $this->getNormalizedNameParts($a);
        [$firstB, $lastB] = $this->getNormalizedNameParts($b);

        $pctFirst = $this->similarText($firstA, $firstB);
        $pctLast = $this->similarText($lastA, $lastB);

        return min($pctFirst, $pctLast);
    }

    /**
     * @return NameParts
     */
    private function getNormalizedNameParts(Donor $donor): array
    {
        if (!isset($this->nameParts[$donor])) {
            $this->nameParts[$donor] = array_values($donor->getNormalizedNameParts());
        }

        return $this->nameParts[$donor];
    }

    private function compareLocales(Donor $a, Donor $b): float
    {
        $localePercent = 0.0;

        $zipA = $this->getParsedZip($a);
        $zipB = $this->getParsedZip($b);

        $zip4Mismatch = null !== $zipA['zip4'] && null !== $zipB['zip4'] && $zipA['zip4'] !== $zipB['zip4'];

        if ($zipA['zip5'] === $zipB['zip5']) {
            $localePercent += $zip4Mismatch ? .20 : .40;
        }

        $cityPercent = $this->similarText($a->city, $b->city);

        $localePercent += ($cityPercent * .35);

        if ('' !== $a->address && '' !== $b->address) {
            $addressPercent = $this->similarText($a->address, $b->address);
            $localePercent += ($addressPercent * .25);
        } else {
            $localePercent *= (100 / 75);
        }

        return $localePercent;
    }

    /**
     * @return ZipCode
     */
    private function getParsedZip(Donor $donor): array
    {
        if (!isset($this->zipCodes[$donor])) {
            $this->zipCodes[$donor] = StringUtilities::parseZip($donor->zip);
        }

        return $this->zipCodes[$donor];
    }

    public function areNamesSimilar(Donor $a, Donor $b): bool
    {
        if (++$this->gcCounter > $this->gcCount) {
            $this->gc();
        }

        return ($this->compareNames($a, $b) * 100.0) >= $this->options->minimumNameSimilarity;
    }
}
