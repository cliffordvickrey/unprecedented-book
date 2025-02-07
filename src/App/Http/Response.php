<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

use CliffordVickrey\Book2024\App\Contract\AbstractCollection;
use Webmozart\Assert\Assert;

/**
 * @extends AbstractCollection<string, mixed>
 */
final class Response extends AbstractCollection
{
    public const string ATTR_CONTENT = 'content';
    public const string ATTR_JS = 'js';
    public const string ATTR_LAYOUT = 'layout';
    public const string ATTR_PAGE = 'page';

    public function __construct()
    {
        $this->data = [self::ATTR_JS => false, self::ATTR_LAYOUT => true];
    }

    public function setObject(object $obj, ?string $key = null): void
    {
        $key ??= $obj::class;
        $this[$key] = $obj;
    }

    /**
     * @template T
     *
     * @phpstan-param T $type
     *
     * @phpstan-return (T is array ? array<array-key, mixed> : (T is bool ? bool : (T is float ? float : (T is int ? int : string))))
     */
    public function getAttribute(string $key, mixed $type): mixed
    {
        $attrib = $this->getAttributeNullable($key, $type);

        Assert::notNull($attrib);

        return $attrib;
    }

    /**
     * @template T
     *
     * @phpstan-param T $type
     *
     * @phpstan-return (T is array ? array<array-key, mixed>|null : (T is bool ? bool|null : (T is float ? float|null : (T is int ? int|null : string|null))))
     */
    public function getAttributeNullable(string $key, mixed $type): mixed
    {
        $attrib = $this->get($key);

        if (\gettype($attrib) === \gettype($type)) {
            return $attrib; // @phpstan-ignore-line good enough!!!
        }

        return null;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @phpstan-return T
     */
    public function getObject(string $classname, ?string $key = null): object
    {
        $obj = $this->getObjectNullable($classname, $key);
        Assert::isInstanceOf($obj, $classname);

        return $obj;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $classname
     *
     * @phpstan-return  T|null
     */
    public function getObjectNullable(string $classname, ?string $key = null): ?object
    {
        $obj = $this->get($key ?? $classname);

        if (\is_object($obj) && is_a($obj, $classname)) {
            return $obj;
        }

        return null;
    }
}
