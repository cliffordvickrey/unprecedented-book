<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

interface MatchServiceInterface
{
    public function areSurnamesSimilar(string $a, string $b): bool;
}