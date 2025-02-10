<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGrid;
use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Repository\DonorReportRepository;
use CliffordVickrey\Book2024\Common\Repository\ReportRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class FrequenciesController implements ControllerInterface
{
    /** @var ReportRepositoryInterface<DonorReport> */
    private ReportRepositoryInterface $repository;

    /**
     * @param ReportRepositoryInterface<DonorReport>|null $repository
     */
    public function __construct(?ReportRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new DonorReportRepository();
    }

    public function dispatch(Request $request): Response
    {
        $response = new Response();

        $query = self::initQuery($request, $response);

        if (null === $query->campaignType) {
            return $response;
        }

        $this->initGrids($response);

        return $response;
    }

    private static function initQuery(Request $request, Response $response): DonorProfileQuery
    {
        $query = DonorProfileQuery::fromRequest($request);
        $response->setObject($query);

        return $query;
    }

    private function initGrids(Response $response): void
    {
        $grids = DonorProfileGrid::collectChildren();

        $query = $response->getObject(DonorProfileQuery::class);

        array_walk($grids, function (DonorProfileGrid $grid) use ($query, $response): void {
            $campaignType = $query->campaignType;
            Assert::notNull($campaignType);

            $report = $this->repository->get(
                $campaignType,
                $query->state,
                $query->characteristicA,
                $query->characteristicB
            );

            $grid->setReport($report);

            $response->setObject($grid);
        });
    }
}
