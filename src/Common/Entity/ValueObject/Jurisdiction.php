<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

final readonly class Jurisdiction implements \Stringable
{
    public function __construct(public string $state, public ?int $district = null)
    {
    }

    public function __toString(): string
    {
        if (null === $this->district) {
            return $this->state;
        }

        return \sprintf('%s%02d', $this->state, $this->district);
    }
}
