<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

enum EntityPropType: string
{
    case bool = 'bool';
    case float = 'float';
    case int = 'int';
    case obj = 'obj';
    case string = 'string';
}
