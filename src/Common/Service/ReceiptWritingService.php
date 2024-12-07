<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Csv\ChunkedCsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;

class ReceiptWritingService extends AbstractReceiptService implements ReceiptWritingServiceInterface
{
    private readonly ChunkedCsvWriter $writer;
    /** @var array<string, string> */
    private array $filenames = [];

    public function __construct(?ChunkedCsvWriter $writer = null)
    {
        $this->writer = $writer ?? new ChunkedCsvWriter();
    }

    public function deleteReceipts(bool $withDonorIds = false): void
    {
        $dir = $withDonorIds ? 'receipts' : '_receipts';

        FileUtilities::unlink(__DIR__."/../../../data/$dir", recursive: true);
    }

    public function flush(): void
    {
        $this->writer->flush();
    }

    public function write(Receipt $receipt): void
    {
        $this->writer->push($this->filename($receipt), $receipt->toArray(true));
    }

    private function filename(Receipt $receipt): string
    {
        $filenameKey = self::getFilenameKey($receipt);

        $filename = $this->filenames[$filenameKey] ?? null;

        if (null !== $filename) {
            return $filename;
        }

        $filename = $this->getFilename($receipt->committee_slug, (bool) $receipt->donor_id);
        $this->filenames[$filenameKey] = $filename;
        $this->writer->push($filename, Receipt::headers());

        return $filename;
    }

    private static function getFilenameKey(Receipt $receipt): string
    {
        return \sprintf('%d|%s', $receipt->donor_id ? 1 : 0, $receipt->committee_slug);
    }
}
