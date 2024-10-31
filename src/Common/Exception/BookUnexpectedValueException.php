<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Exception;

class BookUnexpectedValueException extends \UnexpectedValueException
{
    public static function fromExpectedAndActual(string $expected, mixed $actual): self
    {
        $msg = \sprintf('Expected %s; got %s', $expected, get_debug_type($actual));

        return new self($msg);
    }
}
