<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use Webmozart\Assert\Assert;

/**
 * @phpstan-type ParsedName array{first: ?string, last: string, nickname: ?string, prefix: ?string, suffix: ?string}
 * @phpstan-type ZipCode array{zip5: string, zip4: non-empty-string|null}
 */
class StringUtilities
{
    /** @var list<string> */
    private static array $prefixes = [
        'CAPT',
        'COL',
        'COLONEL',
        'DR',
        'MR',
        'MRS',
        'MS',
        'PROF',
        'REV',
        'REVEREND',
    ];

    /** @var list<string> */
    private static array $suffixes = [
        'DDS',
        'DVM',
        'ESQ',
        'GOVERNOR',
        'II',
        'III',
        'IV',
        'JR',
        'MD',
        'PHD',
        'REP',
        'SENATOR',
        'SR',
    ];

    public static function normalizeCandidateName(string $name): string
    {
        $parsed = self::parseName($name);

        $parts = array_filter([
            $parsed['first'],
            $parsed['nickname'],
            $parsed['last'],
            $parsed['suffix'],
        ], \is_string(...));

        $name = preg_replace('/\s+/', ' ', implode(' ', $parts));
        Assert::string($name);

        return trim($name);
    }

    /**
     * @return ParsedName
     */
    public static function parseName(string $name): array
    {
        $parts = explode(', ', $name, 2);

        $parsed = [
            'first' => null,
            'last' => array_shift($parts),
            'nickname' => null,
            'prefix' => null,
            'suffix' => null,
        ];

        $first = array_shift($parts);

        if (null === $first) {
            return $parsed;
        }

        $firstParts = explode(' ', $first);

        return array_reduce($firstParts, function (array $carry, string $part): array {
            /** @var ParsedName $carry */
            $partSansPeriods = preg_replace('/\./', '', $part);
            Assert::string($partSansPeriods);

            if (\in_array($partSansPeriods, self::$prefixes)) {
                $carry['prefix'] = $partSansPeriods;

                return $carry;
            }

            if (\in_array($partSansPeriods, self::$suffixes)) {
                $carry['suffix'] = $partSansPeriods;

                return $carry;
            }

            if (preg_match('/^["(](.*)[")]$/', $part, $matches)) {
                $carry['nickname'] = \sprintf('"%s"', $matches[1]);

                return $carry;
            }

            if (null === $carry['first']) {
                $carry['first'] = '';
            } else {
                $carry['first'] .= ' ';
            }

            $carry['first'] .= $part;

            return $carry;
        }, $parsed);
    }

    /**
     * @param int<0, max> $decimals
     */
    public static function numberFormat(mixed $val, int $decimals = 0): string
    {
        return number_format((float) CastingUtilities::toFloat($val), $decimals);
    }

    public static function md5(mixed $val): string
    {
        return md5(serialize($val));
    }

    public static function slugify(
        string $str,
        ?int $maxLength = null,
        bool $uppercase = false,
        bool $noNumbers = false,
    ): string {
        // 1. remove non-alphanumeric characters (except spaces and hyphens)
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
        Assert::string($slug);

        // 2. strip out numeric digits
        if ($noNumbers) {
            $slug = preg_replace('/\d/', '', $slug);
            Assert::string($slug);
        }

        // 3. normalize whitespace
        $slug = preg_replace('/\s+/', ' ', str_replace('-', ' ', $slug));
        Assert::string($slug);

        // 4. replace spaces with underscores
        $slug = str_replace(' ', '_', $slug);

        // 5. Remove diacritics
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        Assert::string($slug);

        // 6. Convert to lowercase or uppercase
        $slug = $uppercase ? strtoupper($slug) : strtolower($slug);

        // 7. Trim underscores
        $slug = trim($slug, '_');

        // 8. Enforce maximum length
        if (null !== $maxLength) {
            $slug = substr($slug, 0, $maxLength);
        }

        return $slug;
    }

    /**
     * @return ZipCode
     */
    public static function parseZip(string $zipCode): array
    {
        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);
        Assert::string($zipCode);

        return [
            'zip5' => substr($zipCode, 0, 5),
            'zip4' => (\strlen($zipCode) > 5 ? substr($zipCode, 5) : null) ?: null,
        ];
    }

    public static function similarText(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }

        similar_text($a, $b, $percentMatch);

        return $percentMatch / 100.0;
    }

    public static function nonce(int $seed, int $length = 32): string
    {
        /** @phpstan-var string $characters */
        static $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';

        srand($seed);

        for ($i = 0; $i < $length; ++$i) {
            $nonce .= $characters[rand(0, 25)];
        }

        return $nonce;
    }

    public static function snakeCaseToPascalCase(string $str): string
    {
        $words = explode('_', $str);

        return implode('', array_map(\ucfirst(...), $words));
    }
}
