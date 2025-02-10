<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

/**
 * @implements \ArrayAccess<int, TRow>
 * @implements \IteratorAggregate<int, TRow>
 *
 * @template TRow of AbstractReportRow
 */
abstract class AbstractReport extends Entity implements \ArrayAccess, \Countable, \IteratorAggregate
{
    public const string ALL = 'all';

    public CampaignType $campaignType = CampaignType::donald_trump;
    public State $state = State::USA;
    public ?DonorCharacteristic $characteristicA = null;
    public ?DonorCharacteristic $characteristicB = null;
    /** @var list<TRow> */
    public array $rows = [];
    /** @var array<string, TRow>|null */
    private ?array $indexedRows = null;

    /**
     * @return array<string, static>
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
                    if ($characteristicA->isMutuallyExclusiveOrTautologicalWith($campaign)) {
                        continue;
                    }

                    $reports[self::inflectForKey($campaign, $state, $characteristicA)] = self::build(
                        $campaign,
                        $state,
                        $characteristicA
                    );

                    foreach ($characteristicsB as $characteristicB) {
                        if ($characteristicB->isMutuallyExclusiveOrTautologicalWith($characteristicA, $campaign)) {
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

    /**
     * @return static<TRow>
     */
    protected static function build(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): static {
        /** @var static<TRow> $static */
        $static = new static();
        $static->campaignType = $campaignType;
        $static->state = $state;
        $static->characteristicA = $characteristicA;
        $static->characteristicB = $characteristicB;
        $static->init();

        return $static;
    }

    abstract protected function init(): void;

    public function getKey(): string
    {
        return self::inflectForKey(
            $this->campaignType, $this->state, $this->characteristicA, $this->characteristicB);
    }

    /**
     * @return \ArrayIterator<int, TRow>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->rows);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->rows[self::normalizeOffset($offset)]);
    }

    private function normalizeOffset(mixed $offset): int
    {
        return CastingUtilities::toInt($offset) ?? -1;
    }

    /**
     * @phpstan-return TRow
     */
    public function offsetGet(mixed $offset): AbstractReportRow
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
        $this->indexedRows = null;
    }

    /**
     * @phpstan-param TRow $row
     */
    abstract protected function getRowIndex(AbstractReportRow $row): string;

    public function hasIndex(string $index): bool
    {
        return isset($this->mapByIndex()[$index]);
    }

    /**
     * @return array<string, TRow>
     */
    private function mapByIndex(): array
    {
        if (null === $this->indexedRows) {
            /** @var array<string, TRow> $indexedRows */
            $indexedRows = array_reduce(
                $this->rows,
                fn (array $carry, AbstractReportRow $row) => array_merge(
                    $carry,
                    [$this->getRowIndex($row) => $row]
                ),
                []
            );

            $this->indexedRows = $indexedRows;
        }

        return $this->indexedRows;
    }

    /**
     * @phpstan-return TRow
     */
    public function getByIndex(string $index): AbstractReportRow
    {
        $row = $this->mapByIndex()[$index] ?? null;

        if (null !== $row) {
            return $row;
        }

        throw new BookOutOfBoundsException(\sprintf('Report lacks value at index "%s"', $index));
    }

    public function offsetUnset(mixed $offset): void
    {
        $rows = $this->rows;
        unset($rows[self::normalizeOffset($offset)]);
        $this->rows = array_values($rows);
        $this->indexedRows = null;
    }

    public function count(): int
    {
        return \count($this->rows);
    }

    /**
     * @param callable(TRow): bool $filter
     */
    public function withFilter(callable $filter): static
    {
        $self = clone $this;

        $self->rows = array_values(array_filter($self->rows, $filter));

        return $self;
    }
}
