<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\ReceiptInPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\RecipientAttribute;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\PartyType;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepositoryInterface;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepositoryInterface;
use CliffordVickrey\Book2024\Common\Service\DTO\ReceiptAnalysis;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeBag;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type Recipient array{cycle: int, prop: string}
 */
class DonorProfileService implements DonorProfileServiceInterface
{
    /** @var array<string, PartyType> */
    private static array $actualPartyAffiliations = [
        'angus_king' => PartyType::democratic,
        'bernie_sanders' => PartyType::democratic,
    ];

    private readonly CandidateAggregateRepositoryInterface $candidateAggregateRepository;
    private readonly CommitteeAggregateRepositoryInterface $committeeAggregateRepository;
    private readonly DonorProfile $prototype;
    /** @var array<int, RecipientAttributeBag> */
    private array $recipientAttributes = [];
    /** @var array<string, Recipient|false> */
    private array $recipientMap;

    public function __construct(
        ?CandidateAggregateRepositoryInterface $candidateAggregateRepository = null,
        ?CommitteeAggregateRepositoryInterface $committeeAggregateRepository = null,
    ) {
        $this->candidateAggregateRepository = $candidateAggregateRepository ?? new CandidateAggregateRepository();
        $this->committeeAggregateRepository = $committeeAggregateRepository ?? new CommitteeAggregateRepository();

        $this->prototype = DonorProfile::build();

        $this->recipientMap = $this->buildRecipientMap();
    }

    public function buildDonorProfile(DonorPanel $panel): DonorProfile
    {

    }

    private function analyzeReceipt(ReceiptInPanel $receiptInPanel): ReceiptAnalysis
    {
        $year = $receiptInPanel->date->format('Y');
        Assert::numeric($year);
        $year = (int)$year;
        $cycle = ($year % 2 === 0) ? $year : ($year + 1);

        $committee = $this->committeeAggregateRepository->getByCommitteeId($receiptInPanel->recipientSlug);

        $fecCandidateId = ($committee->infoByYear[$cycle] ?? null)?->CAND_ID;

        if (null === $fecCandidateId) {
            $fecCandidateId = $committee->getCandidateIdByYear($cycle);
        }

        $candidate = $fecCandidateId ? $this->candidateAggregateRepository->getByCandidateId($fecCandidateId) : null;

        $analysis = new ReceiptAnalysis(
            $receiptInPanel->date,
            $receiptInPanel->amount,
            $cycle,
            $committee,
            $candidate
        );

        $committeeFecId = $analysis->committee->id;

        $keyWithDate = \sprintf('%s|%s', $committeeFecId, $analysis->date->format('Y-m-d'));
        $keyWithCycle = \sprintf('%s|%s', $committeeFecId, $analysis->cycle);

        if (isset($this->recipientMap[$keyWithDate])) {
            $recipient = $this->recipientMap[$keyWithDate];
        } elseif (isset($this->recipientMap[$keyWithCycle])) {
            $recipient = $this->recipientMap[$keyWithCycle];
        } elseif ($candidate) {
            $recipient = $this->determineRecipient($analysis);
            $this->recipientMap[$keyWithCycle] = $recipient;
        } else {
            $recipient = false;
            $this->recipientMap[$keyWithCycle] = false;
        }

        if (false !== $recipient) {
            $analysis->cycle = $recipient['cycle'];
            $analysis->prop = $recipient['prop'];
        }

        return $analysis;
    }

    private function determineRecipient(ReceiptAnalysis $analysis): array|false
    {
        $cyclePrototype = $this->prototype->cycles[$analysis->cycle] ?? null;

        if (!$cyclePrototype) {
            return false;
        }

        if ($cyclePrototype->getElectionDate() < $analysis->date) {
            return false;
        }

        $attr = $this->recipientAttributes[$analysis->cycle];

        if (isset($attr->recipientAttributesByCandidateSlug[$analysis->candidate->slug])) {
            return false;
        }

        $candidateInfo = $analysis->candidate->getInfo($analysis->cycle);

        if (!$candidateInfo) {
            return false;
        }

        $office = $candidateInfo->CAND_OFFICE;

        if (!$office) {
            return false;
        }

        $party = self::$actualPartyAffiliations[$candidate->slug] ?? PartyType::fromCandidateInfo($candidateInfo);

        $isPresidential = CandidateOffice::P === $office;

        $prefix = $isPresidential ? 'pres' : 'nonPres';
        $suffix = \ucfirst($party->value);

        return [$analysis->cycle, "$prefix$suffix"];
    }

