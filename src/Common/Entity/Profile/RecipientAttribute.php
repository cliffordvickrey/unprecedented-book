<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Enum\PartyType;

#[\Attribute]
class RecipientAttribute
{
    /**
     * @param array<string, mixed> $committeeIds
     */
    public function __construct(public PartyType $party, public array $committeeIds = [])
    {

    }
}