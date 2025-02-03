<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\DTO;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\PacType;

class ReceiptAnalysis
{
    private const string PROP_BIDEN = 'presJoeBiden';
    private const string PROP_HARRIS = 'presKamalaHarris';
    private const string PROP_TRUMP = 'presDonaldTrump';

    public function __construct(
        public \DateTimeImmutable $date,
        public float $amount,
        public int $cycle,
        public CommitteeAggregate $committee,
        public ?CandidateAggregate $candidate = null,
        public ?PacType $pacType = null,
        public ?string $prop = null,
        public bool $isWeekOneLaunch = false,
        public bool $isDayOneLaunch = false,
    ) {
    }

    public function getCampaignType(?int $cycle = null): ?CampaignType
    {
        if (null === $this->prop) {
            return null;
        }

        if (null !== $cycle && $this->cycle != $cycle) {
            return null;
        }

        return match ($this->prop) {
            self::PROP_BIDEN => CampaignType::joe_biden,
            self::PROP_HARRIS => CampaignType::kamala_harris,
            self::PROP_TRUMP => CampaignType::donald_trump,
            default => null,
        };
    }
}
