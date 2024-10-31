<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

use CliffordVickrey\Book2024\Common\Entity\Entity;

interface EntityHydratorInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function hydrate(Entity $entity, array $data): void;

    public function initialize(Entity $entity): void;

    /**
     * @return array<string, mixed>
     */
    public function extract(Entity $entity, bool $forJson = false): array;
}
