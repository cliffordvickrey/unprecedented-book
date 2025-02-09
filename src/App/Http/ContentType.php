<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

enum ContentType: string
{
    case csv = 'csv';
    case json = 'json';
    case html = 'html';

    public function toHeaderValue(): string
    {
        return match ($this) {
            self::csv => 'text/csv; charset=utf-8',
            self::json => 'application/json; charset=utf-8',
            self::html => 'html',
        };
    }
}
