<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

final readonly class Jurisdiction implements \Stringable
{
    public function __construct(public string $state, public ?int $district = null)
    {
    }

    public static function fromString(string $str): self
    {
        $state = substr($str, 0, 2);
        $district = substr($str, 2, 2);

        return new self($state, '' === $district ? null : CastingUtilities::toInt($district));
    }

    public static function fromMemo(string $memo): ?self
    {
        if (!preg_match('/^EARMARKED FOR DEMOCRATIC NOMINEE FOR (.*)\./', $memo, $matches)) {
            return null;
        }

        return self::fromFecJurisdiction($matches[1]);
    }

    public function getStateName(): string
    {
        /** @phpstan-var array<string, string> $stateNames */
        static $stateNames = [
            'AL' => 'ALABAMA',
            'AK' => 'ALASKA',
            'AS' => 'AMERICAN SAMOA',
            'AZ' => 'ARIZONA',
            'AR' => 'ARKANSAS',
            'CA' => 'CALIFORNIA',
            'CO' => 'COLORADO',
            'CT' => 'CONNECTICUT',
            'DE' => 'DELAWARE',
            'DC' => 'DISTRICT OF COLUMBIA',
            'FM' => 'FEDERATED STATES OF MICRONESIA',
            'FL' => 'FLORIDA',
            'GA' => 'GEORGIA',
            'GU' => 'GUAM',
            'HI' => 'HAWAII',
            'ID' => 'IDAHO',
            'IL' => 'ILLINOIS',
            'IN' => 'INDIANA',
            'IA' => 'IOWA',
            'KS' => 'KANSAS',
            'KY' => 'KENTUCKY',
            'LA' => 'LOUISIANA',
            'ME' => 'MAINE',
            'MH' => 'MARSHALL ISLANDS',
            'MD' => 'MARYLAND',
            'MA' => 'MASSACHUSETTS',
            'MI' => 'MICHIGAN',
            'MN' => 'MINNESOTA',
            'MS' => 'MISSISSIPPI',
            'MO' => 'MISSOURI',
            'MT' => 'MONTANA',
            'NE' => 'NEBRASKA',
            'NV' => 'NEVADA',
            'NH' => 'NEW HAMPSHIRE',
            'NJ' => 'NEW JERSEY',
            'NM' => 'NEW MEXICO',
            'NY' => 'NEW YORK',
            'NC' => 'NORTH CAROLINA',
            'ND' => 'NORTH DAKOTA',
            'MP' => 'NORTHERN MARIANA ISLANDS',
            'OH' => 'OHIO',
            'OK' => 'OKLAHOMA',
            'OR' => 'OREGON',
            'PW' => 'PALAU',
            'PA' => 'PENNSYLVANIA',
            'PR' => 'PUERTO RICO',
            'RI' => 'RHODE ISLAND',
            'SC' => 'SOUTH CAROLINA',
            'SD' => 'SOUTH DAKOTA',
            'TN' => 'TENNESSEE',
            'TX' => 'TEXAS',
            'UT' => 'UTAH',
            'VT' => 'VERMONT',
            'VI' => 'VIRGIN ISLANDS',
            'VA' => 'VIRGINIA',
            'WA' => 'WASHINGTON',
            'WV' => 'WEST VIRGINIA',
            'WI' => 'WISCONSIN',
            'WY' => 'WYOMING',
        ];

        return $stateNames[$this->state] ?? '';
    }

    public static function fromFecJurisdiction(string $fecJurisdiction): ?self
    {
        if ('-' === $fecJurisdiction) {
            return new self('US');
        }

        $parts = explode('-', $fecJurisdiction);

        if (2 !== \count($parts)) {
            return null;
        }

        if ('' === $parts[1]) {
            return new self($parts[0]);
        }

        if ('AL' === $parts[1]) {
            // "at-large"
            return new self($parts[0], 0);
        }

        return new self($parts[0], CastingUtilities::toInt($parts[1]));
    }

    public function __toString(): string
    {
        if (null === $this->district) {
            return $this->state;
        }

        return \sprintf('%s%02d', $this->state, $this->district);
    }
}
