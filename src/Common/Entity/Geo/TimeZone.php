<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Geo;

enum TimeZone: string
{
    case Central = 'Central';
    case America_Anchorage = 'America/Anchorage';
    case America_Adak = 'America/Adak';
    case America_Nome = 'America/Nome';
    case Napakiak = 'Napakiak';
    case America_Yakutat = 'America/Yakutat';
    case America_Juneau = 'America/Juneau';
    case America_Sitka = 'America/Sitka';
    case America_Metlakatla = 'America/Metlakatla';
    case America_Phoenix = 'America/Phoenix';
    case Mountain = 'Mountain';
    case Pacific = 'Pacific';
    case Eastern = 'Eastern';
    case Pacific_Honolulu = 'Pacific/Honolulu';
    case America_Boise = 'America/Boise';
    case America_Indiana_Indianapolis = 'America/Indiana/Indianapolis';
    case America_Indiana_Knox = 'America/Indiana/Knox';
    case America_Indiana_Winamac = 'America/Indiana/Winamac';
    case America_Indiana_Vevay = 'America/Indiana/Vevay';
    case America_Indiana_Marengo = 'America/Indiana/Marengo';
    case America_Indiana_Vincennes = 'America/Indiana/Vincennes';
    case America_Indiana_Tell_City = 'America/Indiana/Tell_City';
    case America_Indiana_Petersburg = 'America/Indiana/Petersburg';
    case America_Kentucky_Monticello = 'America/Kentucky/Monticello';
    case America_Menominee = 'America/Menominee';
    case America_North_Dakota_New_Salem = 'America/North_Dakota/New_Salem';
    case America_North_Dakota_Beulah = 'America/North_Dakota/Beulah';
    case America_North_Dakota_Center = 'America/North_Dakota/Center';
    case America_Puerto_Rico = 'America/Puerto_Rico';
}
