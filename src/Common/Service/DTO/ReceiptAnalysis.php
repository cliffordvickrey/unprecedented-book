<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\DTO;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Enum\RecipientType;

class ReceiptAnalysis
{
    private const string PROP_BIDEN = 'presJoeBiden';
    private const string PROP_HARRIS = 'presKamalaHarris';
    private const string PROP_TRUMP = 'presDonaldTrump';

    public function __construct(
        public \DateTimeImmutable  $date,
        public float               $amount,
        public int                 $cycle,
        public CommitteeAggregate  $committee,
        public ?CandidateAggregate $candidate,
        public ?string             $prop = null
    )
    {
    }

    public function getRecipient(): ?RecipientType
    {
        if (null === $this->prop || $this->cycle <> 2024) {
            return null;
        }

        return match ($this->prop) {
            self::PROP_BIDEN => RecipientType::biden,
            self::PROP_HARRIS => RecipientType::harris,
            self::PROP_TRUMP => RecipientType::trump,
            default => null,
        };
    }
}