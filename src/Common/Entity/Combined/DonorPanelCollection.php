<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;

/**
 * @implements \IteratorAggregate<DonorPanel>
 */
class DonorPanelCollection extends Entity implements \Countable, \IteratorAggregate
{
    #[PropMeta(0)]
    public int $id = 0;
    #[PropMeta(1)]
    public string $state = '';
    /** @var list<DonorPanel> */
    #[PropMeta(2)]
    public array $donorPanels = [];

    /**
     * @return int<-1, 1>
     */
    private static function donorPanelSorter(DonorPanel $a, DonorPanel $b): int
    {
        return $a->donor->id <=> $b->donor->id;
    }

    public function sortDonorPanels(): void
    {
        usort($this->donorPanels, self::donorPanelSorter(...));

        array_walk($this->donorPanels, static fn (DonorPanel $panel) => $panel->sortReceipts());
    }

    /**
     * @return \ArrayIterator<int, DonorPanel>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->donorPanels);
    }

    public function count(): int
    {
        return \count($this->donorPanels);
    }
}
