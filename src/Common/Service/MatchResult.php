<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;

final readonly class MatchResult
{
    public function __construct(
        public Donor $a,
        public Donor $b,
        public float $similarityScore,
        public ?int $id = null,
    ) {
    }

    public static function newUniqueDonor(Donor $a): self
    {
        return new self($a, $a, 100.0, $a->id);
    }

    /**
     * @return list<int|string|float|null>
     */
    public function toSet(): array
    {
        return [
            $this->id,
            $this->similarityScore,
            (string) $this->a,
            (string) $this->b,
        ];
    }
}
