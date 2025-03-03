<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\DTO\GraphData;
use CliffordVickrey\Book2024\App\DTO\GraphDataPoint;
use CliffordVickrey\Book2024\App\DTO\MapData;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type CsvRows list<list<float|int|string|null>>
 */
class ExportController extends AbstractController
{
    public function dispatch(Request $request): Response
    {
        $response = $request->getResponse();
        Assert::notEmpty($response);

        $graphData = $response->getObject(\JsonSerializable::class);

        if (!$graphData instanceof GraphData && !$graphData instanceof MapData) {
            throw BookUnexpectedValueException::fromExpectedAndActual(\sprintf('either %s or %s', GraphData::class, MapData::class), $graphData);
        }

        $query = DonorProfileQuery::fromRequest($request);

        $rows = $this->buildRows($query, $graphData);
        $csv = $this->buildCsv($rows);

        $response->setFilename(self::getFilename($query, $graphData));
        $response->setObject(ContentType::csv);
        $response->setResource($csv);

        return $response;
    }

    /**
     * @return CsvRows
     */
    private function buildRows(DonorProfileQuery $query, GraphData|MapData $graphData): array
    {
        $headers = [
            'campaign',
            'state',
            'characteristic_a',
            'characteristic_b',
        ];

        if ($graphData instanceof GraphData) {
            $headers[] = 'date';
        } else {
            $headers[] = 'jurisdiction';
        }

        $headers[] = $graphData->graphType->value;

        $prototype = [
            $query->campaignType?->value,
            $query->state->value,
            $query->characteristicA?->value,
            $query->characteristicB?->value,
        ];

        $rows = [$headers];

        foreach ($graphData as $dataPoint) {
            $row = $prototype;

            if ($dataPoint instanceof GraphDataPoint) {
                $row[] = $dataPoint->date->format('Y-m-d');
            } else {
                $row[] = $dataPoint->jurisdiction;
            }

            $row[] = $dataPoint->value;
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param CsvRows $rows
     *
     * @return resource
     */
    private function buildCsv(array $rows)
    {
        $writer = new CsvWriter('php://temp', 'r+');
        array_walk($rows, static fn ($row) => $writer->write($row));
        $resource = $writer->detach();
        Assert::notFalse(rewind($resource));

        return $resource;
    }

    private static function getFilename(DonorProfileQuery $query, GraphData|MapData $graphData): string
    {
        $filenameParts = array_filter([
            $graphData instanceof GraphData ? 'graph' : 'map',
            $query->campaignType?->value,
            $query->state->value,
            $query->characteristicA?->value,
            $query->characteristicB?->value,
        ]);

        return \sprintf('%s.csv', implode('_', $filenameParts));
    }
}
