<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

enum CampaignType: string
{
    case joe_biden = 'joe_biden';
    case kamala_harris = 'kamala_harris';
    case donald_trump = 'donald_trump';

    public function getParty(): PartyType
    {
        if (self::donald_trump === $this) {
            return PartyType::republican;
        }

        return PartyType::democratic;
    }

    /**
     * @return array<string, string>
     */
    public static function getDescriptions(): array
    {
        /** @var array<string, string> $arr */
        $arr = array_reduce(self::cases(), static fn (array $carry, self $campaignType): array => array_merge(
            $carry,
            [$campaignType->value => $campaignType->getDescription()]
        ), []);

        return $arr;
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::joe_biden => 'Joe Biden',
            self::kamala_harris => 'Kamala Harris',
            self::donald_trump => 'Donald Trump',
        };
    }

    public function toProp(): string
    {
        return match ($this) {
            self::joe_biden => 'presJoeBiden',
            self::kamala_harris => 'presKamalaHarris',
            self::donald_trump => 'presDonaldTrump',
        };
    }

    public function getLaunchDate(): \DateTimeImmutable
    {
        $dtStr = match ($this) {
            CampaignType::joe_biden => '2023-04-25',
            CampaignType::kamala_harris => '2024-07-21',
            CampaignType::donald_trump => '2022-11-15',
        };

        return CastingUtilities::toDateTime($dtStr) ?? throw new BookRuntimeException();
    }

    public function getDropoutDate(): \DateTimeImmutable
    {
        $dtStr = match ($this) {
            CampaignType::joe_biden => '2024-07-20',
            CampaignType::kamala_harris, CampaignType::donald_trump => '2024-10-16', // @todo rest of election
        };

        return CastingUtilities::toDateTime($dtStr) ?? throw new BookRuntimeException();
    }
}
