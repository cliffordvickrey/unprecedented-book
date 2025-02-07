<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Error;

class ErrorHandler
{
    /**
     * @throws \ErrorException
     */
    public static function handleError(int $severity, string $message, ?string $file, ?int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
