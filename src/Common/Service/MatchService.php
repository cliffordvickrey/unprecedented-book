<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Config\MatchOptions;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

/**
 * @phpstan-import-type NameParts from Donor
 * @phpstan-import-type ZipCode from StringUtilities
 */
class MatchService implements MatchServiceInterface
{
    private const string CACHE_TYPE_A = 'a';
    private const string CACHE_TYPE_B = 'b';

    public const int GC_COUNT = 100000;
    public const int MAX_MEMORY = 8589934592;

    /** @var int<0, max> */
    private int $gcCounter = 0;
    private float $lastSimilarName = 0.0;
    private float $lastSimilarText = 0.0;
    /** @var \WeakMap<Donor, NameParts> */
    private \WeakMap $nameParts;
    private MatchOptions $options;
    /** @var array<self::CACHE_TYPE_*, array<string, float>> */
    private array $similarTextMemo = [self::CACHE_TYPE_A => [], self::CACHE_TYPE_B => []];
    /** @var \WeakMap<Donor, ZipCode> */
    private \WeakMap $zipCodes;

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

        $types = array_reverse(array_keys($this->similarTextMemo));

        foreach ($types as $type) {
            if (memory_get_usage() < $this->maxMemory) {
                return;
            }

            $this->similarTextMemo[$type] = [];
            gc_collect_cycles();
        }
    }

    /**
     * @param self::CACHE_TYPE_* $cacheType
     */
    private function similarText(string $a, string $b, string $cacheType = self::CACHE_TYPE_A): float
    {
        $key = "$a|$b";

        if (isset($this->similarTextMemo[$cacheType][$key])) {
            $similarText = $this->similarTextMemo[$cacheType][$key];
        } else {
            $similarText = StringUtilities::similarText($a, $b);

            $this->similarTextMemo[$cacheType][$key] = $similarText;
            $this->similarTextMemo[$cacheType]["$b|$a"] = $similarText;
        }

        $this->lastSimilarText = $similarText;

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
        $a = $this->getNormalizedNameParts($a);
        $b = $this->getNormalizedNameParts($b);

        $similarities = [$this->similarText(array_shift($a), array_shift($b))];

        foreach ($a as $i => $nameA) {
            $nameB = $b[$i] ?? null;

            if (null === $nameB) {
                break;
            }

            $similarities[] = $this->similarText($nameA, $nameB);
        }

        $similarName = array_sum($similarities) / \count($similarities);
        $this->lastSimilarName = $similarName;

        return $similarName;
    }

    /**
     * @return NameParts
     */
    private function getNormalizedNameParts(Donor $donor): array
    {
        if (!isset($this->nameParts[$donor])) {
            $this->nameParts[$donor] = $donor->getNormalizedNameParts();
        }

        return $this->nameParts[$donor];
    }

    private function compareLocales(Donor $a, Donor $b): float
    {
        $citySimilarity = null;
        $addressSimilarity = null;
        $zip5Similarity = null;
        $zip4Similarity = null;

        // city
        if ('' !== $a->city && '' !== $b->city) {
            $citySimilarity = $this->similarText($a->city, $b->city);
        }

        // address
        if ('' !== $a->address && '' !== $b->address) {
            // memoized address similarity scores are evicted from the cache first
            $addressSimilarity = $this->similarText($a->address, $b->address, cacheType: self::CACHE_TYPE_B);
        }

        // zip
        $zipA = $this->getParsedZip($a);
        $zipB = $this->getParsedZip($b);

        $zip5Mismatch = '' !== $zipA['zip5'] && '' !== $zipB['zip5'] && $zipA['zip5'] !== $zipB['zip5'];

        if (!$zip5Mismatch) {
            $zip5Similarity = 1.0;

            $zip4Mismatch = null !== $zipA['zip4'] && null !== $zipB['zip4'] && $zipA['zip4'] !== $zipB['zip4'];
            if ($zip4Mismatch) {
                $zip4Similarity = 0.0;
            } else {
                $zip4Similarity = 1.0;
            }
        } elseif ('' !== $zipA['zip5'] && '' !== $zipB['zip5']) {
            $zip5Similarity = 0.0;
            $zip4Similarity = 0.0;
        }

        $localeSimilarity = 0.0;

        if (null === $citySimilarity) {
            $localeSimilarity = $this->options->localeOptions->cityFactor;
        } else {
            self::addSimilarity($localeSimilarity, $citySimilarity, $this->options->localeOptions->cityFactor);
        }

        self::addSimilarity($localeSimilarity, $zip5Similarity, $this->options->localeOptions->zip5Factor);
        self::addSimilarity($localeSimilarity, $zip4Similarity, $this->options->localeOptions->zip4Factor);
        self::addSimilarity($localeSimilarity, $addressSimilarity, $this->options->localeOptions->addressFactor);

        return $localeSimilarity;
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

    /**
     * @param-out float $amt
     */
    private static function addSimilarity(float &$amt, ?float $similarity, float $factor): void
    {
        if (null === $similarity) {
            $amt *= (1 / (1 - $factor));

            return;
        }

        $amt += ($similarity * $factor);
    }

    public function areNamesSimilar(Donor $a, Donor $b): bool
    {
        if (++$this->gcCounter > $this->gcCount) {
            $this->gc();
        }

        return $this->compareNames($a, $b) >= $this->options->minimumNameSimilarity;
    }

    public function getLastSimilarText(): float
    {
        return round($this->lastSimilarText, 4);
    }

    public function getLastSimilarName(): float
    {
        return round($this->lastSimilarName, 4);
    }
}
