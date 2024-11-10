<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;

interface ReceiptWritingServiceInterface
{
    public function deleteReceipts(bool $withDonorIds = false): void;

    public function flush(): void;

    public function write(Receipt $receipt): void;
}
