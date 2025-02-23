<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Cache;

/**
 * @phpstan-type CacheStat array{total: int, free: int}
 */
interface CacheInterface
{
    public function clear(): void;

    /**
     * @param class-string         $classStr
     * @param array<string, mixed> $params
     */
    public function delete(string $classStr, array $params = []): void;

    /**
     * @param class-string<TObject> $classStr
     * @param array<string, mixed>  $params
     *
     * @template TObject of object
     *
     * @phpstan-return TObject|null
     */
    public function get(string $classStr, array $params = []): ?object;

    /**
     * @param int<0, max>          $ttl
     * @param array<string, mixed> $params
     */
    public function set(object $object, array $params = [], int $ttl = 0): void;

    /**
     * @return CacheStat
     */
    public function stat(): array;
}
