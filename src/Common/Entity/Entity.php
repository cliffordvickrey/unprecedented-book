<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReport;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReport;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReportRow;
use CliffordVickrey\Book2024\Common\Hydrator\EntityHydrator;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;

abstract class Entity implements \JsonSerializable
{
    private static ?EntityHydrator $hydrator = null;

    final public function __construct()
    {
        self::getHydrator()->initialize($this);
    }

    private static function getHydrator(): EntityHydrator
    {
        self::$hydrator ??= new EntityHydrator();

        return self::$hydrator;
    }

    final public static function create(mixed $value): static
    {
        if (\is_object($value) && is_a($value, static::class)) {
            return $value;
        } elseif (\is_object($value)) {
            $value = (array) $value;
        } elseif (!\is_array($value)) {
            $value = [];
        }

        $staticClass = static::class;

        if (AbstractReport::class === $staticClass) {
            $staticClass = isset($value['totals']) ? DonorReport::class : CampaignReport::class;

            if (CampaignReport::class === $staticClass) {
                // @todo fix
                $staticClass = str_contains(
                    JsonUtilities::jsonEncode($value),
                    'jurisdiction'
                ) ? MapReport::class : CampaignReport::class;
            }
        } elseif (AbstractReportRow::class === $staticClass) {
            $staticClass = match (true) {
                isset($value['characteristic']) => DonorReportRow::class,
                isset($value['jurisdiction']) => MapReportRow::class,
                default => CampaignReportRow::class,
            };
        } elseif (DonorProfileCycle::class === $staticClass) {
            $cycle = CastingUtilities::toInt($value['cycle'] ?? null);

            $staticClass = match ($cycle) {
                2020 => DonorProfileCycle2020::class,
                2024 => DonorProfileCycle2024::class,
                default => DonorProfileCycle2016::class,
            };
        }

        return $staticClass::__set_state($value);
    }

    /**
     * @param array<array-key, mixed> $an_array
     */
    final public static function __set_state(array $an_array): static
    {
        $static = new static();
        self::getHydrator()->hydrate($static, $an_array);

        return $static;
    }

    /**
     * @return list<static>
     */
    final public static function collectList(mixed $value): array
    {
        return array_values(array_map(static::create(...), CastingUtilities::toArray($value)));
    }

    /**
     * @return array<string, mixed>
     */
    final public function toArray(bool $forJson = false): array
    {
        return self::getHydrator()->extract($this, $forJson);
    }

    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return array_keys((new static())->toArray());
    }

    final public static function fromJson(string $json): static
    {
        return static::__set_state(JsonUtilities::jsonDecode($json));
    }

    /**
     * @return array<string, mixed>
     */
    final public function __serialize(): array
    {
        return self::getHydrator()->extract($this);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    final public function __unserialize(array $data): void
    {
        self::getHydrator()->hydrate($this, $data);
    }

    /**
     * @return array<string, mixed>
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray(true);
    }

    public function __clone()
    {
        $props = $this->toArray();

        foreach ($props as $prop => $value) {
            if (self::isCloneable($value)) {
                $this->{$prop} = clone $value;
                continue;
            }

            if (!\is_array($value)) {
                continue;
            }

            $arrayOfObjects = array_filter($value, self::isCloneable(...));

            foreach ($arrayOfObjects as $nestedProp => $nestedValue) {
                $value[$nestedProp] = clone $nestedValue;
            }

            $this->{$prop} = $value;
        }
    }

    /**
     * @phpstan-assert-if-true object $val
     */
    private static function isCloneable(mixed $val): bool
    {
        return \is_object($val) && !$val instanceof \BackedEnum;
    }
}
