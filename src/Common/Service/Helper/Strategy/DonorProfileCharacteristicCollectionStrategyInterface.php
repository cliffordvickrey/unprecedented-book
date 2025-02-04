<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Helper\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

/**
 * @template TEntity of Entity
 */
interface DonorProfileCharacteristicCollectionStrategyInterface
{
    /**
     * @return list<DonorCharacteristic>
     *
     * @phpstan-param TEntity $entity
     */
    public function collectCharacteristics(Entity $entity): array;
}
