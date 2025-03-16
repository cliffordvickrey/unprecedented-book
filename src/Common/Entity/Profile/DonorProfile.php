<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\PacType;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Service\DTO\ReceiptAnalysis;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use Webmozart\Assert\Assert;

class DonorProfile extends Entity implements \Countable
{
    private const int FLAG_DEMOCRAT_2024 = 1;
    private const int FLAG_REPUBLICAN_2024 = 2;

    public int $id = 0;
    public string $name = '';
    public State $state = State::USA;
    public int $flags = 0;
    /** @var array<string, DonorProfileCampaign> */
    public array $campaigns = [];
    /** @var array<int, DonorProfileCycle> */
    public array $cycles = [];
    /** @var array<string, bool> */
    private array $monthlyReceiptMemo = [];
    /** @var array<string, bool> */
    private array $weeklyReceiptMemo = [];

    public static function build(): self
    {
        $self = new self();
        $self->clearState();

        return $self;
    }

    public function clearState(): void
    {
        $this->id = 0;
        $this->name = '';
        $this->state = State::USA;
        $this->flags = 0;

        $this->campaigns = $this->buildCampaigns();

        $this->cycles = [
            2016 => new DonorProfileCycle2016(),
            2020 => new DonorProfileCycle2020(),
            2024 => new DonorProfileCycle2024(),
        ];

        $this->monthlyReceiptMemo = [];
        $this->weeklyReceiptMemo = [];
    }

    /**
     * @return array<string, DonorProfileCampaign>
     */
    private function buildCampaigns(): array
    {
        /** @var array<string, DonorProfileCampaign> $campaigns */
        $campaigns = array_reduce(
            CampaignType::cases(),
            static fn (array $carry, CampaignType $campaignType) => array_merge(
                $carry,
                [$campaignType->value => DonorProfileCampaign::__set_state(['campaignType' => $campaignType])]
            ),
            []
        );

        return $campaigns;
    }

    /**
     * @param list<ReceiptAnalysis> $analyses
     */
    public function acceptAnalyses(array $analyses): void
    {
        array_walk($analyses, $this->addReceiptAnalysis(...));

        array_walk($this->campaigns, static fn (DonorProfileCampaign $campaign) => ksort($campaign->donationsByDate));

        $this->setMaxConsecutiveDonationCounts();
    }

    private function setMaxConsecutiveDonationCounts(): void
    {
        $this->doSetMaxConsecutiveDonationCounts();
        $this->doSetMaxConsecutiveDonationCounts(monthly: true);
    }

    private function doSetMaxConsecutiveDonationCounts(bool $monthly = false): void
    {
        $dropoutThreshold = $monthly ? 18 : 78;

        $memo = $monthly ? $this->monthlyReceiptMemo : $this->weeklyReceiptMemo;

        if (empty($memo)) {
            return;
        }

        ksort($memo);

        $maxConsecutiveDonationCounts = [
            CampaignType::joe_biden->value => 0,
            CampaignType::kamala_harris->value => 0,
            CampaignType::donald_trump->value => 0,
        ];

        $keys = array_keys($memo);

        $counter = 1;

        $laggedPartyCode = '';
        $laggedAmt = '';
        $laggedWeekOrMonth = -1;

        foreach ($keys as $key) {
            [$partyCode, $amt, $weekOrMonth] = explode('|', $key);

            Assert::numeric($weekOrMonth);
            $weekOrMonth = (int) $weekOrMonth;

            if (
                $partyCode !== $laggedPartyCode
                || $amt !== $laggedAmt
                || $weekOrMonth !== ($laggedWeekOrMonth + 1)
            ) {
                $counter = 1;
            } else {
                ++$counter;
            }

            if ('R' === $partyCode) {
                $key = CampaignType::donald_trump->value;
            } elseif ($weekOrMonth > $dropoutThreshold) {
                $key = CampaignType::kamala_harris->value;
            } else {
                $key = CampaignType::joe_biden->value;
            }

            $maxConsecutiveDonationCounts[$key] = max($maxConsecutiveDonationCounts[$key], $counter);

            $laggedPartyCode = $partyCode;
            $laggedAmt = $amt;
            $laggedWeekOrMonth = $weekOrMonth;
        }

        foreach ($maxConsecutiveDonationCounts as $key => $maxConsecutiveDonationCount) {
            if ($monthly) {
                $this->campaigns[$key]->maxConsecutiveMonthlyDonationCount = $maxConsecutiveDonationCount;
            } else {
                $this->campaigns[$key]->maxConsecutiveWeeklyDonationCount = $maxConsecutiveDonationCount;
            }
        }
    }

    public function count(): int
    {
        return array_sum(array_map(\count(...), $this->campaigns));
    }

