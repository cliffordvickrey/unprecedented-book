<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Enum\CommitteeGenre;
use Webmozart\Assert\Assert;

abstract class AbstractReceiptService
{
    protected function getFilename(string $committeeSlug, bool $withDonorId = true): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $committeeSlug);
        Assert::stringNotEmpty($slug);

        $leading = CommitteeGenre::fromSlug($committeeSlug)->value;

        $dir = $withDonorId ? 'receipts' : '_receipts';
        $subDir = "$leading-".substr($slug, 0, 1);

        return __DIR__."/../../../data/$dir/$subDir/$slug.csv";
    }
}
