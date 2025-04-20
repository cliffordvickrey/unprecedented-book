<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Geo;

enum ZipCodeType: string
{
    case PO_Box = 'PO Box';
    case Standard = 'Standard';
}
