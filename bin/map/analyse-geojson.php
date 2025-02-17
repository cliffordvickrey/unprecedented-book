#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $geoJsonFiles = array_values(array_filter(
        FileUtilities::glob(__DIR__.'/../../web-data/geojson/*.json'),
        fn ($filename) => 'usa' !== basename($filename, '.json')
    ));

    $geoJsonMeta = array_map(function (string $geoJsonFile): array {
        $state = strtoupper(basename($geoJsonFile, '.json'));

        $coors = [];

        /** @var array<array-key, mixed> $geoJson */
        $geoJson = JsonUtilities::jsonDecode(FileUtilities::getContents($geoJsonFile));

        $features = $geoJson['features'] ?? null;

        Assert::isArray($features);

        $features = array_filter($features, is_array(...));

        $zctas = [];

        foreach ($features as $feature) {
            $properties = $feature['properties'] ?? null;

            if (is_array($properties)) {
                $zcta = $properties['ZCTA5CE20'] ?? null;

                if (is_numeric($zcta)) {
                    $zctas[] = (int) $zcta;
                }
            }

            $geometry = $feature['geometry'] ?? null;

            if (!is_array($geometry)) {
                continue;
            }

            $coordinates = $geometry['coordinates'] ?? null;

            if (!is_array($coordinates)) {
                continue;
            }

            $type = $geometry['type'] ?? null;

            if ('Polygon' === $type) {
                $coordinatesInPolygon = $coordinates[0] ?? null;
                Assert::isArray($coordinatesInPolygon);
                $coordinatesInPolygon = array_filter(
                    $coordinatesInPolygon,
                    is_array(...)
                );
                $coors = [...$coors, ...array_map(coordinates(...), $coordinatesInPolygon)];
                continue;
            }

            if ('MultiPolygon' !== $type) {
                continue;
            }

            foreach ($coordinates as $polygon) {
                Assert::isArray($polygon);
                $coordinatesInPolygon = $polygon[0] ?? null;
                Assert::isArray($coordinatesInPolygon);
                $coordinatesInPolygon = array_filter(
                    $coordinatesInPolygon,
                    is_array(...)
                );
                $coors = [...$coors, ...array_map(coordinates(...), $coordinatesInPolygon)];
            }
        }

        Assert::notEmpty($coors);
        Assert::notEmpty($zctas);

        $lats = array_map(static fn ($c) => $c['lat'], $coors);
        $lons = array_map(static fn ($c) => $c['lon'], $coors);

        $lat1 = min($lats);
        $lon1 = min($lons);

        $lat2 = max($lats);
        $lon2 = max($lons);

        $diameter = MathUtilities::haversineDistance($lat1, $lon1, $lat2, $lon2);
        $midpoint = MathUtilities::midpoint($lat1, $lon1, $lat2, $lon2);

        printf('%s%s', str_repeat('-', 80), \PHP_EOL);
        printf('Diameter of %s is %s meters%s', $state, StringUtilities::numberFormat($diameter), \PHP_EOL);
        printf('Midpoint of %s is [%g, %g]%s', $state, $midpoint['lat'], $midpoint['lon'], \PHP_EOL);

        return [
            'state' => $state,
            'stateName' => State::from($state)->getDescription(),
            'diameter' => $diameter,
            'midpoint' => $midpoint,
            'minZcta' => sprintf('%05d', min($zctas)),
            'maxZcta' => sprintf('%05d', max($zctas)),
        ];
    }, $geoJsonFiles);

    $geoJsonMetaJson = JsonUtilities::jsonEncode($geoJsonMeta, true);

    FileUtilities::saveContents(__DIR__.'/../../web-data/geojson-meta/geojson-meta.json', $geoJsonMetaJson);
});

/**
 * @param array<array-key, mixed> $coordinates
 *
 * @return array{lat: float, lon: float}
 */
function coordinates(array $coordinates): array
{
    [$lon, $lat] = $coordinates;

    Assert::numeric($lat);
    Assert::numeric($lon);

    return ['lat' => (float) $lat, 'lon' => (float) $lon];
}
