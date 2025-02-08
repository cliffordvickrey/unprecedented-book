<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

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
}
