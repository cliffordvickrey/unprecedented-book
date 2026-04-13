library(dplyr)
library(purrr)
library(sf)

this_dir <- dirname(rstudioapi::getSourceEditorContext()$path)
path <- file.path(this_dir, "..", "data", "precinct")

precincts_file = file.path(path, "2024-precincts.rds")
acs_tracts_file = file.path(path, "2024-acs-tracts.rds")
out_csv_file = file.path(path, "2024-precincts-merged.csv")
out_file = file.path(path, "2024-precincts-merged.rds")

precincts <- readRDS(precincts_file)
acs_tracts <- readRDS(acs_tracts_file)

# work with all vars starting with pct or median
acs_vars <- names(acs_tracts)[grepl("^area_|^ct_|^median_|^population", names(acs_tracts))]

# transform Census data from EPSG:4269 to WebMercator; compute area for each tract
acs_tracts <- acs_tracts |>
  st_transform(3857) |>
  mutate(area_tract = st_area(geometry))

# rename GEOIDs to avoid collision
inter_acs <- st_intersection(
  acs_tracts |> select(tract_GEOID = GEOID, all_of(acs_vars), area_tract),
  precincts |> select(precinct_GEOID = GEOID)
) |>
  mutate(area_inter = st_area(geometry),
         proportion = as.numeric(area_inter / area_tract))

# weight each ACS var by the above proportion (% tract is in each precinct)
df_acs_imputed <- map(acs_vars, function(v) {
  inter_acs |>
    mutate(weighted = .data[[v]] * proportion) |>
    st_drop_geometry() |>
    group_by(precinct_GEOID) |>
    summarize(!!v := sum(weighted, na.rm = TRUE), .groups = "drop")
}) |>
  reduce(left_join, by = "precinct_GEOID")

safe_divide <- function(num, denom) {
  ifelse(denom == 0 | is.na(denom), NA_real_, num / denom)
}

df_acs_imputed_with_percentages = df_acs_imputed |>
  mutate(
    pct_age_18_to_34 = safe_divide(ct_age_18_to_34, ct__age),
    pct_age_65_plus = safe_divide(ct_age_65_plus, ct__age),
    pct_asian_pi = safe_divide(ct_asian_pi, ct__race),
    pct_bachelors_plus = safe_divide(ct_bachelors_plus, ct__education),
    pct_below_poverty = safe_divide(ct_below_poverty, ct__poverty),
    pct_black = safe_divide(ct_black, ct__race),
    pct_hispanic = safe_divide(ct_hispanic, ct__race),
    pct_housing_burden = safe_divide(ct_housing_burden, ct__housing_burden),
    pct_inc_lt_40k = safe_divide(ct_inc_lt_40k, ct__inc),
    pct_no_vehicle = safe_divide(ct_no_vehicle, ct__vehicle),
    pct_non_bachelors = safe_divide(ct_non_bachelors, ct__education),
    pct_renters = safe_divide(ct_renters, ct__renters),
    pct_unemployed = safe_divide(ct_unemployed, ct__lab),
    pct_white_non_hispanic = safe_divide(ct_white_non_hispanic, ct__race),
    pop_density = safe_divide(population, area_sqmi)
  ) |>
  select(precinct_GEOID,
         starts_with("median_"),
         starts_with("pct_"),
         starts_with("pop_"),
  )

# join back with precinct data
precincts_merged <- precincts |>
  left_join(df_acs_imputed_with_percentages,
            by = c("GEOID" = "precinct_GEOID"))

# save RDS
saveRDS(precincts_merged, out_file)

# save CSV
df_csv <- precincts_merged |> st_drop_geometry()
write.csv(df_csv, out_csv_file, row.names = FALSE, na = "")