<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;

enum PacType: string
{
    case hybridPac = 'hybridPac';
    case partyCommittee = 'partyCommittee';
    case singleCandidateIndependentExpenditureCommittee = 'singleCandidateIndependentExpenditure';
    case superPac = 'superPac';
    case traditionalPac = 'traditionalPac';

    public static function fromCommitteeType(CommitteeType $committeeType): ?self
    {
        return match ($committeeType) {
            CommitteeType::V, CommitteeType::W => self::hybridPac,
            CommitteeType::X, CommitteeType::Y, CommitteeType::Z => self::partyCommittee,
            CommitteeType::U => self::singleCandidateIndependentExpenditureCommittee,
            CommitteeType::O => self::superPac,
            CommitteeType::N, CommitteeType::Q => self::traditionalPac,
            default => null,
        };
    }
}
