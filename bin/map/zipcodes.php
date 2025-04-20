#!/usr/bin/php
<?php

/**
 * Create aggregate objects for zip and county data with COVID-19 and economic data.
 */

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Geo\County;
use CliffordVickrey\Book2024\Common\Entity\Geo\ZipCode;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Hydrator\EntityHydrator;
use CliffordVickrey\Book2024\Common\Repository\CountyRepository;
use CliffordVickrey\Book2024\Common\Repository\ZipCodeRepository;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    // region services

    $countyRepo = new CountyRepository();
    $hydrator = new EntityHydrator();
    $zipRepo = new ZipCodeRepository();

    // endregion

    // region hardcoded mappings

    /**
     * Map of counties from the zipcodeR package (https://zipcoder.39n.io/) to county names in the NYT COVID-19
     * datasets. Also, some ZIP codes straddle state (and therefore county) lines; thus zipcodeR reports the wrong
     * state on occasion. I approximate what the county should be.
     *
     * @var array<string, array<string, string>> $countyAliases
     */
    $countyAliases = [
        'AK' => [
            'Bristol Bay' => 'Bristol Bay plus Lake and Peninsula',
            'Lake and Peninsula' => 'Bristol Bay plus Lake and Peninsula',
            'Prince of Wales-Outer Ketchikan' => 'Ketchikan Gateway Borough',
            'Hoonah-Angoon' => 'Yakutat plus Hoonah-Angoon',
            'Wade Hampton' => 'Kusilvak Census Area',
            'Yakutat City and' => 'Yakutat plus Hoonah-Angoon',
            'Yakutat' => 'Yakutat plus Hoonah-Angoon',
        ],
        'AZ' => [
            'McKinley' => 'Apache',
            'San Juan' => 'Apache',
        ],
        'GA' => [
            'Cleburne' => 'Haralson',
        ],
        'IL' => [
            'La Salle' => 'LaSalle',
        ],
        'IN' => [
            'La Porte' => 'LaPorte',
        ],
        'LA' => [
            'La Salle' => 'LaSalle',
        ],
        'MD' => [
            'Mineral' => 'Allegany',
        ],
        'NH' => [
            'Coös' => 'Coos',
        ],
        'NM' => [
            'Doa Ana' => 'Doña Ana',
            'Dona Ana' => 'Doña Ana',
        ],
        'NY' => [
            'Bronx' => 'New York City',
            'Kings' => 'New York City',
            'Queens' => 'New York City',
            'Richmond County' => 'New York City',
        ],
        'ND' => [
            'McPherson' => 'McIntosh',
        ],
        'OR' => [
            'Modoc' => 'Klamath',
        ],
        'PR' => [
            'Aasco' => 'Anasco',
            'Bayamn' => 'Bayamon',
            'Bayamón' => 'Bayamon',
            'Canvanas' => 'Canovanas',
            'Cataño' => 'Catano',
            'Catao' => 'Catano',
            'Comero' => 'Comerio',
            'Gunica' => 'Guanica',
            'Juana Daz' => 'Juana Diaz',
            'Las Maras' => 'Las Marias',
            'Loza' => 'Loiza',
            'Manat' => 'Manati',
            'Mayagez' => 'Mayaguez',
            'Peuelas' => 'Penuelas',
            'Ro Grande' => 'Rio Grande',
            'San Germn' => 'San German',
            'San Sebastin' => 'San Sebastian',
            'Rincn' => 'Rincon',
        ],
        'PA' => [
            'Monongalia' => 'Fayette',
        ],
        'SD' => [
            'Shannon' => 'Oglala Lakota',
        ],
        'TX' => [
            'De Witt' => 'DeWitt',
        ],
        'VA' => [
            'McDowell' => 'Tazewell',
        ],
        'WV' => [
            'Martin' => 'Wayne',
            'Washington' => 'Jefferson',
        ],
    ];

    /**
     * FIPS codes missing in the NYT data.
     *
     * @var array<string, array<string, int>> $fips
     */
    $fips = [
        'AK' => [
            'Bristol Bay plus Lake and Peninsula' => 2060,
            'Yakutat plus Hoonah-Angoon' => 2105,
        ],
    ];

    /**
     * The Big Apple, baby! NYT reported COVID-19 data for the NY metro as a whole instead of by county, for some
     * reason. Here is COVID-19 data by borough.
     *
     * @var list<list<int|string>> $nyBoroughs
     */
    $nyBoroughs = [
        [
            '2023-07-23', // report date
            'Bronx',      // county (coterminous with borough)
            'New York',   // state
            36005,        // county FIPS
            516827,       // COVID-19 cases
            8526,         // COVID-19 deaths
        ],
        [
            '2023-07-23',
            'Kings',
            'New York',
            36047,
            921654,
            14264,
        ],
        [
            '2023-07-23',
            'New York',
            'New York',
            36061,
            595433,
            6186,
        ],
        [
            '2023-07-23',
            'Queens',
            'New York',
            36081,
            873689,
            13448,
        ],
        [
            '2023-07-23',
            'Richmond',
            'New York',
            36085,
            213140,
            2749,
        ],
    ];

    /**
     * Area suffixes; these are dropped when mapping from zipcodeR to the NYT COVID-19 data.
     */
    $areaTypes = [
        'Borough',
        'Census Area',
        'City',
        'City and Borough',
        'County',
        'Parish',
        'Municipality',
        'Municipio',
    ];

    // endregion

    // region counties

    $regexParts = implode('|', array_map(fn ($str) => preg_quote($str, '/'), $areaTypes));
    $countyRegexes = [
        sprintf('/\s(%s)$/', $regexParts),
        sprintf('/^(%s)\sof\s/', $regexParts),
    ];

    $counties = [];
    $countiesByFips = [];

    $stateCodes = array_flip(State::getDescriptions());

    $reader = new CsvReader(__DIR__.'/../../data/csv/us-counties.csv');
    $nyBoroughReader = new ArrayIterator($nyBoroughs);

    $reader->next();

    while ($reader->valid() || $nyBoroughReader->valid()) {
        $row = $reader->valid() ? $reader->current() : $nyBoroughReader->current();

        $remapped = [
            'date' => $row[0],
            'name' => $row[1],
            'state' => $row[2],
            'fips' => $row[3],
            'covid_cases' => $row[4],
            'covid_deaths' => $row[5],
        ];

        $county = County::__set_state($remapped);

        $stateCode = $stateCodes[strtoupper($county->state)] ?? null;

        Assert::string($stateCode, sprintf('Invalid state, "%s"', $county->state));
        $county->state = $stateCode;

        $county->slug = slugifyStateAndCounty($county->state, $county->name);
        $counties[$county->slug] = $county;

        if ($reader->valid()) {
            $reader->next();
            continue;
        }

        $nyBoroughReader->next();
    }

    $reader->close();

    foreach ($counties as $county) {
        if (null === $county->fips && isset($fips[$county->state][$county->name])) {
            $county->fips = $fips[$county->state][$county->name];
        }

        if (null !== $county->fips) {
            $countiesByFips[$county->fips] = $county;
        }
    }

    // endregion

    // region COVID-19 masking

    $reader = new CsvReader(__DIR__.'/../../data/csv/mask-use-by-county.csv');

    $reader->next();

    while ($reader->valid()) {
        $row = $reader->current();

        $fips = CastingUtilities::toInt($row[0]);
        Assert::notNull($fips);

        $county = $countiesByFips[$fips] ?? null;

        if (null === $county) {
            printf('Unknown county FIPS: %05d%s', $fips, \PHP_EOL);
            $reader->next();
            continue;
        }

        $arr = [
            'mask_never' => $row[1],
            'mask_rarely' => $row[2],
            'mask_sometimes' => $row[3],
            'mask_frequently' => $row[3],
            'mask_always' => $row[4],
        ];

        $hydrator->hydrate($county, $arr);

        $reader->next();
    }

    $reader->close();

    // endregion

    // region save counties

    array_walk($counties, fn (County $county) => $countyRepo->saveAggregate($county));

    // endregion

    // region zips
    $reader = new CsvReader(__DIR__.'/../../data/csv/zipcodes.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $zipCode = ZipCode::__set_state(array_combine($headers, $reader->current()));
        $countyName = $zipCode->county;
        $stateCode = $zipCode->state;

        if (null !== $countyName) {
            $countyName = trim($countyName);

            if (isset($countyAliases[$stateCode][$countyName])) {
                $countyName = $countyAliases[$stateCode][$countyName];
            } else {
                $countyName = array_reduce($countyRegexes, function (string $carry, string $regex): string {
                    $carry = preg_replace($regex, '', $carry);
                    Assert::string($carry);

                    return $carry;
                }, $countyName);

                $countyName = $countyAliases[$stateCode][$countyName] ?? $countyName;
            }

            $countyName = array_reduce(
                isset($counties[slugifyStateAndCounty($stateCode, $countyName)]) ? [] : $areaTypes,
                function (string $carry, string $areaType) use ($stateCode, $counties): string {
                    $patterns = ['%s %s', '%2$s of %1$s'];

                    foreach ($patterns as $pattern) {
                        $countyToTest = vsprintf($pattern, [$carry, $areaType]);

                        if (isset($counties[slugifyStateAndCounty($stateCode, $countyToTest)])) {
                            return $countyToTest;
                        }
                    }

                    return $carry;
                },
                $countyName
            );

            $slug = slugifyStateAndCounty($stateCode, $countyName);

            if (!isset($counties[$slug])) {
                printf('Invalid county, "%s" (%s)%s', $countyName, $zipCode->state, \PHP_EOL);
            }

            $zipCode->county = $slug;
        }

        $zipCode->zipcode = ZipCode::normalizeZip($zipCode->zipcode);
        $zipCode->slug = ZipCode::slugifyZip($zipCode->zipcode);

        $zipRepo->saveAggregate($zipCode);

        $reader->next();
    }

    $reader->close();
    // endregion
});

function slugifyStateAndCounty(string $stateCode, string $countyName): string
{
    return strtolower(sprintf('%s_%s', $stateCode, StringUtilities::slugify($countyName)));
}
