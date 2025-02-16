<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\DTO\GraphColor;
use CliffordVickrey\Book2024\App\DTO\GraphData;
use CliffordVickrey\Book2024\App\DTO\GraphDataPoint;
use CliffordVickrey\Book2024\App\DTO\GraphType;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReportRow;
use CliffordVickrey\Book2024\Common\Repository\CampaignReportRepository;
use CliffordVickrey\Book2024\Common\Repository\ReportRepositoryInterface;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;

final class GraphDataController implements ControllerInterface
{
    /** @var ReportRepositoryInterface<CampaignReport> */
    private ReportRepositoryInterface $repository;

    /**
     * @param ReportRepositoryInterface<CampaignReport>|null $repository
     */
    public function __construct(?ReportRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new CampaignReportRepository();
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
        $campaignType = $query->campaignType;
        $graphData = new GraphData($query->graphType, GraphColor::fromCampaign($campaignType));

        if (!$campaignType) {
            return $graphData;
        }

        $range = DateUtilities::getDateRanges($campaignType->getLaunchDate(), $campaignType->getDropoutDate());

        $report = $this->repository->get(
            campaignType: $campaignType,
            state: $query->state,
            characteristicA: $query->characteristicA,
            characteristicB: $query->characteristicB
        );

        foreach ($range as $dt) {
            $row = $report->hasByDate($dt) ? $report->getByDate($dt) : new CampaignReportRow();
            $valueObj = $row->value;

            $value = match ($query->graphType) {
                GraphType::amount => $valueObj->amount,
                GraphType::donors => $valueObj->donors,
                GraphType::receipts => $valueObj->receipts,
            };

            $graphData[] = new GraphDataPoint($dt, $value);
        }

        return $graphData;
    }
}
