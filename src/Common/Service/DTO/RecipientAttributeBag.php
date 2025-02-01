<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\DTO;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\RecipientAttribute;

/**
 * @implements \IteratorAggregate<string, RecipientAttribute>
 */
readonly class RecipientAttributeBag implements \IteratorAggregate
{
    /** @var array<string, RecipientAttribute> */
    public array $recipientAttributesByCandidateSlug;

    /**
     * @param array<string, RecipientAttribute> $recipientAttributes
     */
    public function __construct(public array $recipientAttributes)
    {
        $this->recipientAttributesByCandidateSlug = array_reduce(
            $this->recipientAttributes,
            fn (array $carry, RecipientAttribute $attribute) => array_merge($carry, [$attribute->slug => $attribute]),
            []
        );
    }

    /**
     * @return \ArrayIterator<string, RecipientAttribute>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->recipientAttributes);
    }
}
