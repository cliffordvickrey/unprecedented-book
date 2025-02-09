<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

class DonorProfileQuery
{
    public const string PARAM_CAMPAIGN_TYPE = 'campaign_type';
    public const string PARAM_CHARACTERISTIC = 'characteristic';
    public const string PARAM_GRAPH_TYPE = 'graph_type';
    public const string PARAM_STATE = 'state';

    public function __construct(
        public ?CampaignType $campaignType = null,
        public State $state = State::USA,
        public ?DonorCharacteristic $characteristicA = null,
        public ?DonorCharacteristic $characteristicB = null,
        public GraphType $graphType = GraphType::receipts,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $campaignType = self::initEnum(CampaignType::class, $request);

        if (null === $campaignType) {
            return new self();
        }

        $state = self::initEnum(State::class, $request) ?? State::USA;

        $characteristicA = self::initEnum(DonorCharacteristic::class, $request, 0);
        $characteristicB = self::initEnum(DonorCharacteristic::class, $request, 1);

        if ($characteristicA?->isMutuallyExclusiveOrTautologicalWith($campaignType)) {
            $characteristicA = null;
        }

        if (null === $characteristicA && null !== $characteristicB) {
            $characteristicA = $characteristicB->isMutuallyExclusiveOrTautologicalWith($campaignType)
                ? null
                : $characteristicB;
            $characteristicB = null;
        }

        if (
            $characteristicA?->isMutuallyExclusiveOrTautologicalWith($characteristicB)
            || $characteristicB?->isMutuallyExclusiveOrTautologicalWith($campaignType)
        ) {
            $characteristicB = null;
        }

        $graphType = self::initEnum(GraphType::class, $request) ?? GraphType::amount;

        return new self(
            campaignType: $campaignType,
            state: $state,
            characteristicA: $characteristicA,
            characteristicB: $characteristicB,
            graphType: $graphType
        );
    }

    /**
     * @param class-string<TEnum> $classStr
     *
     * @template TEnum of \BackedEnum
     *
     * @phpstan-return TEnum
     */
    private static function initEnum(string $classStr, Request $request, ?int $index = null): ?\BackedEnum
    {
        $key = match ($classStr) {
            CampaignType::class => self::PARAM_CAMPAIGN_TYPE,
            GraphType::class => self::PARAM_GRAPH_TYPE,
            DonorCharacteristic::class => self::PARAM_CHARACTERISTIC,
            State::class => self::PARAM_STATE,
            default => throw new BookUnexpectedValueException(),
        };

        $value = $request->getQueryParam($key);

        if (null === $index) {
            return $classStr::tryFrom((string) CastingUtilities::toString($value));
        }

        if (!\is_array($value)) {
            return null;
        }

        return $classStr::tryFrom((string) CastingUtilities::toString($value[$index] ?? null));
    }
}
