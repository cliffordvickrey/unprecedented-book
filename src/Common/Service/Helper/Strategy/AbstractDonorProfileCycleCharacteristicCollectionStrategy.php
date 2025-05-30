<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Helper\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\PartyType;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeCollection;

/**
 * @implements DonorProfileCharacteristicCollectionStrategyInterface<TCycle>
 *
 * @template TCycle of DonorProfileCycle
 */
abstract class AbstractDonorProfileCycleCharacteristicCollectionStrategy implements DonorProfileCharacteristicCollectionStrategyInterface
{
    public const int MIN_OUT_OF_STATE_COMMITTEES = 4;

    /** @var array<string, list<string>> */
    private array $presidentialPropertiesByParty = [];

    final public function __construct(private readonly RecipientAttributeCollection $recipientAttributes)
    {
    }

    /**
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    final public function collectCharacteristics(Entity $entity): array
    {
        $arr = [];

        if ($entity->houseDemocratic->receipts || $entity->senateDemocratic->receipts) {
            $arr[] = 'cycle_%d_dem_non_pres';
        }

        if ($entity->houseRepublican->receipts || $entity->senateRepublican->receipts) {
            $arr[] = 'cycle_%d_gop_non_pres';
        }

        $outOfStateCampaigns = \count($entity->outOfStateHouse) + \count($entity->outOfStateSenate);

        if ($outOfStateCampaigns >= self::MIN_OUT_OF_STATE_COMMITTEES) {
            $arr[] = 'cycle_%d_out_of_state';
        }

        if ($entity->partyCommittee->receipts) {
            $arr[] = 'cycle_%d_party_elite';
        }

        if ($entity->superPac->receipts) {
            $arr[] = 'cycle_%d_super_pac';
        }

        return [
            ...array_map(
                static fn ($str) => DonorCharacteristic::from(\sprintf($str, $entity->cycle)),
                $arr
            ),
            ...$this->doCollectAttributesForCycle($entity),
        ];
    }

    /**
     * @return list<DonorCharacteristic>
     *
     * @phpstan-param TCycle $profileCycle
     */
    abstract protected function doCollectAttributesForCycle(DonorProfileCycle $profileCycle): array;

    protected function hasPresReceiptsByPartyForCycle(DonorProfileCycle $profileCycle, PartyType $party): bool
    {
        $props = $this->getPresidentialPropertiesByParty($profileCycle, $party);

        foreach ($props as $prop) {
            /** @var DonorProfileAmount $value */
            $value = $profileCycle->{$prop};

            if ($value->receipts) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function getPresidentialPropertiesByParty(DonorProfileCycle $profileCycle, PartyType $party): array
    {
        if (!isset($this->presidentialPropertiesByParty[$party->value])) {
            $this->presidentialPropertiesByParty[$party->value] = array_values(array_filter(
                $this->getPresidentialProperties($profileCycle),
                fn (string $prop) => $this->recipientAttributes[$prop]->party === $party
            ));
        }

        return $this->presidentialPropertiesByParty[$party->value];
    }

    /**
     * @return list<string>
     */
    private static function getPresidentialProperties(DonorProfileCycle $profileCycle): array
    {
        return array_keys(array_filter(
            $profileCycle->toArray(),
            static fn ($key) => str_starts_with($key, 'pres'),
            \ARRAY_FILTER_USE_KEY
        ));
    }
}
