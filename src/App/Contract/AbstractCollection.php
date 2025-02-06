<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Contract;

use ArrayAccess;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Iterator;

use function key;

/**
 * @template TKey
 * @template TValue
 *
 * @implements ArrayAccess<TKey, TValue>
 * @implements Iterator<TKey, TValue>
 */
abstract class AbstractCollection implements \ArrayAccess, \Countable, \Iterator
{
    /** @var array<array-key, TValue> */
    protected array $data = [];

    public function __clone(): void
    {
        $data = [];

        foreach ($this->data as $k => $v) {
            $data[$k] = \is_object($v) ? (clone $v) : $v;
        }

        $this->data = $data;
    }

    /**
     * @param TKey $key
     *
     * @return TValue|null
     */
    public function get(mixed $key): mixed
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        return null;
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists(self::normalizeOffset($offset), $this->data);
    }

    private static function normalizeOffset(mixed $offset): int|string
    {
        if (\is_int($offset) || \is_string($offset)) {
            return $offset;
        }

        return (string) CastingUtilities::toString($offset);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        $normalizedOffset = self::normalizeOffset($offset);

        if (\array_key_exists($normalizedOffset, $this->data)) {
            return $this->data[$normalizedOffset];
        }

        throw new BookOutOfBoundsException('Invalid offset');
    }

    /**
     * @return TValue
     */
    public function current(): mixed
    {
        $value = \current($this->data);

        if (false === $value) {
            throw new BookOutOfBoundsException('Key is out of bounds');
        }

        return $value;
    }

    public function next(): void
    {
        \next($this->data);
    }

    public function valid(): bool
    {
        return null !== $this->key();
    }

    public function key(): string|int|null
    {
        return \key($this->data);
    }

    public function rewind(): void
    {
        \reset($this->data);
    }

    /**
     * @param TKey|null $offset
     * @param TValue    $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->data[] = $value;

            return;
        }

        $this->data[self::normalizeOffset($offset)] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[self::normalizeOffset($offset)]);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->data);
    }
}
