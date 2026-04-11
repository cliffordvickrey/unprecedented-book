library(tidycensus)
library(tidyr)
library(dplyr)
library(purrr)
library(sf)

options(tigris_use_cache = TRUE)

this_dir <- dirname(rstudioapi::getSourceEditorContext()$path)
path <- file.path(this_dir, "..", "data", "precinct")
out_file <- file.path(path, "2024-acs-tracts.rds")
out_csv_file <- file.path(path, "2024-acs-tracts.csv")

state_fips <- c(
  "01",
  "02",
  "04",
  "05",
  "06",
  "08",
  "09",
  "10",
  "11",
  "12",
  "13",
  "15",
  "16",
  "17",
  "18",
  "19",
  "20",
  "21",
  "22",
  "23",
  "24",
  "25",
  "26",
  "27",
  "28",
  "29",
  "30",
  "31",
  "32",
  "33",
  "34",
  "35",
  "36",
  "37",
  "38",
  "39",
  "40",
  "41",
  "42",
  "44",
  "45",
  "46",
  "47",
  "48",
  "49",
  "50",
  "51",
  "53",
  "54",
  "55",
  "56"
)

acs_vars <- c(
  age_total = "B01001_001",
  age_total_18_19_m = "B01001_007",
  age_total_20_m = "B01001_008",
  age_total_21_m = "B01001_009",
  age_total_22_24_m = "B01001_010",
  age_total_25_29_m = "B01001_011",
  age_total_30_34_m = "B01001_012",
  age_total_18_19_f = "B01001_031",
  age_total_20_f = "B01001_032",
  age_total_21_f = "B01001_033",
  age_total_22_24_f = "B01001_034",
  age_total_25_29_f = "B01001_035",
  age_total_30_34_f = "B01001_036",
  age_total_65_66_m = "B01001_020",
  age_total_67_69_m = "B01001_021",
  age_total_70_74_m = "B01001_022",
  age_total_75_79_m = "B01001_023",
  age_total_80_84_m = "B01001_024",
  age_total_85_plus_m = "B01001_025",
  age_total_65_66_f = "B01001_044",
  age_total_67_69_f = "B01001_045",
  age_total_70_74_f = "B01001_046",
  age_total_75_79_f = "B01001_047",
  age_total_80_84_f = "B01001_048",
  age_total_85_plus_f = "B01001_049",
  
  # education
  edu_total = "B15003_001",
  edu_total_bachelors = "B15003_022",
  edu_total_below_ba_1 = "B15003_002",
  edu_total_below_ba_2 = "B15003_003",
  edu_total_below_ba_3 = "B15003_004",
  edu_total_below_ba_4 = "B15003_005",
  edu_total_below_ba_5 = "B15003_006",
  edu_total_below_ba_6 = "B15003_007",
  edu_total_below_ba_7 = "B15003_008",
  edu_total_below_ba_8 = "B15003_009",
  edu_total_below_ba_9 = "B15003_010",
  edu_total_below_ba_10 = "B15003_011",
  edu_total_below_ba_11 = "B15003_012",
  edu_total_below_ba_12 = "B15003_013",
  edu_total_below_ba_13 = "B15003_014",
  edu_total_below_ba_14 = "B15003_015",
  edu_total_below_ba_15 = "B15003_016",
  edu_total_below_ba_16 = "B15003_017",
  edu_total_below_ba_17 = "B15003_018",
  edu_total_below_ba_18 = "B15003_019",
  edu_total_below_ba_19 = "B15003_020",
  edu_total_below_ba_20 = "B15003_021",
  edu_total_doctorate = "B15003_025",
  edu_total_masters = "B15003_023",
  edu_total_professional_school_degree = "B15003_024",
  
  # income
  inc_med_household_income = "B19013_001",
  
  # labor
  lab_total = "B23025_003",
  lab_total_unemployed = "B23025_005",
  
  # poverty
  pov_total = "B17001_001",
  pov_total_below_poverty = "B17001_002",
  
  # race (non-Hispanic/Latino)
  race_total = "B03002_001",
  race_total_white_nh  = "B03002_003",
  race_total_black_nh  = "B03002_004",
  race_total_asian_nh  = "B03002_006",
  race_total_pacific_islander_nh = "B03002_007",
  
  # race (Hispanic/Latino)
  race_total_hispanic = "B03002_012",
  race_total_white_hispanic  = "B03002_013",
  race_total_black_hispanic  = "B03002_014",
  race_total_asian_hispanic  = "B03002_016",
  race_total_pacific_islander_hispanic = "B03002_017",
  
  # rent
  rent_med_gross_rent = "B25064_001",
  rent_total = "B25003_001",
  rent_total_renters = "B25003_003",
  
  # rent burden
  rent_burden_total = "B25070_001",
  rent_burden_total_30_to_35 = "B25070_007",
  rent_burden_total_35_to_40 = "B25070_008",
  rent_burden_total_40_to_50 = "B25070_009",
  rent_burden_total_50_or_over = "B25070_010",
  
  # vehicle
  vehicle_total = "B08201_001",
  vehicle_total_no_vehicle = "B08201_002"
)

