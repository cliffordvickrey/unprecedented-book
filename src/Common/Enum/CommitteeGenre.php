<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;

enum CommitteeGenre: string
{
    case cand = 'cand'; // candidate
    case pac = 'pac';   // PAC

    public static function fromSlug(string $slug): self
    {
        /** @phpstan-var array<string, string> $officeSlugs */
        static $officeSlugs = CandidateOffice::getSlugs();

        $parts = explode('-', $slug);

        $officeParts = explode('_', $parts[1] ?? '');

        if (
            isset($officeSlugs[$officeParts[0]])
            || 'donald_trump' === $parts[0]
            || 'kamala_harris' === $parts[0]
        ) {
            return self::cand;
        }

        return self::pac;
    }
}
