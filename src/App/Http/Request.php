<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

final readonly class Request
{
    /**
     * @param array<array-key, mixed> $queryParams
     */
    public function __construct(private array $queryParams = [], private string $method = 'GET')
    {
    }

    public function isPost(): bool
    {
        return 'POST' === strtoupper($this->method);
    }

    public static function fromSuperGlobals(): self
    {
        return new self($_GET, CastingUtilities::toString($_SERVER['REQUEST_METHOD']) ?? 'GET');
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAllQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQueryParam(string $name): mixed
    {
        return $this->queryParams[$name] ?? null;
    }
}
