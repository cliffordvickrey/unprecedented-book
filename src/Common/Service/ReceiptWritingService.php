<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Csv\ChunkedCsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use Webmozart\Assert\Assert;

class ReceiptWritingService implements ReceiptWritingServiceInterface
{
    private readonly ChunkedCsvWriter $writer;
    /** @var array<string, string> */
    private array $filenames = [];

    public function __construct(?ChunkedCsvWriter $writer = null)
    {
        $this->writer = $writer ?? new ChunkedCsvWriter();
    }

    public function flush(): void
    {
        $this->writer->flush();
    }

    public function write(Receipt $receipt): void
    {
        $this->writer->push($this->getFilename($receipt), $receipt->toArray(true));
    }

    private function getFilename(Receipt $receipt): string
    {
        $filenameKey = self::getFilenameKey($receipt);

        $filename = $this->filenames[$filenameKey] ?? null;

        if (null !== $filename) {
            return $filename;
        }

        $filename = self::resolveFilename($receipt);
        $this->filenames[$filenameKey] = $filename;
        $this->writer->push($filename, Receipt::headers());

        return $filename;
    }

    private static function getFilenameKey(Receipt $receipt): string
    {
        return \sprintf('%d|%s', $receipt->donor_id ? 1 : 0, $receipt->fec_committee_id);
    }

    private static function resolveFilename(Receipt $receipt): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $receipt->fec_committee_id);
        Assert::stringNotEmpty($slug);

        $leading = 'pac';

        if (str_contains($slug, '-')) {
            $leading = 'cand';
        }

        $dir = $receipt->donor_id ? 'receipts' : '_receipts';
        $subDir = "$leading-".substr($slug, 0, 1);

        return __DIR__."/../../../data/$dir/$subDir/$slug.csv";
    }
}
