<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum PartyAffiliation: string
{
    case ACE = 'ACE'; // Ace Party
    case AKI = 'AKI'; // Alaskan Independence Party
    case AIC = 'AIC'; // American Independent Conservative
    case AIP = 'AIP'; // American Independent Party
    case AMP = 'AMP'; // American Party
    case APF = 'APF'; // American People's Freedom Party
    case AE = 'AE'; // Americans Elect
    case CIT = 'CIT'; // Citizens' Party
    case CMD = 'CMD'; // Commandments Party
    case CMP = 'CMP'; // Commonwealth Party of the U.S.
    case COM = 'COM'; // Communist Party
    case CNC = 'CNC'; // Concerned Citizens Party Of Connecticut
    case CRV = 'CRV'; // Conservative Party
    case CON = 'CON'; // Constitution Party
    case CST = 'CST'; // Constitutional
    case COU = 'COU'; // Country
    case DCG = 'DCG'; // D.C. Statehood Green Party
    case DNL = 'DNL'; // Democratic -Nonpartisan League
    case DEM = 'DEM'; // Democratic Party
    case DC = 'D/C'; // Democratic/Conservative
    case DFL = 'DFL'; // Democratic-Farmer-Labor
    case DGR = 'DGR'; // Desert Green Party
    case FED = 'FED'; // Federalist
    case FLP = 'FLP'; // Freedom Labor Party
    case FRE = 'FRE'; // Freedom Party
    case GWP = 'GWP'; // George Wallace Party
    case GRT = 'GRT'; // Grassroots
    case GRE = 'GRE'; // Green Party
    case GR = 'GR'; // Green-Rainbow
    case HRP = 'HRP'; // Human Rights Party
    case IDP = 'IDP'; // Independence Party
    case IND = 'IND'; // Independent
    case IAP = 'IAP'; // Independent American Party
    case ICD = 'ICD'; // Independent Conservative Democratic
    case IGR = 'IGR'; // Independent Green
    case IP = 'IP'; // Independent Party
    case IDE = 'IDE'; // Independent Party of Delaware
    case IGD = 'IGD'; // Industrial Government Party
    case JCN = 'JCN'; // Jewish/Christian National
    case JUS = 'JUS'; // Justice Party
    case LRU = 'LRU'; // La Raza Unida
    case LBR = 'LBR'; // Labor Party
    case LFT = 'LFT'; // Less Federal Taxes
    case LBL = 'LBL'; // Liberal Party
    case LIB = 'LIB'; // Libertarian Party
    case LBU = 'LBU'; // Liberty Union Party
    case MTP = 'MTP'; // Mountain Party
    case NDP = 'NDP'; // National Democratic Party
    case NLP = 'NLP'; // Natural Law Party
    case NA = 'NA'; // New Alliance
    case NJC = 'NJC'; // New Jersey Conservative Party
    case NPP = 'NPP'; // New Progressive Party
    case NPA = 'NPA'; // No Party Affiliation
    case NOP = 'NOP'; // No Party Preference
    case NNE = 'NNE'; // None
    case N = 'N'; // Nonpartisan
    case NON = 'NON'; // Non-Party
    case OE = 'OE'; // One Earth Party
    case OTH = 'OTH'; // Other
    case PG = 'PG'; // Pacific Green
    case PSL = 'PSL'; // Party for Socialism and Liberation
    case PAF = 'PAF'; // Peace And Freedom
    case PFP = 'PFP'; // Peace And Freedom Party
    case PFD = 'PFD'; // Peace Freedom Party
    case POP = 'POP'; // People Over Politics
    case PPY = 'PPY'; // People's Party
    case PCH = 'PCH'; // Personal Choice Party
    case PPD = 'PPD'; // Popular Democratic Party
    case PRO = 'PRO'; // Progressive Party
    case NAP = 'NAP'; // Prohibition Party
    case PRI = 'PRI'; // Puerto Rican Independence Party
    case RUP = 'RUP'; // Raza Unida Party
    case REF = 'REF'; // Reform Party
    case REP = 'REP'; // Republican Party
    case RES = 'RES'; // Resource Party
    case RTL = 'RTL'; // Right To Life
    case SEP = 'SEP'; // Socialist Equality Party
    case SLP = 'SLP'; // Socialist Labor Party
    case SUS = 'SUS'; // Socialist Party
    case SOC = 'SOC'; // Socialist Party U.S.A.
    case SWP = 'SWP'; // Socialist Workers Party
    case TX = 'TX'; // Taxpayers
    case TWR = 'TWR'; // Taxpayers Without Representation
    case TEA = 'TEA'; // Tea Party
    case THD = 'THD'; // Theo-Democratic
    case LAB = 'LAB'; // U.S. Labor Party
    case USP = 'USP'; // U.S. People's Party
    case UST = 'UST'; // U.S. Taxpayers Party
    case UN = 'UN'; // Unaffiliated
    case UC = 'UC'; // United Citizen
    case UNI = 'UNI'; // United Party
    case UNK = 'UNK'; // Unknown
    case VET = 'VET'; // Veterans Party
    case WTP = 'WTP'; // We the People
    case W = 'W'; // Write-In

    public function isRepublican(): bool
    {
        return self::REP === $this || self::CRV === $this;
    }

    public function isDemocratic(): bool
    {
        return self::DEM === $this || self::DNL === $this || self::DFL === $this;
    }
}
