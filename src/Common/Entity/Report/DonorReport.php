<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

/**
 * @implements \ArrayAccess<int, DonorReportRow>
 * @implements \IteratorAggregate<int, DonorReportRow>
 *
 * @phpstan-import-type DonorReportRecord from DonorReportRow
 */
class DonorReport extends Entity implements \ArrayAccess, \Countable, \IteratorAggregate
{
    public const string ALL = 'all';

    public CampaignType $campaignType = CampaignType::donald_trump;
    public State $state = State::USA;
    public ?DonorCharacteristic $characteristicA = null;
    public ?DonorCharacteristic $characteristicB = null;
    public DonorReportValue $totals;
    /** @var list<DonorReportRow> */
    public array $rows = [];
    /** @var array<string, DonorReportRow>|null */
    private ?array $rowsByCharacteristic = null;

    /**
     * @return array<string, self>
     */
    public static function collectNew(): array
    {
        $campaigns = CampaignType::cases();
        $states = State::cases();
        $characteristicsA = DonorCharacteristic::cases();
        $characteristicsB = DonorCharacteristic::cases();

        $reports = [];

        foreach ($campaigns as $campaign) {
            foreach ($states as $state) {
                $reports[self::inflectForKey($campaign, $state)] = self::build($campaign, $state);

                foreach ($characteristicsA as $characteristicA) {
                    $reports[self::inflectForKey($campaign, $state, $characteristicA)] = self::build(
                        $campaign,
                        $state,
                        $characteristicA
                    );

                    foreach ($characteristicsB as $characteristicB) {
                        if ($characteristicA->isMutuallyExclusive($characteristicB)) {
                            continue;
                        }

                        $key = self::inflectForKey($campaign, $state, $characteristicA, $characteristicB);

                        $reports[$key] = self::build($campaign, $state, $characteristicA, $characteristicB);
                    }
                }
            }
        }

        return $reports;
    }

    public static function inflectForKey(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): string {
        return \sprintf(
            '%s-%s-%s-%s',
            $campaignType->value,
            strtolower($state->value),
            $characteristicA->value ?? self::ALL,
            $characteristicB->value ?? self::ALL
        );
    }

    public static function build(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): self {
        $self = new self();
        $self->campaignType = $campaignType;
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

    /**
     * @param callable(DonorReportRow): bool $filter
     *
     * @return $this
     */
    public function withFilter(callable $filter): self
    {
        $self = clone $this;

        $self->rows = array_values(array_filter($self->rows, $filter));

        return $self;
    }

    /**
     * @return list<DonorReportRecord>
     */
    public function toRecords(): array
    {
        return array_map(static fn (DonorReportRow $row) => $row->toRecord(), $this->rows);
    }

    public function getKey(): string
    {
        return self::inflectForKey(
            $this->campaignType, $this->state, $this->characteristicA, $this->characteristicB);
    }

    public function setPercentages(): void
    {
        $this->totals->percent = 1.0;

        array_walk($this->rows, fn (DonorReportRow $row) => $row->value->percent = MathUtilities::divide(
            $row->value->donors,
            $this->totals->donors,
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
        $this->rowsByCharacteristic = null;
    }

    public function offsetUnset(mixed $offset): void
    {
        $rows = $this->rows;
        unset($rows[self::normalizeOffset($offset)]);
        $this->rows = array_values($rows);
        $this->rowsByCharacteristic = null;
    }

    public function count(): int
    {
        return \count($this->rows);
    }

    /**
     * @param list<DonorCharacteristic> $characteristics
     */
    public function add(DonorReportValue $value, array $characteristics): void
    {
        $this->totals->add($value);

        array_walk($characteristics, function (DonorCharacteristic $characteristic) use ($value): void {
            if (!$this->hasCharacteristic($characteristic)) {
                return;
            }

            $this->getByCharacteristic($characteristic)->value->add($value);
        });
    }

    public function hasCharacteristic(DonorCharacteristic $characteristic): bool
    {
        return isset($this->mapByCharacteristic()[$characteristic->value]);
    }

    /**
     * @return array<string, DonorReportRow>
     */
    private function mapByCharacteristic(): array
    {
        if (null === $this->rowsByCharacteristic) {
            /** @var array<string, DonorReportRow> $rowsByCharacteristic */
            $rowsByCharacteristic = array_reduce(
                $this->rows,
                static fn (array $carry, DonorReportRow $row) => array_merge(
                    $carry,
                    [$row->characteristic->value => $row]
                ),
                []
            );

            $this->rowsByCharacteristic = $rowsByCharacteristic;
        }

        return $this->rowsByCharacteristic;
    }

    public function getByCharacteristic(DonorCharacteristic $characteristic): DonorReportRow
    {
        $row = $this->mapByCharacteristic()[$characteristic->value] ?? null;

        if (null !== $row) {
            return $row;
        }

        throw new BookOutOfBoundsException(\sprintf('Report lacks characteristic "%s"', $characteristic->value));
    }
}
