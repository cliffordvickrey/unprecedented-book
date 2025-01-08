<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

class EntityHydrator implements EntityHydratorInterface
{
    /** @var array<class-string<Entity>, list<EntityProp>> */
    private static array $props = [];
    /** @var array<class-string<Entity>, list<EntityProp>> */
    private static array $propsToInitialize = [];

    public function hydrate(Entity $entity, array $data): void
    {
        $props = self::getProps($entity);

        foreach ($props as $prop) {
            $propName = $prop->name;

            if (null !== $prop->fallback && empty($data[$propName])) {
                $propName = $prop->fallback;
            }

            if (!\array_key_exists($propName, $data)) {
                continue;
            }

            self::set($entity, $prop, $data[$propName]);
        }
    }

    /**
     * @return list<EntityProp>
     */
    private static function getProps(Entity $entity): array
    {
        if (isset(self::$props[$entity::class])) {
            return self::$props[$entity::class];
        }

        $props = self::buildProps($entity);
        self::$props[$entity::class] = $props;

        return $props;
    }

    /**
     * @return list<EntityProp>
     */
    private static function buildProps(Entity $entity): array
    {
        $reflectionObj = new \ReflectionObject($entity);

        $reflectionProps = $reflectionObj->getProperties(\ReflectionProperty::IS_PUBLIC);

        $props = array_map(
            static fn (\ReflectionProperty $reflectionProp) => EntityProp::fromReflectionProp($reflectionProp),
            $reflectionProps
        );

        usort($props, static fn (EntityProp $propA, EntityProp $propB) => $propA->order <=> $propB->order);

        return $props;
    }

    private static function set(Entity $entity, EntityProp $prop, mixed $value = null): void
    {
        $entity->{$prop->name} = self::parseValue($value, $prop);
    }

    private static function parseValue(mixed $value, EntityProp $prop): mixed
    {
        if ($prop->keyType) {
            return self::parseArray($value, $prop);
        }

        return match ($prop->type) {
            EntityPropType::bool => self::parseBool($value, $prop),
            EntityPropType::float => self::parseFloat($value, $prop),
            EntityPropType::int => self::parseInt($value, $prop),
            EntityPropType::obj => self::parseObject($value, $prop),
            EntityPropType::string => self::parseString($value, $prop),
        };
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private static function parseArray(mixed $value, EntityProp $prop): ?array
    {
        if (!\is_array($value)) {
            return $prop->nullable ? null : [];
        }

        $parsed = [];

        $propSansKeyType = $prop->sansKeyType();

        foreach ($value as $k => $v) {
            $parsed[$k] = self::parseValue($v, $propSansKeyType);
        }

        return $parsed;
    }

    private static function parseBool(mixed $value, EntityProp $prop): ?bool
    {
        if (null === $value && $prop->nullable) {
            return null;
        }

        return (bool) $value;
    }

    private static function parseFloat(mixed $value, EntityProp $prop): ?float
    {
        $parsed = CastingUtilities::toFloat($value);

        if (null !== $parsed || $prop->nullable) {
            return $parsed;
        }

        return 0.0;
    }

    private static function parseInt(mixed $value, EntityProp $prop): ?int
    {
        $parsed = CastingUtilities::toInt($value);

        if (null !== $parsed || $prop->nullable) {
            return $parsed;
        }

        return 0;
    }

    private static function parseObject(mixed $value, EntityProp $prop): ?object
    {
        $classStr = (string) $prop->classStr;

        if (\DateTimeImmutable::class === $classStr) {
            $obj = CastingUtilities::toDateTime($value);
        } elseif (is_subclass_of($classStr, \BackedEnum::class)) {
            $obj = CastingUtilities::toEnum($value, $classStr);
        } else {
            /** @var class-string<Entity> $classStr */
            $obj = CastingUtilities::toEntity($value, $classStr);
        }

        if ($obj || $prop->nullable) {
            return $obj;
        }

        if (\DateTimeImmutable::class === $classStr) {
            return new \DateTimeImmutable();
        } elseif (is_subclass_of($classStr, \BackedEnum::class)) {
            $cases = $classStr::cases();

            return $cases[0];
        }

        return new $classStr();
    }

    private static function parseString(mixed $value, EntityProp $prop): ?string
    {
        $parsed = CastingUtilities::toString($value);

        if (null !== $parsed || $prop->nullable) {
            return $parsed;
        }

        return '';
    }

    public function extract(Entity $entity, bool $forJson = false): array
    {
        $props = self::getProps($entity);

        $extracted = [];

        foreach ($props as $prop) {
            $value = $entity->{$prop->name};

            if (!$forJson) {
                $extracted[$prop->name] = $value;
                continue;
            }

            if ($value instanceof \DateTimeImmutable) {
                $value = $value->format('Y-m-d');
            } elseif ($value instanceof \BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof Entity) {
                $value = $this->extract($value, true);
            }

            $extracted[$prop->name] = $value;
        }

        return $extracted;
    }

    public function initialize(Entity $entity): void
    {
        $propsToInitialize = self::getPropsToInitialize($entity);

        array_walk($propsToInitialize, static fn (EntityProp $prop) => self::set($entity, $prop));
    }

    /**
     * @return list<EntityProp>
     */
    private static function getPropsToInitialize(Entity $entity): array
    {
        if (isset(self::$propsToInitialize[$entity::class])) {
            return self::$propsToInitialize[$entity::class];
        }

        $props = self::buildPropsToInitialize($entity);
        self::$propsToInitialize[$entity::class] = $props;

        return $props;
    }

    /**
     * @return list<EntityProp>
     */
    private static function buildPropsToInitialize(Entity $entity): array
    {
        $props = self::getProps($entity);

        return array_values(array_filter(
            $props,
            static fn (EntityProp $prop) => $prop->classStr && !$prop->initalized
        ));
    }
}
