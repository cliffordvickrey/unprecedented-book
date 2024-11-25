<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CandidateOffice: string
{
    case H = 'H'; // House
    case P = 'P'; // President
    case S = 'S'; // Senate

    /**
     * @return array<string, string>
     */
    public static function getSlugs(): array
    {
        $cases = self::cases();

        /** @var array<string, string> $slugs */
        $slugs = array_reduce($cases, static fn (array $carry, CandidateOffice $office) => array_merge(
            $carry,
            [$office->getSlug() => $office->value]
        ), []);

        $slugs['leadership'] = 'L';

        return $slugs;
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::H => 'house',
            self::P => 'pres',
            self::S => 'sen',
        };
    }
}