    private function addReceiptAnalysis(ReceiptAnalysis $analysis): void
    {
        $priorDonor = $analysis->cycle < 2024
            && $analysis->candidate
            && CampaignType::tryFrom($analysis->candidate->slug);

        if ($priorDonor) {
            $campaignType = CampaignType::from((string) $analysis->candidate?->slug);
            $this->campaigns[$campaignType->value]->priorDonor = true;
        }

        if (!isset($this->cycles[$analysis->cycle])) {
            return;
        }

        $campaignType = $analysis->getCampaignType(2024);

        if ($campaignType) {
            $this->addCampaignAmount(
                $campaignType,
                $analysis->date,
                $analysis->amount,
                $analysis->isWeekOneLaunch,
                $analysis->isDayOneLaunch
            );
        }

        if ($analysis->prop) {
            $candState = State::tryFrom((string) $analysis->candidate?->getInfo($analysis->cycle)?->CAND_OFFICE_ST);
            $this->addAmountForCycle(
                $analysis->cycle,
                $analysis->prop,
                $analysis->amount,
                $analysis->committee->slug,
                $candState
            );
        }

        if ($analysis->pacType) {
            $this->addPacTypeForCycle(
                $analysis->cycle,
                $analysis->pacType,
                $analysis->amount,
                $analysis->committee->slug
            );
        }
    }

    private function addCampaignAmount(
        CampaignType $campaignType,
        \DateTimeImmutable $date,
        float $amount,
        bool $isWeekOneLaunch,
        bool $isDayOneLaunch,
    ): void {
        $partyCode = $campaignType->getParty()->toCode();

        $monthKey = \sprintf(
            '%s|%010d|%02d',
            $partyCode,
            (int) floor($amount),
            DateUtilities::getMonthsAfterStartOfElectionCycle($date)
        );

        $this->monthlyReceiptMemo[$monthKey] = true;

        $weekKey = \sprintf(
            '%s|%010d|%03d',
            $partyCode,
            (int) floor($amount),
            DateUtilities::getWeeksAfterStartOfElectionCycle($date)
        );

        $this->weeklyReceiptMemo[$weekKey] = true;

        $campaign = $this->campaigns[$campaignType->value];

        ++$campaign->total->receipts;
        $campaign->total->amount += $amount;

        $dateStr = $date->format('Y-m-d');

        if (!isset($campaign->donationsByDate[$dateStr])) {
            $campaign->donationsByDate[$dateStr] = new DonorProfileAmount();
        }

        ++$campaign->donationsByDate[$dateStr]->receipts;
        $campaign->donationsByDate[$dateStr]->amount += $amount;

        if ($isWeekOneLaunch) {
            $campaign->weekOneLaunch = true;
        }

        if ($isDayOneLaunch) {
            $campaign->dayOneLaunch = true;
        }

        if ($dateStr > '2024-07-20') {
            $campaign->afterBidenDropout = true;
        } else {
            $campaign->beforeBidenDropout = true;
        }

        if (
            (
                CampaignType::donald_trump === $campaignType
                && !($this->flags & self::FLAG_REPUBLICAN_2024)
                && $this->flags & self::FLAG_DEMOCRAT_2024
            )
            || (
                CampaignType::donald_trump !== $campaignType
                && !($this->flags & self::FLAG_DEMOCRAT_2024)
                && $this->flags & self::FLAG_REPUBLICAN_2024
            )
        ) {
            $campaign->priorOpponentDonor = true;
        }

        if (CampaignType::donald_trump === $campaignType && !($this->flags & self::FLAG_REPUBLICAN_2024)) {
            $this->flags |= self::FLAG_REPUBLICAN_2024;
        } elseif (CampaignType::donald_trump !== $campaignType && !($this->flags & self::FLAG_DEMOCRAT_2024)) {
            $this->flags |= self::FLAG_DEMOCRAT_2024;
        }
    }

    private function addAmountForCycle(
        int $cycle,
        string $prop,
        float $amount,
        string $committeeSlug,
        ?State $candidateState = null,
    ): void {
        /** @phpstan-var array<string, bool> $nonPresProps */
        static $nonPresProps = [
            'houseDemocratic' => true,
            'houseRepublican' => true,
            'houseThirdParty' => true,
            'senateDemocratic' => true,
            'senateRepublican' => true,
            'senateThirdParty' => true,
        ];

        $donorState = State::USA === $this->state ? null : $this->state;

        if (State::USA === $candidateState) {
            $candidateState = null;
        }

        if (
            $donorState
            && $candidateState
            && isset($nonPresProps[$prop])
            && $donorState !== $candidateState
        ) {
            $outOfStateProp = str_starts_with($prop, 'house') ? 'outOfStateHouse' : 'outOfStateSenate';
            $this->cycles[$cycle]->add($outOfStateProp, $amount, $committeeSlug);
        }

        $this->cycles[$cycle]->add($prop, $amount, $committeeSlug);
    }

    private function addPacTypeForCycle(int $cycle, PacType $pacType, float $amount, string $committeeSlug): void
    {
        $this->addAmountForCycle($cycle, $pacType->value, $amount, $committeeSlug);
    }
}
