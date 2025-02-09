<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\DTO\GraphData;
use CliffordVickrey\Book2024\App\DTO\GraphDataPoint;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use Faker\Factory;
use Faker\Generator;

final class GraphDataController implements ControllerInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function dispatch(Request $request): Response
    {
        $query = DonorProfileQuery::fromRequest($request);

        return $this->buildResponse($query);
    }

    private function buildResponse(DonorProfileQuery $query): Response
    {
        $response = new Response();
        $response->setObject(ContentType::json);
        $response->setObject($this->buildGraphData($query), \JsonSerializable::class);

        return $response;
    }

    private function buildGraphData(DonorProfileQuery $query): GraphData
    {
        $graphData = new GraphData($query->graphType);
        $campaignType = $query->campaignType;

        if (!$campaignType) {
            return $graphData;
        }

        $range = DateUtilities::getDateRanges($campaignType->getLaunchDate(), $campaignType->getDropoutDate());

        $isDollarAmount = $query->graphType->isDollarAmount();

        foreach ($range as $dt) {
            $amt = $this->faker->randomFloat(2, 0, 1000000);

            if (!$isDollarAmount) {
                $amt = (int) $amt;
            }

            $graphData[] = new GraphDataPoint($dt, $amt);
        }

        return $graphData;
    }
}
