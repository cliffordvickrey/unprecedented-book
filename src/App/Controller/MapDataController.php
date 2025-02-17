<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\DTO\GraphColor;
use CliffordVickrey\Book2024\App\DTO\GraphType;
use CliffordVickrey\Book2024\App\DTO\MapData;
use CliffordVickrey\Book2024\App\DTO\MapDataPoint;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReport;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Repository\MapReportRepository;
use CliffordVickrey\Book2024\Common\Repository\ReportRepositoryInterface;

final class MapDataController implements ControllerInterface
{
    /** @var ReportRepositoryInterface<MapReport> */
    private ReportRepositoryInterface $repository;

    /**
     * @param ReportRepositoryInterface<MapReport>|null $repository
     */
    public function __construct(?ReportRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new MapReportRepository();
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

        $report = $this->repository->get(
            campaignType: $campaignType,
            state: $query->state,
            characteristicA: $query->characteristicA,
            characteristicB: $query->characteristicB
        );

        $oppositionReports = GraphType::percent === $query->graphType ? $this->getOppositionReports($query) : [];

        foreach ($report as $row) {
            $valueObj = $row->value;

            $total = 0;

            if (0 !== \count($oppositionReports)) {
                $total = $valueObj->donors + array_reduce(
                    $oppositionReports,
                    function (int $carry, MapReport $report) use ($row) {
                        if (!$report->hasByJurisdiction($row->jurisdiction)) {
                            return $carry;
                        }

                        return $carry + $report->getByJurisdiction($row->jurisdiction)->value->donors;
                    },
                    0
                );

                if (0 === $total) {
                    continue;
                }
            }

            $value = match ($query->graphType) {
                GraphType::amount => $valueObj->amount,
                GraphType::donors => $valueObj->donors,
                GraphType::receipts => $valueObj->receipts,
                GraphType::percent => round(($valueObj->donors / $total) * 100, 2),
            };

            $mapData[] = new MapDataPoint($row->jurisdiction, $value);
        }

        return $mapData;
    }

    /**
     * @return list<MapReport>
     */
    private function getOppositionReports(DonorProfileQuery $query): array
    {
        if (CampaignType::donald_trump === $query->campaignType) {
            return [
                $this->repository->get(
                    campaignType: CampaignType::joe_biden,
                    state: $query->state,
                    characteristicA: $query->characteristicA,
                    characteristicB: $query->characteristicB
                ),
                $this->repository->get(
                    campaignType: CampaignType::kamala_harris,
                    state: $query->state,
                    characteristicA: $query->characteristicA,
                    characteristicB: $query->characteristicB
                ),
            ];
        }

        return [$this->repository->get(
            state: $query->state,
            characteristicA: $query->characteristicA,
            characteristicB: $query->characteristicB
        )];
    }
}
