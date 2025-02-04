<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Collector;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Service\Collector\Strategy\DonorProfileCampaignCharacteristicCollectionStrategy;
use CliffordVickrey\Book2024\Common\Service\Collector\Strategy\DonorProfileCharacteristicCollectionStrategyInterface;
use CliffordVickrey\Book2024\Common\Service\Collector\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2016;
use CliffordVickrey\Book2024\Common\Service\Collector\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2020;
use CliffordVickrey\Book2024\Common\Service\Collector\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2024;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeBag;
use Webmozart\Assert\Assert;

final readonly class DonorProfileCharacteristicCollector
{
    /** @var array<class-string<Entity>, DonorProfileCharacteristicCollectionStrategyInterface<covariant Entity>> */
    private array $collectionStrategies;

    /**
     * @param array<int, RecipientAttributeBag> $recipientAttributesByCycle
     */
    public function __construct(array $recipientAttributesByCycle)
    {
        $this->collectionStrategies = [
            DonorProfileCampaign::class => new DonorProfileCampaignCharacteristicCollectionStrategy(),
            DonorProfileCycle2016::class => new DonorProfileCycleCharacteristicCollectionStrategy2016(
                $recipientAttributesByCycle[2016]
            ),
            DonorProfileCycle2020::class => new DonorProfileCycleCharacteristicCollectionStrategy2020(
                $recipientAttributesByCycle[2020]
            ),
            DonorProfileCycle2024::class => new DonorProfileCycleCharacteristicCollectionStrategy2024(
                $recipientAttributesByCycle[2024]
            ),
        ];
    }

    /**
     * @return array<string, DonorCharacteristic>
     */
    public function collectCharacteristics(DonorProfile $profile): array
    {
        if (0 === \count($profile)) {
            return [];
        }

        /** @var list<DonorCharacteristic> $donorCharacteristics */
        $donorCharacteristics = array_reduce(
            $profile->cycles,
            fn (array $carry, DonorProfileCycle $profileCycle) => [
                ...$carry,
                ...$this->doCollect($profileCycle),
            ],
            []
        );

        /** @var array<string, DonorCharacteristic> $campaignCharacteristics */
        $campaignCharacteristics = array_reduce(
            array_filter(
                $profile->campaigns,
                static fn (DonorProfileCampaign $profileCampaign) => (bool) \count($profileCampaign)
            ),
            fn (array $carry, DonorProfileCampaign $profileCampaign) => array_merge(
                $carry,
                [$profileCampaign->campaignType->value => [
                    ...$donorCharacteristics,
                    ...$this->doCollect($profileCampaign)],
                ]
            ),
            []
        );

        return $campaignCharacteristics;
    }

    /**
     * @return list<DonorCharacteristic>
     */
    private function doCollect(Entity $entity): array
    {
        return $this->strategize($entity)->collectCharacteristics($entity);
    }

    /**
     * @return DonorProfileCharacteristicCollectionStrategyInterface<TEntity>
     *
     * @phpstan-param TEntity $entity
     *
     * @template TEntity of Entity
     */
    private function strategize(Entity $entity): DonorProfileCharacteristicCollectionStrategyInterface
    {
        $strategy = $this->collectionStrategies[$entity::class] ?? null;
        Assert::notNull($strategy, \sprintf('Unexpected classname, "%s"', $entity::class));

        /** @var DonorProfileCharacteristicCollectionStrategyInterface<TEntity> $strategy */
        return $strategy;
    }
}
