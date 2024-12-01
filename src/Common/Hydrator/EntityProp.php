<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;

/**
 * @phpstan-type ParsedArray array{keyType: EntityArrayKeyType, valueType: string}
 * @phpstan-type ParsedType array{
 *     type: EntityPropType,
 *     classStr: class-string<Entity|\BackedEnum|\DateTimeImmutable>|null,
 *     keyType: EntityArrayKeyType|null
 * }
 */
final readonly class EntityProp
{
    /**
     * @param class-string<Entity|\BackedEnum|\DateTimeImmutable>|null $classStr
     */
    public function __construct(
        public string $name,
        public EntityPropType $type,
        public int $order = 0,
        public bool $nullable = false,
        public ?string $classStr = null,
        public ?EntityArrayKeyType $keyType = null,
        public ?string $fallback = null,
        public bool $initalized = false,
    ) {
    }

    public static function fromReflectionProp(\ReflectionProperty $reflectionProp): self
    {
        $reflectionType = $reflectionProp->getType();

        if (!$reflectionType instanceof \ReflectionNamedType) {
            throw BookUnexpectedValueException::fromExpectedAndActual(\ReflectionNamedType::class, $reflectionType);
        }

        $parsed = self::parseReflectionType($reflectionType, (string) $reflectionProp->getDocComment());

        $meta = self::getPropMeta($reflectionProp);

        $name = $reflectionProp->getName();

        return new self(
            name: $reflectionProp->getName(),
            type: $parsed['type'],
            order: $meta->order,
            nullable: $reflectionType->allowsNull(),
            classStr: $parsed['classStr'],
            keyType: $parsed['keyType'],
            fallback: $meta->fallback,
            initalized: \array_key_exists($name, $reflectionProp->getDeclaringClass()->getDefaultProperties())
        );
    }

    /**
     * @return ParsedType
     */
    private static function parseReflectionType(\ReflectionNamedType $reflectionType, string $docComment): array
    {
        $classStr = null;
        $keyType = null;
        $type = null;
        $typeName = $reflectionType->getName();

        if ('array' === $typeName) {
            $subTypes = self::getArraySubTypes($docComment);
            $keyType = $subTypes['keyType'];
            $typeName = $subTypes['valueType'];
            $type = EntityPropType::tryFrom($typeName);
        } elseif ($reflectionType->isBuiltin()) {
            $type = EntityPropType::from($typeName);
        }

        if (null === $type) {
            $classStr = self::getFullyQualifiedClassStr($typeName);
            self::assertValidClassStr($classStr);
            $type = EntityPropType::obj;
        }

        return ['type' => $type, 'classStr' => $classStr, 'keyType' => $keyType];
    }

    /**
     * @return ParsedArray
     */
    private static function getArraySubTypes(string $docComment): array
    {
        if (preg_match('/list<(.+)>/', $docComment, $matches)) {
            return ['keyType' => EntityArrayKeyType::int, 'valueType' => $matches[1]];
        }

        if (!preg_match('/array<(.+), (.+)>/', $docComment, $matches)) {
            $msg = \sprintf('Could not extract array sub-types from docComment: %s', $docComment);
            throw new BookRuntimeException($msg);
        }

        if ('int' === $matches[1]) {
            return ['keyType' => EntityArrayKeyType::intAssociative, 'valueType' => $matches[2]];
        }

        return ['keyType' => EntityArrayKeyType::string, 'valueType' => $matches[2]];
    }

    private static function getFullyQualifiedClassStr(string $classStr): string
    {
        if (class_exists($classStr)) {
            return $classStr;
        }

        return ClassAliases::$aliases[$classStr] ?? $classStr;
    }

    /**
     * @phpstan-assert class-string<Entity|\BackedEnum|\DateTimeImmutable> $classStr
     */
    private static function assertValidClassStr(string $classStr): void
    {
        if (\DateTimeImmutable::class === $classStr) {
            return;
        }

        if (!class_exists($classStr)) {
            $msg = \sprintf('Class %s does not exist', $classStr);
            throw new BookUnexpectedValueException($msg);
        }

        if (is_subclass_of($classStr, \BackedEnum::class)) {
            return;
        }

        if (!is_subclass_of($classStr, Entity::class)) {
            $msg = \sprintf('Class %s is not a subtype of %s', $classStr, Entity::class);
            throw new BookUnexpectedValueException($msg);
        }
    }

    private static function getPropMeta(\ReflectionProperty $reflectionProp): PropMeta
    {
        $reflectionAttr = $reflectionProp->getAttributes(PropMeta::class)[0] ?? null;

        if (!$reflectionAttr) {
            return new PropMeta(0);
        }

        return $reflectionAttr->newInstance();
    }

    public function sansKeyType(): self
    {
        return new self(
            name: $this->name,
            type: $this->type,
            order: $this->order,
            nullable: $this->nullable,
            classStr: $this->classStr,
            fallback: $this->fallback,
            initalized: $this->initalized
        );
    }
}
