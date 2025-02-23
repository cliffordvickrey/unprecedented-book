<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Cache\CacheInterface;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReport;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReport;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReportCollection;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Exception\ReportDoesNotExistException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use CliffordVickrey\Book2024\Common\Utilities\ZipUtilities;
use Webmozart\Assert\Assert;

/**
 * @implements ReportRepositoryInterface<TReport>
 *
 * @template TReport of AbstractReport
 *
 * @phpstan-import-type CompressionLevel from ZipUtilities
 */
abstract readonly class AbstractReportRepository implements ReportRepositoryInterface
{
    private string $path;

    /**
     * @phpstan-param CompressionLevel|null $compressionLevel
     */
    public function __construct(
        string $path = __DIR__.'/../../../web-data/',
        private bool $prettyPrint = false,
        private ?int $compressionLevel = ZipUtilities::DEFAULT_COMPRESSION_LEVEL,
        private ?CacheInterface $cache = null,
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

        return strtolower($subFolder).(null !== $this->compressionLevel ? '-gz' : '');
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

        if (null === $this->cache) {
            return $this->doGet($campaignType, $state, $characteristicA, $characteristicB);
        }

        $params = array_filter([
            'campaignType' => $campaignType->value,
            'state' => $state->value,
            'characteristicA' => $characteristicA?->value,
            'characteristicB' => $characteristicB?->value,
        ]);

        $report = $this->cache->get($this->getClassStr(), $params);

        if ($report) {
            return $report;
        }

        $report = $this->doGet($campaignType, $state, $characteristicA, $characteristicB);
        $ttl = $characteristicA ? 3600 : 0;
        $this->cache->set($report, $params, $ttl);

        return $report;
    }

    /**
     * @phpstan-return TReport
     */
    private function doGet(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): AbstractReport {
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

        $contents = null !== $this->compressionLevel ?
            ZipUtilities::gzUnCompressFile($filename)
            : FileUtilities::getContents($filename);

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

        return \sprintf('%s/%s.%s', $this->path, implode(\DIRECTORY_SEPARATOR, $parts), $this->getExtension());
    }

    private function getExtension(): string
    {
        return null === $this->compressionLevel ? 'json' : 'gz';
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
            CampaignReport::class => CampaignReportCollection::class,
            DonorReport::class => DonorReportCollection::class,
            MapReport::class => MapReportCollection::class,
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
        $json = JsonUtilities::jsonEncode($reports, $this->prettyPrint);

        if (null !== $this->compressionLevel) {
            $json = ZipUtilities::gzCompress($json, $this->compressionLevel);
        }

        return $json;
    }

    public function deleteAll(): void
    {
        // careful!!!
        FileUtilities::unlink($this->path, recursive: true);
    }
}
