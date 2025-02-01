<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum State: string
{
    case AL = 'AL';
    case AK = 'AK';
    case AS = 'AS';
    case AZ = 'AZ';
    case AR = 'AR';
    case CA = 'CA';
    case CO = 'CO';
    case CT = 'CT';
    case DE = 'DE';
    case DC = 'DC';
    case FM = 'FM';
    case FL = 'FL';
    case GA = 'GA';
    case GU = 'GU';
    case HI = 'HI';
    case ID = 'ID';
    case IL = 'IL';
    case IN = 'IN';
    case IA = 'IA';
    case KS = 'KS';
    case KY = 'KY';
    case LA = 'LA';
    case ME = 'ME';
    case MH = 'MH';
    case MD = 'MD';
    case MA = 'MA';
    case MI = 'MI';
    case MN = 'MN';
    case MS = 'MS';
    case MO = 'MO';
    case MT = 'MT';
    case NE = 'NE';
    case NV = 'NV';
    case NH = 'NH';
    case NJ = 'NJ';
    case NM = 'NM';
    case NY = 'NY';
    case NC = 'NC';
    case ND = 'ND';
    case MP = 'MP';
    case OH = 'OH';
    case OK = 'OK';
    case OR = 'OR';
    case PW = 'PW';
    case PA = 'PA';
    case PR = 'PR';
    case RI = 'RI';
    case SC = 'SC';
    case SD = 'SD';
    case TN = 'TN';
    case TX = 'TX';
    case UT = 'UT';
    case VT = 'VT';
    case VI = 'VI';
    case VA = 'VA';
    case WA = 'WA';
    case WV = 'WV';
    case WI = 'WI';
    case WY = 'WY';

    public function getDescription(): string
    {
        /** @phpstan-var array<string, string> $descriptions */
        static $descriptions = self::getDescriptions();

        return $descriptions[$this->value];
    }

    /**
     * @return array<string, string>
     */
    public static function getDescriptions(): array
    {
        return [
            self::AL->value => 'ALABAMA',
            self::AK->value => 'ALASKA',
            self::AS->value => 'AMERICAN SAMOA',
            self::AZ->value => 'ARIZONA',
            self::AR->value => 'ARKANSAS',
            self::CA->value => 'CALIFORNIA',
            self::CO->value => 'COLORADO',
            self::CT->value => 'CONNECTICUT',
            self::DE->value => 'DELAWARE',
            self::DC->value => 'DISTRICT OF COLUMBIA',
            self::FM->value => 'FEDERATED STATES OF MICRONESIA',
            self::FL->value => 'FLORIDA',
            self::GA->value => 'GEORGIA',
            self::GU->value => 'GUAM',
            self::HI->value => 'HAWAII',
            self::ID->value => 'IDAHO',
            self::IL->value => 'ILLINOIS',
            self::IN->value => 'INDIANA',
            self::IA->value => 'IOWA',
            self::KS->value => 'KANSAS',
            self::KY->value => 'KENTUCKY',
            self::LA->value => 'LOUISIANA',
            self::ME->value => 'MAINE',
            self::MH->value => 'MARSHALL ISLANDS',
            self::MD->value => 'MARYLAND',
            self::MA->value => 'MASSACHUSETTS',
            self::MI->value => 'MICHIGAN',
            self::MN->value => 'MINNESOTA',
            self::MS->value => 'MISSISSIPPI',
            self::MO->value => 'MISSOURI',
            self::MT->value => 'MONTANA',
            self::NE->value => 'NEBRASKA',
            self::NV->value => 'NEVADA',
            self::NH->value => 'NEW HAMPSHIRE',
            self::NJ->value => 'NEW JERSEY',
            self::NM->value => 'NEW MEXICO',
            self::NY->value => 'NEW YORK',
            self::NC->value => 'NORTH CAROLINA',
            self::ND->value => 'NORTH DAKOTA',
            self::MP->value => 'NORTHERN MARIANA ISLANDS',
            self::OH->value => 'OHIO',
            self::OK->value => 'OKLAHOMA',
            self::OR->value => 'OREGON',
            self::PW->value => 'PALAU',
            self::PA->value => 'PENNSYLVANIA',
            self::PR->value => 'PUERTO RICO',
            self::RI->value => 'RHODE ISLAND',
            self::SC->value => 'SOUTH CAROLINA',
            self::SD->value => 'SOUTH DAKOTA',
            self::TN->value => 'TENNESSEE',
            self::TX->value => 'TEXAS',
            self::UT->value => 'UTAH',
            self::VT->value => 'VERMONT',
            self::VI->value => 'VIRGIN ISLANDS',
            self::VA->value => 'VIRGINIA',
            self::WA->value => 'WASHINGTON',
            self::WV->value => 'WEST VIRGINIA',
            self::WI->value => 'WISCONSIN',
            self::WY->value => 'WYOMING',
        ];
    }
}
