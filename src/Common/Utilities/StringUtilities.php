<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use Webmozart\Assert\Assert;

/**
 * @phpstan-type ParsedName array{first: ?string, last: string, nickname: ?string, prefix: ?string, suffix: ?string}
 */
class StringUtilities
{
    /** @var list<string> */
    private static array $prefixes = [
        'CAPT',
        'DR',
        'MR',
        'MRS',
        'MS',
        'PROF',
        'REV',
    ];

    /** @var list<string> */
    private static array $suffixes = [
        'DDS',
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
        $parsed = self::parseCandidateName($name);

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
    private static function parseCandidateName(string $name): array
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

    public static function slugify(string $str): string
    {
        // remove non-alphanumeric characters (except spaces)
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $str);
        Assert::string($slug);

        // normalize whitespace
        $slug = preg_replace('/\s+/', ' ', str_replace('-', ' ', $slug));
        Assert::string($slug);

        // replace spaces with underscores
        $slug = str_replace(' ', '_', $slug);

        // 3. Remove diacritics
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        Assert::string($slug);

        // 4. Convert to lowercase
        return strtolower($slug);
    }
}
