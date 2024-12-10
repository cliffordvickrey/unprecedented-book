<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Config\MatchOptions;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class MatchService implements MatchServiceInterface
{
    private MatchOptions $options;

    public function __construct(?MatchOptions $options = null)
    {
        $this->options = $options ?? new MatchOptions();
    }

    public function areSurnamesSimilar(string $a, string $b): bool
    {
        $similar = StringUtilities::similarText($a, $b);

        return $similar >= $this->options->minimumSurnameSimilarity;
    }
}
