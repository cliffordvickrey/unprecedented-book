<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum DonorCharacteristicGenre: string
{
    case donor = 'donor';
    case cycle_2016 = 'cycle_2016';
    case cycle_2020 = 'cycle_2020';
    case cycle_2024 = 'cycle_2024';

    public static function fromCharacteristic(DonorCharacteristic $characteristic): self
    {
        if (preg_match('/^cycle_\d{4}/', $characteristic->value, $match)) {
            return self::from($match[0]);
        }

        return self::donor;
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::donor => 'Donor Characteristics',
            self::cycle_2016 => '2016 Election Cycle Behavior',
            self::cycle_2020 => '2020 Election Cycle Behavior',
            self::cycle_2024 => '2024 Election Cycle Behavior',
        };
    }
}
