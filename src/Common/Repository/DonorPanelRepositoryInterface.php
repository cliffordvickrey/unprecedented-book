<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanelCollection;

interface DonorPanelRepositoryInterface
{
    /**
     * @return \Generator<DonorPanel>
     */
    public function get(?string $state = null, ?int $chunkId = null): \Generator;

    public function deleteAll(): void;

    public function save(DonorPanelCollection $panels): void;
}
