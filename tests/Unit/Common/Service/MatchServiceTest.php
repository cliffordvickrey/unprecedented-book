<?php

declare(strict_types=1);

namespace Unit\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Service\MatchService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MatchService::class)]
class MatchServiceTest extends TestCase
{
    public function testAreNamesSimilar(): void
    {
        $nameA = 'COSTANZA, GEORGE COL.';
        $nameB = 'COSTANZA, GEORGE LOUIS';

        $donorA = Donor::__set_state(['name' => $nameA]);
        $donorB = Donor::__set_state(['name' => $nameB]);

        $matchService = new MatchService();
        self::assertTrue($matchService->areNamesSimilar($donorA, $donorB));
        self::assertEquals(1, $matchService->getLastSimilarName());

        $matchService = new MatchService();
        $donorA->name = 'COSTANZA, GEORGE ALEXANDER';
        self::assertFalse($matchService->areNamesSimilar($donorA, $donorB));
        self::assertEquals(0.7143, $matchService->getLastSimilarName());
        self::assertEquals(0.1429, $matchService->getLastSimilarText());
    }

    public function testCompare(): void
    {
        $matchService = new MatchService();

        $a = self::mockDonor(100);
        $b = self::mockDonor();
        $b->occupation = 'CONSULTANT';

        $result = $matchService->compare($a, $b);

        self::assertEquals(0.9611, $result->similarityScore);
        self::assertEquals(100, $result->id);

        $b = self::mockDonor();
        $b->name = 'COSTANZA, GEORGE';
        $result = $matchService->compare($a, $b);

        self::assertEquals(0.6493, $result->similarityScore);
        self::assertNull($result->id);

        $b = self::mockDonor();
        $b->zip = '100252000';
        $result = $matchService->compare($a, $b);

        self::assertEquals(0.92, $result->similarityScore);
        self::assertEquals(100, $result->id);

        $b = self::mockDonor();
        $b->zip = '999992000';
        $result = $matchService->compare($a, $b);

        self::assertEquals(0.84, $result->similarityScore);
        self::assertEquals(100, $result->id);
    }

    private static function mockDonor(?int $id = null): Donor
    {
        $donor = new Donor();
        $donor->id = (int) $id;
        $donor->name = 'VANDELAY, ART';
        $donor->address = '2880 BROADWAY';
        $donor->city = 'NEW YORK';
        $donor->state = 'NY';
        $donor->zip = '10025-1000';
        $donor->employer = 'VANDELAY INDUSTRIES';
        $donor->occupation = 'IMPORTER';

        return $donor;
    }
}
