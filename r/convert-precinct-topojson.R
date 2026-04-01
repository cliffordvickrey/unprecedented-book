library(dplyr)
library(purrr)
library(sf)

this_dir <- dirname(rstudioapi::getSourceEditorContext()$path)
path <- file.path(this_dir, "..", "data", "precinct")

prec_2020_file <- file.path(path, "2020-precincts-with-results.geojson")
prec_2024_file <- file.path(path, "2024-precincts-with-results.geojson")
out_file <- file.path(path, "2024-precincts.rds")
out_csv_file <- file.path(path, "2024-precincts-with-2020-results.csv");

# read
prec_2020 <- st_read(prec_2020_file)
prec_2024 <- st_read(prec_2024_file)

# clean, recalculate vertices, and project to Web Mercador
prec_2020 <- st_transform(st_make_valid(prec_2020), 3857) |> st_buffer(0)
prec_2024 <- st_transform(st_make_valid(prec_2024), 3857) |> st_buffer(0)

# compute area
prec_2020 <- prec_2020 |> mutate(area_2020 = st_area(geometry))

vote_vars <- c("votes_dem", "votes_rep", "votes_total")

# intersect and prune to just the vars we need
inter <- st_intersection(prec_2020 |> select(all_of(vote_vars), area_2020),
                         prec_2024 |> select(GEOID))

# calculate proportion of each 2020 precinct to 2024 precinct
inter <- inter |>
  mutate(area_inter = st_area(geometry),
         proportion = as.numeric(area_inter / area_2020))

# impute votes
df_with_imputations <- map(vote_vars, function(vote_var) {
  inter |>
    mutate(imputed = .data[[vote_var]] * proportion) |> # impute votes (2020 vote * proportion)
    st_drop_geometry() |> # drop GIS junk (no longer needed)
    group_by(GEOID) |> # group intersections by 2024 precinct boundaries
    summarize(!!paste0(vote_var, "_2020") := sum(imputed, na.rm = TRUE),
              .groups = "drop") # allocate new variable
}) |>
  reduce(left_join, by = "GEOID") # now: merge the vote totals into one DF

# join back to the 2024 totals
prec_2024 <- prec_2024 |>
  left_join(df_with_imputations, by = "GEOID")

# write RData
saveRDS(prec_2024, out_file)

# write CSV
df_csv <- prec_2024 |>
  st_drop_geometry()

write.csv(df_csv, out_csv_file, row.names = FALSE, na = "")
