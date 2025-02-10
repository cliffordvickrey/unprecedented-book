<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReport;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportCollection;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Exception\ReportDoesNotExistException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

/**
 * @implements ReportRepositoryInterface<TReport>
 *
 * @template TReport of AbstractReport
 */
abstract readonly class AbstractReportRepository implements ReportRepositoryInterface
{
    private string $path;

    public function __construct(
        string $path = __DIR__.'/../../../web-data/',
        private bool $prettyPrint = false,
    ) {
        $this->path = $path.$this->getSubFolder();
    }

    private function getSubFolder(): string
    {
        $class = $this->getClassStr();

        $parts = explode('\\', $class);

        $trailing = $parts[array_key_last($parts)];

        $subFolder = preg_replace('/Report$/', '', $trailing);

        Assert::string($subFolder);

        return strtolower($subFolder);
    }

    /**
     * @phpstan-return class-string<TReport>
     */
    abstract protected function getClassStr(): string;

    public function get(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): AbstractReport {
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

        if ($characteristicA?->isMutuallyExclusiveOrTautologicalWith($characteristicB)) {
            $msg = \sprintf(
                '%s and %s are mutually exclusive donor categories',
                $characteristicA->value,
                $characteristicB?->value
            );
            throw new BookUnexpectedValueException($msg);
        }
    }

    /**
     * @throws ReportDoesNotExistException
     *
     * @phpstan-return AbstractReportCollection<TReport>
     */
    private function getCollection(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
    ): AbstractReportCollection {
        $filename = $this->getFilename(AbstractReport::inflectForKey(
            campaignType: $campaignType,
            state: $state,
            characteristicA: $characteristicA
        ));

        if (!is_file($filename)) {
            throw new ReportDoesNotExistException(\sprintf('Report "%s" does not exist', $filename));
        }

        $contents = FileUtilities::getContents($filename);

        return $this->unmarshallCollection($contents);
    }

    /**
     * @param AbstractReportCollection<TReport>|AbstractReport<AbstractReportRow>|string $report
     */
    private function getFilename(AbstractReportCollection|AbstractReport|string $report): string
    {
        $key = \is_object($report) ? $report->getKey() : $report;

        $parts = explode('-', $key, 4);

        if (\count($parts) > 3) {
            array_pop($parts);
        }

        return \sprintf('%s/%s.json', $this->path, implode(\DIRECTORY_SEPARATOR, $parts));
    }

    /**
     * @return AbstractReportCollection<TReport>
     */
    private function unmarshallCollection(string $json): AbstractReportCollection
    {
        $classStr = $this->getCollectionClassStr();

        $json = JsonUtilities::jsonDecode($json);

        return $classStr::__set_state($json);
    }

    /**
     * @return class-string<AbstractReportCollection<TReport>>
     */
    private function getCollectionClassStr(): string
    {
        // @phpstan-ignore-next-line Stan isn't clever enough to determine subtype here
        return match ($this->getClassStr()) {
            DonorReport::class => DonorReportCollection::class,
            CampaignReport::class => CampaignReportCollection::class,
            default => throw new BookUnexpectedValueException(),
        };
    }

    public function save(AbstractReport $report): void
    {
        try {
            $reports = $this->getCollection(
                $report->campaignType,
                $report->state,
                $report->characteristicA
            );
        } catch (ReportDoesNotExistException) {
            $className = $this->getCollectionClassStr();
            $reports = new $className();
        }

        $reports->reports[$report->characteristicB->value ?? AbstractReport::ALL] = $report;
        $this->saveCollection($reports);
    }

    public function saveCollection(AbstractReportCollection $reports): void
    {
        FileUtilities::saveContents($this->getFilename($reports), $this->marshalCollection($reports));
    }

    /**
     * @param AbstractReportCollection<TReport> $reports
     */
    private function marshalCollection(AbstractReportCollection $reports): string
    {
        return JsonUtilities::jsonEncode($reports, $this->prettyPrint);
    }

    public function deleteAll(): void
    {
        // careful!!!
        FileUtilities::unlink($this->path, recursive: true);
    }
}