# create national ACS dataframe
national_acs <- map_dfr(
  state_fips,
  ~ get_acs(
    geography = "tract",
    variables = acs_vars,
    state = .x,
    year = 2024,
    survey = "acs5",
    geometry = TRUE
  )
)

# create national decennial Census frame (for population density)
national_decennial <- map_dfr(
  state_fips,
  ~ get_decennial(
    geography = "tract",
    variables = "P1_001N",
    state = .x,
    year = 2020,
    geometry = TRUE
  )
)

# compute population density
national_decennial <- national_decennial |>
  mutate(
    area_sqmi = as.numeric(st_area(geometry)) / 2589988.11,
    population = value
  ) |>
  select(GEOID, area_sqmi, population)

# unpack vars
national_acs_parsed <- national_acs |>
  select(-moe) |>
  pivot_wider(names_from = variable, values_from = estimate) |>
  mutate(
    ct__age = age_total,
	ct__education = edu_total,
	ct__lab = lab_total,
	ct__poverty = pov_total,
	ct__race = race_total,
	ct__rent = rent_total,
	ct__rent_burden_total = rent_burden_total,
	ct__vehicle_total = vehicle_total,
    ct_age_18_to_34 =
      age_total_18_19_m + age_total_20_m + age_total_21_m +
        age_total_22_24_m + age_total_25_29_m + age_total_30_34_m +
        age_total_18_19_f + age_total_20_f + age_total_21_f +
        age_total_22_24_f + age_total_25_29_f + age_total_30_34_f,
    ct_age_65_plus =
      age_total_65_66_m + age_total_67_69_m + age_total_70_74_m +
        age_total_75_79_m + age_total_80_84_m + age_total_85_plus_m +
        age_total_65_66_f + age_total_67_69_f + age_total_70_74_f +
        age_total_75_79_f + age_total_80_84_f + age_total_85_plus_f,
    ct_asian_pi = race_total_asian_hispanic + race_total_asian_nh +
        race_total_pacific_islander_hispanic + race_total_pacific_islander_nh,
    ct_bachelors_plus =
      edu_total_bachelors + edu_total_masters +
        edu_total_professional_school_degree + edu_total_doctorate, 
    ct_below_poverty = pov_total_below_poverty,
    ct_black = race_total_black_hispanic + race_total_black_nh,
    ct_hispanic = race_total_hispanic,
    ct_housing_burden = rent_burden_total_30_to_35 + rent_burden_total_35_to_40 +
        rent_burden_total_40_to_50 + rent_burden_total_50_or_over,
    ct_no_vehicle = vehicle_total_no_vehicle,
    ct_non_bachelors = edu_total_below_ba_1 + edu_total_below_ba_2 + edu_total_below_ba_3 +
        edu_total_below_ba_4 + edu_total_below_ba_5 + edu_total_below_ba_6 +
        edu_total_below_ba_7 + edu_total_below_ba_8 + edu_total_below_ba_9 +
        edu_total_below_ba_10 + edu_total_below_ba_11 + edu_total_below_ba_12 +
        edu_total_below_ba_13 + edu_total_below_ba_14 + edu_total_below_ba_15 +
        edu_total_below_ba_16 + edu_total_below_ba_17 + edu_total_below_ba_18 +
        edu_total_below_ba_19 + edu_total_below_ba_20,
	ct_renters = rent_total_renters,
    ct_unemployed = lab_total_unemployed,
    ct_white_non_hispanic = race_total_white_nh,
    median_income = inc_med_household_income,
    median_gross_rent = rent_med_gross_rent
  ) |>
  select(GEOID,
         NAME,
         starts_with("ct_"),
         starts_with("median_"),
         geometry)

# merge in population density
national_decennial_nogeo <- national_decennial |> 
  st_drop_geometry()

national_acs_parsed <- national_acs_parsed |>
  left_join(national_decennial_nogeo, by = "GEOID")

saveRDS(national_acs_parsed, out_file)

df_csv <- national_acs_parsed |>
  st_drop_geometry()

write.csv(df_csv, out_csv_file, row.names = FALSE, na = "")