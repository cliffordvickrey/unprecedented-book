<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;

interface ReceiptReadingServiceInterface
{
    /**
     * @return \Generator<Receipt>
     */
    public function readByCommitteeSlug(string $committeeSlug, bool $withDonorIds = true): \Generator;

    /**
     * @return \Generator<Receipt>
     */
    public function readByCommitteeId(string $committeeId, bool $withDonorIds = true): \Generator;
}
