<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

class DonorPanelCollection
{
    /** @var list<DonorPanel> */
    public array $donorPanels = [];

    public function sortDonorPanels(): void
    {
        usort($this->donorPanels, self::donorPanelSorter(...));
    }

    /**
     * @return int<-1, 1>
     */
    private static function donorPanelSorter(DonorPanel $a, DonorPanel $b): int
    {
        return $a->donor->id <=> $b->donor->id;
    }
}
