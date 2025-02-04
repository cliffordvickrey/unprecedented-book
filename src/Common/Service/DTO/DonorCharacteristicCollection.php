<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\DTO;

use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

/**
 * @implements \ArrayAccess<string, list<DonorCharacteristic>>
 * @implements \IteratorAggregate<string, list<DonorCharacteristic>>
 */
class DonorCharacteristicCollection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, \Stringable
{
    /**
     * @param array<string, list<DonorCharacteristic>> $characteristicsByCampaign
     */
    public function __construct(private array $characteristicsByCampaign)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->characteristicsByCampaign[self::normalizeOffset($offset)]);
    }

    private static function normalizeOffset(mixed $offset): string
    {
        return (string) CastingUtilities::toString($offset);
    }

    /**
     * @return list<DonorCharacteristic>
     */
    public function offsetGet(mixed $offset): array
    {
        $offset = self::normalizeOffset($offset);

        return $this->characteristicsByCampaign[$offset]
            ?? throw new BookOutOfBoundsException(\sprintf('Illegal offset, "%s"', $offset));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->characteristicsByCampaign[self::normalizeOffset($offset)] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->characteristicsByCampaign[self::normalizeOffset($offset)]);
    }

    public function count(): int
    {
        return \count($this->characteristicsByCampaign);
    }

    public function __toString(): string
    {
        if (0 === \count($this->characteristicsByCampaign)) {
            return '';
        }

        $lines = [str_repeat('-', 80)];

        foreach ($this->characteristicsByCampaign as $candidate => $characteristics) {
            $lines = [
                ...$lines,
                \sprintf('%s:', $candidate),
                ...array_map(
                    static fn (DonorCharacteristic $characteristic) => $characteristic->value,
                    $characteristics
                ),
                '',
            ];
        }

        return implode(\PHP_EOL, $lines);
    }

    /**
     * @return \ArrayIterator<string, list<DonorCharacteristic>>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->characteristicsByCampaign);
    }

    /**
     * @return array<string, list<string>>
     */
    public function jsonSerialize(): array
    {
        return array_map(
            static fn ($arr) => array_map(fn (DonorCharacteristic $characteristic) => $characteristic->value, $arr),
            $this->characteristicsByCampaign
        );
    }
}
