<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum ReportType: string
{
    case _12C = '12C'; // Pre-convention
    case _12G = '12G'; // Pre-general
    case _12P = '12P'; // Pre-primary
    case _12R = '12R'; // Pre-Runoff
    case _12S = '12S'; // Pre-special
    case _30D = '30D'; // Post-Election
    case _30G = '30G'; // Post-general
    case _30P = '30P'; // Post-primary
    case _30R = '30R'; // Post-runoff
    case _30S = '30S'; // Post-special
    case _60D = '60D'; // Post-convention
    case ADJ = 'ADJ'; // Comprehensive adjusted amendment
    case CA = 'CA'; // Comprehensive amendment
    case M10 = 'M10'; // October monthly
    case M11 = 'M11'; // November monthly
    case M12 = 'M12'; // December monthly
    case M2 = 'M2'; // February monthly
    case M3 = 'M3'; // March monthly
    case M4 = 'M4'; // April monthly
    case M5 = 'M5'; // May monthly
    case M6 = 'M6'; // June monthly
    case M7 = 'M7'; // July monthly
    case M8 = 'M8'; // August monthly
    case M9 = 'M9'; // September monthly
    case MY = 'MY'; // Mid-year
    case Q1 = 'Q1'; // April quarterly
    case Q2 = 'Q2'; // July quarterly
    case Q3 = 'Q3'; // October quarterly
    case TER = 'TER'; // Termination
    case YE = 'YE'; // Year end
    case _90S = '90S'; // Post inaugural supplement
    case _90D = '90D'; // Post inaugural
    case _48H = '48H'; // 48-hour
    case _24H = '24H'; // 24-hour
}
