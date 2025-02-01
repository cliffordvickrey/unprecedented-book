<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Enum\PartyType;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

#[\Attribute]
class RecipientAttribute
{
    public ?\DateTimeImmutable $startDate = null;
    public ?\DateTimeImmutable $endDate = null;

    /**
     * @param array<string, mixed> $committeeIds
     */
    public function __construct(
        public ?string $slug = null,
        public PartyType $party = PartyType::other,
        string|\DateTimeImmutable|null $startDate = null,
        string|\DateTimeImmutable|null $endDate = null,
        public array $committeeIds = [],
    ) {
        $this->startDate = $startDate ? CastingUtilities::toDateTime($startDate) : null;
        $this->endDate = $endDate ? CastingUtilities::toDateTime($endDate) : null;
    }
}
