<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

final readonly class Request
{
    /**
     * @param array<array-key, mixed> $queryParams
     */
    public function __construct(private array $queryParams = [])
    {
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAllQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQueryParams(string $name): mixed
    {
        return $this->queryParams[$name] ?? null;
    }
}
