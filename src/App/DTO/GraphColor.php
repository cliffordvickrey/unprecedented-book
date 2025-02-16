<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

use CliffordVickrey\Book2024\Common\Enum\CampaignType;

enum GraphColor: string
{
    case red = 'red';
    case blue = 'blue';

    public static function fromCampaign(?CampaignType $campaign = null): self
    {
        return CampaignType::donald_trump === $campaign ? self::red : self::blue;
    }
}
