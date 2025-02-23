# Scripts
## Data pipeline
### A. `./fec-bulk` (FEC bulk files)
1. `bulk-download.php`: downloads latest bulk files from the FEC
2. `concatenate-bulk-downloads.php`: parses and merges the downloaded bulk files
3. `build-aggregates.php`: creates and serializes candidate and committee objects from the FEC bulk files
### B. `./fec-api` (FEC API data)
1. `import-json.php`: converts payloads from FEC's RESTful API into smaller CSVs. These CSVs represent unitemized individual contributions routed through ActBlue and WinRed
2. `parse-receipts.php`: concatenates data from the FEC bulk files (itemized receipts) and API (unitemized receipts), and stores them in CSVs for each FEC committee
### C. `./match` (create unique donor IDs)
1. `group.php`: group donors by very approximate similarity
2. `chunk-groups.php`: chunk donors by their computed group, allowing them to be stored in memory one group at a time
3. `match.php`: using old string similarity algorithms, compare every donor in each group to every other donor in the group, and impute a unique donor ID
4. `merge.php`: merge the imputed donor IDs into the receipt CSVs
### D. `./panel` (panelize receipt data)
1. `chunk-receipts.php`: group receipts into chunks of 1,000 unique donors
2. `panelize.php`: create objects encapsulating the giving history of each donor
### E. `./profile` (profile donors)
1. `profile.php`: analyze panel objects and report totals by category and jurisdiction
## Misc. scripts
* `./fec-bulk/guess-primary-winners.php`: guesses House/Senate primary winners by contribution totals. Used to help map certain ActBlue receipts (held in escrow) to committee IDs
* `./map/analyse-geojson.php`: computes geographic coordinates and dimensions for each state, to assist in visualizing maps
* `./map/geojson.php`: splits the 2020 Census national TIGER map of ZCTAs into states
* `./report/bulk-report.php`: reports totals in the FEC bulk file by transaction type
* `./committee-by-period.php`: tracks Biden/Harris/Trump individual contributions by reporting period, to validate receipts against known reported figures
* `./daily-receipts.php`: reports daily Biden/Harris/Trump individual contributions
* `./report-receipts.php`: reports imputed committee totals by cycle for validation purposes