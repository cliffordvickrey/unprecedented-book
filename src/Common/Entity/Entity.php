<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
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

        if (DonorProfileCycle::class === static::class) {
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
}
