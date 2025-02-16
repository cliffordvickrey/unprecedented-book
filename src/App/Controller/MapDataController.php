<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\DTO\GraphColor;
use CliffordVickrey\Book2024\App\DTO\MapData;
use CliffordVickrey\Book2024\App\DTO\MapDataPoint;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Faker\Factory;

final class MapDataController extends AbstractController
{
    public function dispatch(Request $request): Response
    {
        $query = DonorProfileQuery::fromRequest($request);

        return $this->buildResponse($query);
    }

    private function buildResponse(DonorProfileQuery $query): Response
    {
        $response = new Response();
        $response->setObject(ContentType::json);
        $response->setObject($this->buildMapData($query), \JsonSerializable::class);

        return $response;
    }

    private function buildMapData(DonorProfileQuery $query): MapData
    {
        $campaignType = $query->campaignType;
        $mapData = new MapData($query->graphType, GraphColor::fromCampaign($campaignType));

        if (!$campaignType) {
            return $mapData;
        }

        $jurisdictions = self::getJurisdictions($query->state);

        $isDollarAmount = $query->graphType->isDollarAmount();

        $faker = Factory::create();

        foreach ($jurisdictions as $jurisdiction) {
            $amt = $faker->randomFloat(2, 0, 1000000);

            if (!$isDollarAmount) {
                $amt = (int) $amt;
            }

            $mapData[] = new MapDataPoint($jurisdiction, $amt);
        }

        return $mapData;
    }

    /**
     * @return list<string>
     */
    private static function getJurisdictions(State $state): array
    {
        if (State::USA === $state) {
            return array_values(State::getDescriptions());
        }

        $contents = FileUtilities::getContents(__DIR__.'/../../../web-data/geojson-meta/geojson-meta.json');
        /** @var list<array{state: string, minZcta: numeric-string, maxZcta: numeric-string}> $geoJsonMeta */
        $geoJsonMeta = JsonUtilities::jsonDecode($contents);

        $geoJsonMetaInState = array_filter($geoJsonMeta, fn ($meta) => $meta['state'] === $state->value);

        if (0 === \count($geoJsonMetaInState)) {
            return [];
        }

        $zctas = array_pop($geoJsonMetaInState);

        $min = (int) CastingUtilities::toInt($zctas['minZcta']);
        $max = (int) CastingUtilities::toInt($zctas['maxZcta']);

        return array_map(
            fn ($jurisdiction) => \sprintf('%05d', $jurisdiction),
            range($min, $max)
        );
    }
}
