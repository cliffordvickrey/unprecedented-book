<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Cache;

use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Random\Randomizer;

readonly class Cache implements CacheInterface
{
    /**
     * @param int<0, max> $gcFrequency
     */
    public function __construct(int $gcFrequency = 0, private int $minSpace = 1048576)
    {
        if ($gcFrequency > 0) {
            $randomizer = new Randomizer();

            if (1 === $randomizer->getInt(1, $gcFrequency)) {
                $this->gc();
            }
        }
    }

    private function gc(): void
    {
        $stat = $this->stat();

        if ($stat['free'] < $this->minSpace) {
            $this->clear();
        }
    }

    public function clear(): void
    {
        apcu_clear_cache();
    }

    public function delete(string $classStr, array $params = []): void
    {
        apcu_delete(self::toKey($classStr, $params));
    }

    /**
     * @param class-string|object  $classStrOrObject
     * @param array<string, mixed> $params
     */
    private static function toKey(string|object $classStrOrObject, array $params): string
    {
        $classStr = \is_object($classStrOrObject) ? $classStrOrObject::class : $classStrOrObject;

        $query = '';

        if (0 !== \count($params)) {
            $query = http_build_query($params);
        }

        if ('' !== $query) {
            return \sprintf('%s?%s', $classStr, $query);
        }

        return $classStr;
    }

    public function get(string $classStr, array $params = []): ?object
    {
        $obj = apcu_fetch(self::toKey($classStr, $params));

        if (\is_object($obj) && is_a($obj, $classStr)) {
            return $obj;
        }

        return null;
    }

    public function set(object $object, array $params = [], int $ttl = 0): void
    {
        apcu_store(self::toKey($object, $params), $object, $ttl);
    }

    public function stat(): array
    {
        $info = apcu_sma_info(true) ?: [];

        $extract = static fn (string $key): int => (int) CastingUtilities::toInt($info[$key] ?? 0);

        $numSeg = $extract('num_seg');
        $segSize = $extract('seg_size');
        $availMem = $extract('avail_mem');

        return ['total' => $numSeg * $segSize, 'free' => $availMem];
    }
}
