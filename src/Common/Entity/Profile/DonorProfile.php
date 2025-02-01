<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Enum\RecipientType;
use CliffordVickrey\Book2024\Common\Enum\State;

class DonorProfile extends Entity
{
    public ?State $state = null;
    /** @var array<string, DonorProfileCampaign> */
    public array $candidates = [];
    /** @var array<int, DonorProfileCycle> */
    public array $cycles = [];

    public static function build(): self
    {
        $self = new self();
        $self->clearState();
        return $self;
    }

    public function clearState(): void
    {
        $this->state = null;

        $this->candidates = [
            RecipientType::biden->value => new DonorProfileCampaign(),
            RecipientType::harris->value => new DonorProfileCampaign(),
            RecipientType::trump->value => new DonorProfileCampaign(),
        ];

        $this->cycles = [
            2016 => new DonorProfileCycle2016(),
            2020 => new DonorProfileCycle2020(),
            2024 => new DonorProfileCycle2024(),
        ];
    }
}
