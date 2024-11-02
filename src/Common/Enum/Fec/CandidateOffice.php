<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CandidateOffice: string
{
    case H = 'H'; // House
    case P = 'P'; // President
    case S = 'S'; // Senate

    public function getSlug(): string
    {
        return match ($this) {
            self::H => 'house',
            self::P => 'pres',
            self::S => 'sen',
        };
    }
}
