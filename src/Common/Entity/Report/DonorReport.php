<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

/**
 * @implements \ArrayAccess<int, DonorReportRow>
 * @implements \IteratorAggregate<int, DonorReportRow>
 */
class DonorReport extends Entity implements \ArrayAccess, \Countable, \IteratorAggregate
{
    public const string ALL = 'all';

    public State $state = State::USA;
    public ?DonorCharacteristic $characteristicA = null;
    public ?DonorCharacteristic $characteristicB = null;
    public int $n = 0;
    /** @var list<DonorReportRow> */
    public array $rows = [];

    public static function build(
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): self {
        $self = new self();
        $self->state = $state;
        $self->characteristicA = $characteristicA;
        $self->characteristicB = $characteristicB;

        $characteristics = array_values(array_filter(
            DonorCharacteristic::cases(),
            static fn (DonorCharacteristic $characteristic) => !$characteristic->isMutuallyExclusive(
                $characteristicA,
                $characteristicB
            )
        ));

        $self->rows = array_map(
            static fn (DonorCharacteristic $characteristic) => DonorReportRow::__set_state([
                'characteristic' => $characteristic,
            ]),
            $characteristics
        );

        return $self;
    }

    public function getKey(): string
    {
        return self::inflectForKey($this->state, $this->characteristicA, $this->characteristicB);
    }

    public static function inflectForKey(
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): string {
        return \sprintf(
            '%s-%s-%s',
            strtolower($state->value),
            $characteristicA->value ?? self::ALL,
            $characteristicB->value ?? self::ALL
        );
    }

    /**
     * @return array<string, self>
     */
    public static function collectNew(): array
    {
        $states = State::cases();

        $reports = [];

        $characteristicsA = DonorCharacteristic::cases();

        foreach ($states as $state) {
            $reports[self::inflectForKey($state)] = self::build($state);

            foreach ($characteristicsA as $characteristicA) {
                $reports[self::inflectForKey($state, $characteristicA)] = self::build($state, $characteristicA);

                $characteristicsB = DonorCharacteristic::cases();

                foreach ($characteristicsB as $characteristicB) {
                    if ($characteristicA->isMutuallyExclusive($characteristicB)) {
                        continue;
                    }

                    $reports[self::inflectForKey($state, $characteristicA, $characteristicB)] = self::build(
                        $state,
                        $characteristicA,
                        $characteristicB
                    );
                }
            }
        }

        return $reports;
    }

    public function setPercentages(): void
    {
        array_walk($this->rows, fn (DonorReportRow $row) => $row->value->percent = MathUtilities::divide(
            $row->value->donors,
            $this->n,
            4
        ));
    }

    /**
     * @return \ArrayIterator<int, DonorReportRow>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->rows);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->rows[self::normalizeOffset($offset)]);
    }

    private static function normalizeOffset(mixed $offset): int
    {
        return CastingUtilities::toInt($offset) ?? -1;
    }

    public function offsetGet(mixed $offset): DonorReportRow
    {
        $row = $this->rows[self::normalizeOffset($offset)] ?? null;

        if (null === $row) {
            $msg = \sprintf('Illegal offset, "%s"', CastingUtilities::toString($offset));
            throw new BookOutOfBoundsException($msg);
        }

        return $row;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->rows[] = $value;

            return;
        }

        $rows = $this->rows;
        $rows[self::normalizeOffset($offset)] = $value;
        $this->rows = array_values($rows);
    }

    public function offsetUnset(mixed $offset): void
    {
        $rows = $this->rows;
        unset($rows[self::normalizeOffset($offset)]);
        $this->rows = array_values($rows);
    }

    public function count(): int
    {
        return \count($this->rows);
    }
}
