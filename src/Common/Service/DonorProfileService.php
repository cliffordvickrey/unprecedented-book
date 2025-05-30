<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\ReceiptInPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\RecipientAttribute;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\PacType;
use CliffordVickrey\Book2024\Common\Enum\PartyType;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepositoryInterface;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepositoryInterface;
use CliffordVickrey\Book2024\Common\Service\DTO\DonorCharacteristicCollection;
use CliffordVickrey\Book2024\Common\Service\DTO\ReceiptAnalysis;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeCollection;
use CliffordVickrey\Book2024\Common\Service\Helper\DonorProfileCharacteristicCollector;
use CliffordVickrey\Book2024\Common\Service\Helper\DonorProfileSerializationDecorator;
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

    private readonly DonorProfileCharacteristicCollector $characteristicCollector;
    private readonly CandidateAggregateRepositoryInterface $candidateAggregateRepository;
    private readonly CommitteeAggregateRepositoryInterface $committeeAggregateRepository;
    private readonly DonorProfile $prototype;
    /** @var array<int, RecipientAttributeCollection> */
    private array $recipientAttributesByCycle = [];
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

        $this->characteristicCollector = new DonorProfileCharacteristicCollector($this->recipientAttributesByCycle);
    }

    /**
     * @return array<string, Recipient>
     */
    private function buildRecipientMap(): array
    {
        /** @var array<string, Recipient> $map */
        $map = array_reduce(
            $this->prototype->cycles,
            fn (array $carry, DonorProfileCycle $donorProfileCycle) => array_merge(
                $carry,
                $this->buildRecipientCycleMap($donorProfileCycle)
            ),
            []
        );

        return $map;
    }

    /**
     * @return array<string, Recipient>
     */
    private function buildRecipientCycleMap(DonorProfileCycle $donorProfileCycle): array
    {
        $recipientAttributes = $this->getRecipientAttributes($donorProfileCycle);

        $electionDate = $donorProfileCycle->getElectionDate();

        $startOfCycleDateStr = \sprintf('%d-01-01', $donorProfileCycle->cycle - 1);

        $startOfCycleDate = \DateTimeImmutable::createFromFormat('Y-m-d', $startOfCycleDateStr);

        Assert::isInstanceOf($startOfCycleDate, \DateTimeImmutable::class);

        $map = [];

        foreach ($recipientAttributes as $prop => $attr) {
            if (empty($attr->committeeIds)) {
                // probably not a candidate
                continue;
            }

            $this->initRecipientAttribute($attr);

            $startDate = $attr->startDate ?? $startOfCycleDate;

            if (
                $startDate > $startOfCycleDate
                && '2024-07-21' !== $startDate->format('Y-m-d')
            ) {
                $startDate = $startOfCycleDate;
            }

            $endDate = $attr->endDate ?? $electionDate;

            $recipient = ['cycle' => $donorProfileCycle->cycle, 'prop' => $prop];

            // (extremely nerds voice) my Lisp-like higher order functions
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

        /** @var array<string, Recipient> $map */
        return $map;
    }

    private function getRecipientAttributes(int|DonorProfileCycle $cycle): RecipientAttributeCollection
    {
        if (\is_int($cycle)) {
            $key = $cycle;
        } else {
            $key = $cycle->cycle;
        }

        $this->recipientAttributesByCycle[$key] ??= $this->collectRecipientAttributes($cycle);

        return $this->recipientAttributesByCycle[$key];
    }

    private function collectRecipientAttributes(int|DonorProfileCycle $cycle): RecipientAttributeCollection
    {
        $donorProfileCycle = $cycle;

        if (\is_int($donorProfileCycle)) {
            $donorProfileCycle = DonorProfileCycle::create(['cycle' => $cycle]);
        }

        $reflectionObj = new \ReflectionObject($donorProfileCycle);

        $properties = $reflectionObj->getProperties(\ReflectionProperty::IS_PUBLIC);

        $attrs = array_reduce($properties, function (array $carry, \ReflectionProperty $property): array {
            $attrs = $property->getAttributes(RecipientAttribute::class);

            /** @var array<string, RecipientAttribute> $carry */
            if (0 === \count($attrs)) {
                return $carry;
            }

            return array_merge($carry, [$property->getName() => $attrs[0]->newInstance()]);
        }, []);

        return new RecipientAttributeCollection($attrs);
    }

    public function buildDonorProfile(DonorPanel $panel): DonorProfile
    {
        $profile = clone $this->prototype;
        $profile->id = $panel->donor->id;
        $profile->name = $panel->donor->name;
        $profile->state = State::create($panel->donor->state);

        $analyses = array_map($this->analyzeReceipt(...), $panel->receipts);
        $profile->acceptAnalyses($analyses);

        return $profile;
    }

    private function initRecipientAttribute(RecipientAttribute $attr): void
    {
        try {
            $slug = $attr->slug;
            Assert::notEmpty($slug);
            $candidate = $this->candidateAggregateRepository->getAggregate($slug);
            $attr->description ??= $candidate->name;
            $attr->description .= ' (President)';
        } catch (\Throwable) {
            $msg = \sprintf('Invalid candidate slug, "%s"', $slug);
            throw new BookUnexpectedValueException($msg);
        }
    }

    private function analyzeReceipt(ReceiptInPanel $receiptInPanel): ReceiptAnalysis
    {
        $committee = $this->committeeAggregateRepository->getAggregate($receiptInPanel->recipientSlug);

        $cycle = $receiptInPanel->getCycle();
        $fecCandidateId = ($committee->infoByYear[$cycle] ?? null)?->CAND_ID;

        if (null === $fecCandidateId) {
            $fecCandidateId = $committee->getCandidateIdByYear($cycle);
        }

        try {
            $candidate = $fecCandidateId
                ? $this->candidateAggregateRepository->getByCandidateId($fecCandidateId)
                : null;
        } catch (BookOutOfBoundsException) {
            $candidate = null;
        }

        $analysis = new ReceiptAnalysis(
            $receiptInPanel->date,
            $receiptInPanel->amount,
            $cycle,
            $committee,
            $candidate
        );

        $committeeFecId = $analysis->committee->id;

        $keyWithDate = \sprintf('%s|%s', $committeeFecId, $analysis->date->format('Y-m-d'));
        $keyWithCycle = \sprintf('%s|%d', $committeeFecId, $analysis->cycle);

        $beforeElection = isset($this->recipientMap[$keyWithDate]);

        if (!$beforeElection) {
            $cyclePrototype = $this->prototype->cycles[$analysis->cycle] ?? null;
            $beforeElection = $cyclePrototype && $analysis->date <= $cyclePrototype->getElectionDate();
        }

        $recipient = false;

        if (isset($this->recipientMap[$keyWithDate])) {
            $recipient = $this->recipientMap[$keyWithDate];
        } elseif (isset($this->recipientMap[$keyWithCycle]) && $beforeElection) {
            $recipient = $this->recipientMap[$keyWithCycle];
        } elseif ($candidate && $beforeElection) {
            $recipient = $this->determineRecipient($analysis);
            $this->recipientMap[$keyWithCycle] = $recipient;
        } elseif ($beforeElection) {
            $this->recipientMap[$keyWithCycle] = false;
        }

        if (false !== $recipient) {
            $this->setAnalysisRecipient($analysis, $recipient);
        }

        $this->analyzePacType($analysis);

        return $analysis;
    }

    /**
     * @return Recipient|false
     */
    private function determineRecipient(ReceiptAnalysis $analysis): array|false
    {
        $cyclePrototype = $this->prototype->cycles[$analysis->cycle] ?? null;

        if (!$cyclePrototype || !$analysis->candidate) {
            return false;
        }

        $attr = $this->recipientAttributesByCycle[$analysis->cycle]
            ->getAttributeByCandidateSlug($analysis->candidate->slug);

        if (null !== $attr) {
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

        $slug = $analysis->candidate->slug;
        $party = self::$actualPartyAffiliations[$slug] ?? PartyType::fromCandidateInfo($candidateInfo);

        $prefix = match ($office) {
            CandidateOffice::H => 'house',
            CandidateOffice::S => 'senate',
            default => 'presOther',
        };

        $suffix = ucfirst($party->value);

        return ['cycle' => $analysis->cycle, 'prop' => "$prefix$suffix"];
    }

    /**
     * @param Recipient $recipient
     */
    private function setAnalysisRecipient(ReceiptAnalysis $analysis, array $recipient): void
    {
        $analysis->cycle = $recipient['cycle'];
        $analysis->prop = $recipient['prop'];

        $attr = $this->getRecipientAttributeByRecipient($recipient);

        if (null === $analysis->candidate && $attr->slug) {
            $analysis->candidate = $this->candidateAggregateRepository->getAggregate($attr->slug);
        }

        // weak comparison is intentional here
        $analysis->isDayOneLaunch = $attr->startDate && $attr->startDate == $analysis->date;
        $analysis->isWeekOneLaunch = $attr->startDate && DateUtilities::isWithinWeek($analysis->date, $attr->startDate);
    }

    /**
     * @param Recipient $recipient
     */
    private function getRecipientAttributeByRecipient(array $recipient): RecipientAttribute
    {
        return $this->getRecipientAttribute($recipient['cycle'], $recipient['prop']);
    }

    private function getRecipientAttribute(int $cycle, string $prop): RecipientAttribute
    {
        return $this->recipientAttributesByCycle[$cycle][$prop];
    }

    private function analyzePacType(ReceiptAnalysis $analysis): void
    {
        $cycle = $analysis->cycle;
        $isJoint = false;
        $committeeType = null;

        // exclude Biden/Harris/Trump's joint fundraising committees. We'll consider these donations to the campaign
        // itself
        if ($analysis->getCampaignType()) {
            $isJoint = ($analysis->committee->infoByYear[$cycle] ?? null)?->CMTE_DSGN === CommitteeDesignation::J;
        }

        if (!$isJoint) {
            $committeeInfo = $analysis->committee->infoByYear[$cycle] ?? null;
            $committeeType = $committeeInfo?->CMTE_TP;
        }

        if (null !== $committeeType) {
            $analysis->pacType = PacType::fromCommitteeType($committeeType);
        }
    }

    public function serializeDonorProfile(DonorProfile $profile): string
    {
        return (string) new DonorProfileSerializationDecorator($profile, $this->recipientAttributesByCycle);
    }

    public function collectDonorCharacteristics(
        DonorPanel|DonorProfile $panelOrProfile,
    ): DonorCharacteristicCollection {
        if ($panelOrProfile instanceof DonorPanel) {
            $profile = $this->buildDonorProfile($panelOrProfile);
        } else {
            $profile = $panelOrProfile;
        }

        return new DonorCharacteristicCollection($this->characteristicCollector->collectCharacteristics($profile));
    }

    public function getCampaignCommitteeSlugs(CampaignType $campaignType): array
    {
        $attribute = $this->getRecipientAttribute(2024, $campaignType->toProp());

        return array_map(
            fn (string $committeeId) => $this->committeeAggregateRepository->getByCommitteeId($committeeId)->slug,
            $attribute->committeeIds
        );
    }
}
