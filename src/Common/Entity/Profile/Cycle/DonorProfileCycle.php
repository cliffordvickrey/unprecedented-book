<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use Webmozart\Assert\Assert;

abstract class DonorProfileCycle extends Entity
{
    public int $cycle = 0;
    public bool $nonPresDemocratic = false;
    public bool $nonPresRepublican = false;
    public bool $nonPresThirdParty = false;
    public bool $pac = false;
    public bool $party = false;
    public bool $presOtherDemocratic = false;
    public bool $presOtherRepublican = false;
    public bool $presOtherThirdParty = false;

    public function getElectionDate(): \DateTimeImmutable
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $this->getElectionDayStr()) ?: null;
        $dt = $dt?->setTime(0, 0);
        Assert::isInstanceOf(\DateTimeImmutable::class, $dt);

        return $dt;
    }

    abstract protected function getElectionDayStr(): string;
}
