install.packages("tidycensus")
library(tidycensus)
library(dplyr)
library(sf)

this_dir <- dirname(rstudioapi::getSourceEditorContext()$path)
path <- file.path(this_dir, "..", "data", "precinct")
key <- trimws(readLines(file.path(path, "apikey.txt")))

census_api_key(key, install = TRUE)