<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Helper;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeCollection;
use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCampaignCharacteristicCollectionStrategy;
use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCharacteristicCollectionStrategyInterface;
use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2016;
use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2020;
use CliffordVickrey\Book2024\Common\Service\Helper\Strategy\DonorProfileCycleCharacteristicCollectionStrategy2024;
use Webmozart\Assert\Assert;

final readonly class DonorProfileCharacteristicCollector
{
    /** @var array<class-string<Entity>, DonorProfileCharacteristicCollectionStrategyInterface<covariant Entity>> */
    private array $collectionStrategies;

    /**
     * @param array<int, RecipientAttributeCollection> $recipientAttributesByCycle
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
     * @return array<string, list<DonorCharacteristic>>
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
                ...$this->doCollectCharacteristics($profileCycle),
            ],
            []
        );

        /** @var array<string, list<DonorCharacteristic>> $campaignCharacteristics */
        $campaignCharacteristics = array_reduce(
            array_filter(
                $profile->campaigns,
                static fn (DonorProfileCampaign $profileCampaign) => (bool) \count($profileCampaign)
            ),
            fn (array $carry, DonorProfileCampaign $profileCampaign) => array_merge(
                $carry,
                [$profileCampaign->campaignType->value => [
                    ...$donorCharacteristics,
                    ...$this->doCollectCharacteristics($profileCampaign)],
                ]
            ),
            []
        );

        return $campaignCharacteristics;
    }

    /**
     * Combining two of everyone's favorite things: US elections AND enterprise code! Excitement city! A better duo than
     * chocolate and peanut butter...
     *
     * @return list<DonorCharacteristic>
     */
    private function doCollectCharacteristics(Entity $entity): array
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

        return $strategy; // @phpstan-ignore-line
    }
}
