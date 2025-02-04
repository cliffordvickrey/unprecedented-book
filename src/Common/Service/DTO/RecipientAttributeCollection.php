<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\DTO;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\RecipientAttribute;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

/**
 * @implements \ArrayAccess<string, RecipientAttribute>
 * @implements \IteratorAggregate<string, RecipientAttribute>
 */
readonly class RecipientAttributeCollection implements \ArrayAccess, \IteratorAggregate
{
    /** @var array<string, RecipientAttribute> */
    private array $recipientAttributesByCandidateSlug;

    /**
     * @param array<string, RecipientAttribute> $recipientAttributes
     */
    public function __construct(public array $recipientAttributes)
    {
        /** @var array<string, RecipientAttribute> $attrs */
        $attrs = array_reduce(
            $this->recipientAttributes,
            fn (array $carry, RecipientAttribute $attribute) => array_merge($carry, [$attribute->slug => $attribute]),
            []
        );

        $this->recipientAttributesByCandidateSlug = $attrs;
    }

    public function getAttributeByCandidateSlug(string $slug): ?RecipientAttribute
    {
        return $this->recipientAttributesByCandidateSlug[$slug] ?? null;
    }

    /**
     * @return \ArrayIterator<string, RecipientAttribute>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->recipientAttributes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->recipientAttributes[CastingUtilities::toString($offset)]);
    }

    public function offsetGet(mixed $offset): RecipientAttribute
    {
        $offset = CastingUtilities::toString($offset);

        return $this->recipientAttributes[$offset]
            ?? throw new BookOutOfBoundsException(\sprintf('Illegal offset, %s', $offset));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException();
    }
}
