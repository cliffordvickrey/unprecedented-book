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

class DonorProfile extends Entity
{
    public int $id = 0;
    public string $name = '';
    public ?State $state = null;
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
        $this->state = null;

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
    public function analyze(array $analyses): void
    {
        array_walk($analyses, $this->addReceiptAnalysis(...));
        $this->setMaxConsecutiveMonthlyDonationCounts();
    }

    private function setMaxConsecutiveMonthlyDonationCounts(): void
    {
        $this->doSetMaxConsecutiveDonationCounts();
        $this->doSetMaxConsecutiveDonationCounts(monthly: false);
    }

    private function doSetMaxConsecutiveDonationCounts(bool $monthly = true): void
    {
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
            } elseif ($weekOrMonth > 18) {
                $key = CampaignType::kamala_harris->value;
            } else {
                $key = CampaignType::joe_biden->value;
            }

            $maxConsecutiveDonationCounts[$key] = max($maxConsecutiveDonationCounts[$key], $counter);

            $laggedPartyCode = $partyCode;
            $laggedAmt = $amt;
            $laggedWeekOrMonth = $weekOrMonth;
        }

        foreach ($maxConsecutiveDonationCounts as $key => $maxConsecutiveMonthlyDonationCount) {
            if ($monthly) {
                $this->campaigns[$key]->maxConsecutiveMonthlyDonationCount = $maxConsecutiveMonthlyDonationCount;
            } else {
                $this->campaigns[$key]->maxConsecutiveWeeklyDonationCount = $maxConsecutiveMonthlyDonationCount;
            }
        }
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
            $this->addCampaignAmount($campaignType, $analysis->date, $analysis->amount, $analysis->isWeekOneLaunch);
        }

        if ($analysis->prop) {
            $this->addAmountForCycle($analysis->cycle, $analysis->prop, $analysis->amount);
        }

        if ($analysis->pacType) {
            $this->addPacTypeForCycle($analysis->cycle, $analysis->pacType, $analysis->amount);
        }
    }

    private function addCampaignAmount(
        CampaignType $campaignType,
        \DateTimeImmutable $date,
        float $amount,
        bool $isWeekOneLaunch,
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

        if ($isWeekOneLaunch) {
            $campaign->weekOneLaunch = true;
        }
    }

    private function addAmountForCycle(int $cycle, string $prop, float $amount): void
    {
        $this->cycles[$cycle]->add($prop, $amount);
    }

    private function addPacTypeForCycle(int $cycle, PacType $pacType, float $amount): void
    {
        $this->addAmountForCycle($cycle, $pacType->value, $amount);
    }
}
