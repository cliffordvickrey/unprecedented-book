<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Http;

use CliffordVickrey\Book2024\App\Controller\CacheController;
use CliffordVickrey\Book2024\App\Controller\ControllerInterface;
use CliffordVickrey\Book2024\App\Controller\ErrorController;
use CliffordVickrey\Book2024\App\Controller\ExportController;
use CliffordVickrey\Book2024\App\Controller\FrequenciesController;
use CliffordVickrey\Book2024\App\Controller\GeoJsonController;
use CliffordVickrey\Book2024\App\Controller\GraphController;
use CliffordVickrey\Book2024\App\Controller\GraphDataController;
use CliffordVickrey\Book2024\App\Controller\MapController;
use CliffordVickrey\Book2024\App\Controller\MapDataController;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

enum Route: string
{
    case cache = 'cache';
    case error = 'error';
    case frequencies = 'frequencies';
    case geoJson = 'geoJson';
    case graph = 'graph';
    case graphData = 'graphData';
    case graphExport = 'graphExport';
    case map = 'map';
    case mapData = 'mapData';
    case mapExport = 'mapExport';

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
     * @return class-string<ControllerInterface>|non-empty-list<class-string<ControllerInterface>>
     */
    public function getControllerOrControllers(): array|string
    {
        return match ($this) {
            self::cache => CacheController::class,
            self::error => ErrorController::class,
            self::frequencies => FrequenciesController::class,
            self::geoJson => GeoJsonController::class,
            self::graph => GraphController::class,
            self::graphData => GraphDataController::class,
            self::graphExport => [GraphDataController::class, ExportController::class],
            self::map => MapController::class,
            self::mapData => MapDataController::class,
            self::mapExport => [MapDataController::class, ExportController::class],
        };
    }
}
