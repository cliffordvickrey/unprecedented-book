<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;

enum PartyType: string
{
    case democratic = 'democratic';
    case republican = 'republican';
    case thirdParty = 'thirdParty';

    public static function fromCandidateInfo(Candidate $candidateInfo): PartyType
    {
        $isDemocratic = (bool) $candidateInfo->CAND_PTY_AFFILIATION?->isDemocratic();
        $isRepublican = !$isDemocratic && $candidateInfo->CAND_PTY_AFFILIATION?->isRepublican();

        if ($isDemocratic) {
            return self::democratic;
        } elseif ($isRepublican) {
            return self::republican;
        }

        return self::thirdParty;
    }

    public function toCode(): string
    {
        return strtoupper(substr($this->value, 0, 1));
    }
}
