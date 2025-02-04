<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum DonorCharacteristic: string
{
    case amt_up_to_1 = 'amt_up_to_1';
    case amt_up_to_200 = 'amt_up_to_200';
    case amt_up_to_1000 = 'amt_up_to_1000';
    case amt_up_to_2800 = 'amt_up_to_2800';
    case amt_more_than_2800 = 'amt_more_than_2800';
    case prior = 'prior';
    case day_one_launch = 'day_one_launch';
    case week_one_launch = 'week_one_launch';
    case weekly = 'weekly';
    case monthly = 'monthly';
    case cycle_2016_clinton = 'cycle_2016_clinton';
    case cycle_2016_sanders = 'cycle_2016_sanders';
    case cycle_2016_trump = 'cycle_2016_trump';
    case cycle_2016_dem_pres = 'cycle_2016_dem_pres';
    case cycle_2016_gop_pres = 'cycle_2016_gop_pres';
    case cycle_2016_dem_non_pres = 'cycle_2016_dem_non_pres';
    case cycle_2016_gop_non_pres = 'cycle_2016_gop_non_pres';
    case cycle_2016_party_elite = 'cycle_2016_party_elite';
    case cycle_2016_super_pac = 'cycle_2016_super_pac';
    case cycle_2020_trump = 'cycle_2020_trump';
    case cycle_2020_biden = 'cycle_2020_biden';
    case cycle_2020_progressive = 'cycle_2020_progressive';
    case cycle_2020_non_biden = 'cycle_2020_non_biden';
    case cycle_2020_dem_non_pres = 'cycle_2020_dem_non_pres';
    case cycle_2020_gop_non_pres = 'cycle_2020_gop_non_pres';
    case cycle_2020_party_elite = 'cycle_2020_party_elite';
    case cycle_2020_super_pac = 'cycle_2020_super_pac';
    case cycle_2024_biden = 'cycle_2024_biden';
    case cycle_2024_harris = 'cycle_2024_harris';
    case cycle_2024_trump = 'cycle_2024_trump';
    case cycle_2024_desantis = 'cycle_2024_desantis';
    case cycle_2024_haley = 'cycle_2024_haley';
    case cycle_2024_rfk_jr = 'cycle_2024_rfk_jr';
    case cycle_2024_non_trump = 'cycle_2024_non_trump';
    case cycle_2024_dem_non_pres = 'cycle_2024_dem_non_pres';
    case cycle_2024_gop_non_pres = 'cycle_2024_gop_non_pres';
    case cycle_2024_party_elite = 'cycle_2024_party_elite';
    case cycle_2024_super_pac = 'cycle_2024_super_pac';
}
