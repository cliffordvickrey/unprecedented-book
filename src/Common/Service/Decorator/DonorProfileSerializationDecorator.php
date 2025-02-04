<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Decorator;

use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Service\DTO\RecipientAttributeBag;

final readonly class DonorProfileSerializationDecorator implements \Stringable
{
    /**
     * @param array<int, RecipientAttributeBag> $recipientAttributesByCycle
     */
    public function __construct(
        private DonorProfile $subject,
        private array $recipientAttributesByCycle,
    ) {
    }

    public function __toString(): string
    {
        return implode(\PHP_EOL, $this->toLines()).\PHP_EOL;
    }

    /**
     * @return list<string>
     */
    private function toLines(): array
    {
        $lines = [
            str_repeat('-', 80),
            \sprintf('ID: %d', $this->subject->id),
            \sprintf('Name: %s', $this->subject->name),
        ];

        if (null !== $this->subject->state) {
            $lines[] = \sprintf('State: %s', $this->subject->state->getDescription());
        }

        foreach ($this->subject->campaigns as $profileByCampaign) {
            $linesForCampaign = $this->linesForCampaign($profileByCampaign);

            if (0 !== \count($linesForCampaign)) {
                $lines = [...$lines, '', ...$linesForCampaign];
            }
        }

        foreach ($this->subject->cycles as $cycle => $profileByCycle) {
            $linesForCycle = $this->linesForCycle($profileByCycle);

            if (0 !== \count($linesForCycle)) {
                $lines = [...$lines, '', \sprintf('%d cycle', $cycle), ...$linesForCycle];
            }
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function linesForCampaign(DonorProfileCampaign $profileByCampaign): array
    {
        if ($profileByCampaign->total->receipts < 1) {
            return [];
        }

        $prop = $profileByCampaign->campaignType->toProp();

        $attr = $this->recipientAttributesByCycle[2024][$prop];

        $lines = [
            \sprintf('2024 Race - %s:', $attr->description),
            $this->stringify(2024, $prop, $profileByCampaign->total),
        ];

        if ($profileByCampaign->maxConsecutiveMonthlyDonationCount > 1) {
            $lines[] = \sprintf(
                'Gave the same contribution for %d consecutive months',
                $profileByCampaign->maxConsecutiveMonthlyDonationCount
            );
        }

        if ($profileByCampaign->maxConsecutiveWeeklyDonationCount > 1) {
            $lines[] = \sprintf(
                'Gave the same contribution for %d consecutive weeks',
                $profileByCampaign->maxConsecutiveWeeklyDonationCount
            );
        }

        if ($profileByCampaign->priorDonor) {
            $lines[] = 'Gave to this candidate in a previous election cycle';
        }

        if ($profileByCampaign->dayOneLaunch) {
            $lines[] = 'Gave to this candidate within the first day of the campaign';
        }

        if ($profileByCampaign->weekOneLaunch) {
            $lines[] = 'Gave to this candidate within the first week of the campaign';
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    private function linesForCycle(DonorProfileCycle $profileByCycle): array
    {
        $amounts = array_filter(
            $profileByCycle->toArray(),
            static fn ($val) => ($val instanceof DonorProfileAmount) && $val->receipts > 0
        );

        $lines = [];

        foreach ($amounts as $prop => $amt) {
            $lines[] = $this->stringify($profileByCycle->cycle, $prop, $amt);
        }

        return $lines;
    }

    private function stringify(int $cycle, string $prop, DonorProfileAmount $amt): string
    {
        $attr = $this->recipientAttributesByCycle[$cycle][$prop];

        return \sprintf('Gave %s to %s', $amt, $attr->description);
    }
}
