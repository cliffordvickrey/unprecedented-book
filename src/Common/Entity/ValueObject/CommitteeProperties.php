<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;

class CommitteeProperties extends Entity
{
    public ?CommitteeDesignation $committeeDesignation = null;
    public ?string $candidateSlug = null;
    public bool $isLeadership = false;
    public ?CandidateOffice $candidateOffice = null;
    public ?string $state = null;
    public ?string $district = null;
}
