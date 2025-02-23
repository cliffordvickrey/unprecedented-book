<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\View;

use CliffordVickrey\Book2024\App\Contract\AbstractCollection;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;

/**
 * @extends AbstractCollection<string, list<string>>
 */
class AssetUris extends AbstractCollection
{
    public static function build(): self
    {
        $distFilenames = FileUtilities::glob(__DIR__.'/../../../public/dist/*.{js,css}', true);

        $self = new self();

        foreach ($distFilenames as $distFilename) {
            $ext = pathinfo($distFilename, \PATHINFO_EXTENSION);
            $basename = basename($distFilename, ".$ext");

            $parts = explode('.', $basename);
            array_pop($parts);

            $key = \sprintf('%s.%s', implode('.', $parts), $ext);

            if (!isset($self[$key])) {
                $self[$key] = [];
            }

            $self[$key][] = "dist/$basename.$ext";
        }

        return $self;
    }
}
