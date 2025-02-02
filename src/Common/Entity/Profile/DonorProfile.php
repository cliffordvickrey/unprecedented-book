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

class DonorProfile extends Entity
{
    public ?State $state = null;
    /** @var array<string, DonorProfileCampaign> */
    public array $campaigns = [];
    /** @var array<int, DonorProfileCycle> */
    public array $cycles = [];
    /** @var array<string, bool> */
    private array $monthlyReceiptMemo = [];

    public static function build(): self
    {
        $self = new self();
        $self->clearState();
        return $self;
    }

    public function clearState(): void
    {
        $this->state = null;

        $this->campaigns = [
            CampaignType::joe_biden->value => new DonorProfileCampaign(),
            CampaignType::kamala_harris->value => new DonorProfileCampaign(),
            CampaignType::donald_trump->value => new DonorProfileCampaign(),
        ];

        $this->cycles = [
            2016 => new DonorProfileCycle2016(),
            2020 => new DonorProfileCycle2020(),
            2024 => new DonorProfileCycle2024(),
        ];

        $this->monthlyReceiptMemo = [];
    }

    public function addCampaignAmount(CampaignType $campaignType, float $amount, bool $isWeekOneLaunch): void
    {
        $campaign = $this->campaigns[$campaignType->value];

        $campaign->total->receipts++;
        $campaign->total->amount += $amount;

        if ($isWeekOneLaunch) {
            $campaign->weekOneLaunch = true;
        }
    }

    public function addReceiptAnalysis(ReceiptAnalysis $analysis): void
    {
        $priorDonor = $analysis->cycle < 2024
            && $analysis->candidate
            && CampaignType::tryFrom($analysis->candidate->slug);

        if ($priorDonor) {
            $campaignType = CampaignType::from((string)$analysis->candidate?->slug);
            $this->campaigns[$campaignType->value]->priorDonor = true;
        }

        if (!isset($this->cycles[$analysis->cycle])) {
            return;
        }

        $campaignType = $analysis->getCampaignType(2024);

        if ($campaignType) {
            $this->addCampaignAmount($campaignType, $analysis->amount, $analysis->isWeekOneLaunch);
        }

        if ($analysis->prop) {
            $this->addAmountForCycle($analysis->cycle, $analysis->prop, $analysis->amount);
        }

        if ($analysis->pacType) {
            $this->addPacTypeForCycle($analysis->cycle, $analysis->pacType, $analysis->amount);
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
