<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;

interface MatchServiceInterface
{
    public function areSurnamesSimilar(string $a, string $b): bool;

    public function compare(Donor $a, Donor $b): MatchResult;
}
