<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

use CliffordVickrey\Book2024\App\Controller\ControllerInterface;
use CliffordVickrey\Book2024\App\Controller\ErrorController;
use CliffordVickrey\Book2024\App\Controller\FrequenciesController;
use CliffordVickrey\Book2024\App\Controller\GraphController;
use CliffordVickrey\Book2024\App\Controller\GraphDataController;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

enum Route: string
{
    case error = 'error';
    case frequencies = 'frequencies';
    case graph = 'graph';
    case graphData = 'graphData';

    public static function fromRequest(Request $request): self
    {
        return self::fromAnything($request->getQueryParam('action'));
    }

    public static function fromAnything(mixed $value): self
    {
        return CastingUtilities::toEnum($value, self::class) ?? self::getIndexAction();
    }

    public static function getIndexAction(): self
    {
        return self::frequencies;
    }

    /**
     * @return class-string<ControllerInterface>
     */
    public function getControllerClassStr(): string
    {
        return match ($this) {
            self::error => ErrorController::class,
            self::frequencies => FrequenciesController::class,
            self::graph => GraphController::class,
            self::graphData => GraphDataController::class,
        };
    }
}
