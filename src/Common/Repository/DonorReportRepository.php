<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportCollection;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Exception\DonorReportDoesNotExistException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;

final readonly class DonorReportRepository implements DonorReportRepositoryInterface
{
    public function __construct(
        private string $path = __DIR__.'/../../../web-data/donors',
        private bool $prettyPrint = false,
    ) {
    }

    public function get(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): DonorReport {
        self::validateCharacteristics($characteristicA, $characteristicB);

        return $this->getCollection($campaignType, $state, $characteristicA)->getByCharacteristic($characteristicB);
    }

    private static function validateCharacteristics(
        ?DonorCharacteristic &$characteristicA,
        ?DonorCharacteristic &$characteristicB,
    ): void {
        if (null !== $characteristicB && null === $characteristicA) {
            $characteristicA = $characteristicB;
            $characteristicB = null;
        }

        if ($characteristicA?->isMutuallyExclusive($characteristicB)) {
            $msg = \sprintf(
                '%s and %s are mutually exclusive donor categories',
                $characteristicA->value,
                $characteristicB?->value
            );
            throw new BookUnexpectedValueException($msg);
        }
    }

    /**
     * @throws DonorReportDoesNotExistException
     */
    private function getCollection(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
    ): DonorReportCollection {
        $filename = $this->getFilename(DonorReport::inflectForKey(
            campaignType: $campaignType,
            state: $state,
            characteristicA: $characteristicA
        ));

        if (!is_file($filename)) {
            throw new DonorReportDoesNotExistException(\sprintf('Donor report "%s" does not exist', $filename));
        }

        $contents = FileUtilities::getContents($filename);

        return self::unmarshallCollection($contents);
    }

    private function getFilename(DonorReportCollection|DonorReport|string $report): string
    {
        $key = \is_object($report) ? $report->getKey() : $report;

        $parts = explode('-', $key, 4);

        if (\count($parts) > 3) {
            array_pop($parts);
        }

        return \sprintf('%s/%s.json', $this->path, implode(\DIRECTORY_SEPARATOR, $parts));
    }

    private static function unmarshallCollection(string $json): DonorReportCollection
    {
        $json = JsonUtilities::jsonDecode($json);

        return DonorReportCollection::__set_state($json);
    }

    public function save(DonorReport $report): void
    {
        $report->setPercentages();

        try {
            $reports = $this->getCollection(
                $report->campaignType,
                $report->state,
                $report->characteristicA
            );
        } catch (DonorReportDoesNotExistException) {
            $reports = new DonorReportCollection();
        }

        $reports->donorReports[$report->characteristicB->value ?? DonorReport::ALL] = $report;
        $this->saveCollection($reports);
    }

    public function saveCollection(DonorReportCollection $reports): void
    {
        FileUtilities::saveContents($this->getFilename($reports), $this->marshalCollection($reports));
    }

    private function marshalCollection(DonorReportCollection $reports): string
    {
        return JsonUtilities::jsonEncode($reports, $this->prettyPrint);
    }

    public function deleteAll(): void
    {
        // careful!!!
        FileUtilities::unlink($this->path, recursive: true);
    }
}
