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
acs_vars <- names(acs_tracts)[grepl("^pct_|^median_", names(acs_tracts))]

# transform Census data from EPSG:4269 to WebMercator; compute area for each tract
acs_tracts <- acs_tracts |>
  st_transform(3857) |>
  mutate(area_tract = st_area(geometry))

# rename GEOIDs to avoid collision
inter_acs <- st_intersection(
  acs_tracts |> select(tract_GEOID = GEOID, all_of(acs_vars), area_tract),
  precincts |> select(precinct_GEOID = GEOID)
) |>
  mutate(
    area_inter = st_area(geometry),
    proportion = as.numeric(area_inter / area_tract)
  )

# weight each ACS var by the above proportion (% tract is in each precinct)
df_acs_imputed <- map(acs_vars, function(v) {
  inter_acs |>
    mutate(weighted = .data[[v]] * proportion) |>
    st_drop_geometry() |>
    group_by(precinct_GEOID) |>
    summarize(
      !!v := sum(weighted, na.rm = TRUE),
      .groups = "drop"
    )
}) |>
  reduce(left_join, by = "precinct_GEOID")

# join back with precinct data
precincts_merged <- precincts |>
  left_join(df_acs_imputed, by = c("GEOID" = "precinct_GEOID"))

# save RDS
saveRDS(precincts_merged, out_file)

# save CSV
df_csv <- precincts_merged |> st_drop_geometry()
write.csv(df_csv, out_csv_file, row.names = FALSE, na = "")