    /**
     * @return array<string, Recipient>
     */
    private function buildRecipientMap(): array
    {
        return array_reduce(
            $this->prototype->cycles,
            fn (array $carry, DonorProfileCycle $donorProfileCycle) => \array_merge(
                $carry,
                $this->buildRecipientCycleMap($donorProfileCycle)
            ),
            []
        );
    }

    /**
     * @return array<string, Recipient>
     */
    private function buildRecipientCycleMap(DonorProfileCycle $donorProfileCycle): array
    {
        $recipientAttributes = $this->getRecipientAttributes($donorProfileCycle);

        $electionDate = $donorProfileCycle->getElectionDate();

        $startOfCycleDateStr = \sprintf('%d-01-01', $donorProfileCycle->cycle - 2);

        $startOfCycleDate = \DateTimeImmutable::createFromFormat('Y-m-d', $startOfCycleDateStr);

        Assert::isInstanceOf(\DateTimeImmutable::class, $startOfCycleDate);

        $map = [];

        foreach ($recipientAttributes as $prop => $attr) {
            $this->assertValidCandidateSlug((string)$attr->slug);

            $startDate = $attr->startDate ?? $startOfCycleDate;

            if ($startDate > $startOfCycleDate) {
                $startDate = $startOfCycleDate;
            }

            $endDate = $attr->endDate ?? $electionDate;

            $recipient = ['cycle' => $donorProfileCycle, 'prop' => $prop];

            $map = array_merge($map, array_reduce(
                DateUtilities::getDateRanges($startDate, $endDate),
                static fn (array $carry, \DateTimeImmutable $date) => array_merge(
                    $carry,
                    array_reduce(
                        $attr->committeeIds,
                        static fn (array $carryInner, string $committeeId) => array_merge(
                            $carryInner,
                            [\sprintf('%s|%s', $committeeId, $date->format('Y-m-d')) => $recipient]
                        ),
                        []
                    )
                ),
                []
            ));
        }

        return $map;
    }

    private function assertValidCandidateSlug(string $slug): void
    {
        try {
            Assert::notEmpty($slug);
            $this->candidateAggregateRepository->getAggregate($slug);
        } catch (\Throwable) {
            $msg = \sprintf('Invalid candidate slug, "%s"', $slug);
            throw new BookUnexpectedValueException($msg);
        }
    }

    private function getRecipientAttributes(int|DonorProfileCycle $cycle): RecipientAttributeBag
    {
        $key = $cycle;

        if ($cycle instanceof DonorProfileCycle) {
            $key = $cycle->cycle;
        }

        $this->recipientAttributes[$key] ??= $this->collectRecipientAttributes($cycle);

        return $this->recipientAttributes[$key];
    }

    private function collectRecipientAttributes(int|DonorProfileCycle $cycle): RecipientAttributeBag
    {
        $donorProfileCycle = $cycle;

        if (\is_int($donorProfileCycle)) {
            $donorProfileCycle = DonorProfileCycle::create(['cycle' => $cycle]);
        }

        $reflectionObj = new \ReflectionObject($donorProfileCycle);

        $properties = $reflectionObj->getProperties(\ReflectionProperty::IS_PUBLIC);

        $attrs = array_reduce($properties, function (array $carry, \ReflectionProperty $property): array {
            $attrs = $property->getAttributes(RecipientAttribute::class);

            if (0 === \count($attrs)) {
                return $carry;
            }

            return array_merge($carry, [$property->getName() => $attrs[0]]);
        }, []);

        return new RecipientAttributeBag($attrs);
    }
}
