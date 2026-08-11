<?php
/**
 * AB-27 + GE/UA/SY/AE konum paketleri (v1.2.0).
 * Ülke adı: 11 dil. İl/ilçe: yalnızca ülkenin kendi dili (default_locale).
 * AZ/TR dokunulmaz.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('memory_limit', '1024M');

$root = __DIR__;
$sourcesDir = $root.'/sources/dr5hn';
$dataI18n = $root.'/data/country-i18n.php';

if (! is_file($dataI18n)) {
    fwrite(STDERR, "Run generate-country-i18n.php first\n");
    exit(1);
}

/** @var array<string, array<string, mixed>> $countriesMeta */
$countriesMeta = include $dataI18n;

if (! is_dir($sourcesDir)) {
    mkdir($sourcesDir, 0755, true);
}

$combinedJson = $sourcesDir.'/countries-states-cities.json';
$combinedUrl = 'https://github.com/dr5hn/countries-states-cities-database/releases/download/v3.2-export.7/json-countries+states+cities.json.gz';

if (! is_file($combinedJson) || filesize($combinedJson) < 1000) {
    echo "Downloading combined JSON...\n";
    $gzPath = $sourcesDir.'/countries-states-cities.json.gz';
    passthru('curl.exe -L --fail --retry 3 -o "'.$gzPath.'" "'.$combinedUrl.'"', $code);
    if ($code !== 0) {
        exit(1);
    }
    file_put_contents($combinedJson, gzdecode((string) file_get_contents($gzPath)));
}

echo "Loading countries JSON...\n";
$countriesRaw = json_decode((string) file_get_contents($combinedJson), true, 512, JSON_THROW_ON_ERROR);
if (isset($countriesRaw['data']) && is_array($countriesRaw['data'])) {
    $countriesRaw = $countriesRaw['data'];
}

function placeKey(string $name): string
{
    $map = [
        'ş' => 's', 'Ş' => 's', 'ı' => 'i', 'İ' => 'i', 'I' => 'i',
        'ğ' => 'g', 'Ğ' => 'g', 'ü' => 'u', 'Ü' => 'u',
        'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
        'ä' => 'a', 'Ä' => 'a', 'å' => 'a', 'Å' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u',
        'ý' => 'y', 'ñ' => 'n', 'Ñ' => 'n',
        'č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z',
        'ł' => 'l', 'ń' => 'n', 'ę' => 'e', 'ą' => 'a',
        'ă' => 'a', 'ș' => 's', 'ț' => 't', 'ş' => 's', 'ţ' => 't',
    ];
    $s = strtr($name, $map);
    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? '';
    $s = trim($s, '-');

    return $s !== '' ? mb_substr($s, 0, 60) : 'x'.substr(md5($name), 0, 8);
}

function uniqueKey(string $base, array &$used): string
{
    $key = $base !== '' ? $base : 'x';
    if (! isset($used[$key])) {
        $used[$key] = true;

        return $key;
    }
    $i = 2;
    while (isset($used[$key.'-'.$i])) {
        $i++;
    }
    $key = $key.'-'.$i;
    $used[$key] = true;

    return $key;
}

/**
 * İl/ilçe: yalnızca ülkenin kendi dili.
 * native varsa onu, yoksa İngilizce name (kaynakta yerel yoksa).
 *
 * @return array<string, string>
 */
function placeNameLocal(string $defaultLocale, string $enName, string $native): array
{
    $label = $native !== '' ? $native : $enName;
    if ($label === '') {
        return [];
    }

    return [$defaultLocale => $label];
}

$wantedIso2 = [];
foreach ($countriesMeta as $meta) {
    $wantedIso2[strtoupper((string) $meta['iso2'])] = true;
}

$byIso2 = [];
foreach ($countriesRaw as $c) {
    if (! is_array($c)) {
        continue;
    }
    $iso2 = strtoupper((string) ($c['iso2'] ?? ''));
    if ($iso2 === '' || ! isset($wantedIso2[$iso2])) {
        continue;
    }
    $byIso2[$iso2] = $c;
}

echo 'Wanted countries='.count($byIso2).PHP_EOL;

$generated = [];
$preserveIds = ['az', 'tr'];
$packVersion = '1.2.0';

foreach ($countriesMeta as $meta) {
    $iso2 = strtoupper((string) $meta['iso2']);
    $id = (string) $meta['id'];
    $iso3 = (string) $meta['iso3'];
    $defaultLocale = (string) $meta['default_locale'];

    $src = $byIso2[$iso2] ?? null;
    if ($src === null) {
        fwrite(STDERR, "WARNING: skip $iso2\n");
        continue;
    }

    $states = is_array($src['states'] ?? null) ? $src['states'] : [];
    usort($states, fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

    $stateUsed = [];
    $packStates = [];
    $citiesTotal = 0;

    foreach ($states as $state) {
        if (! is_array($state)) {
            continue;
        }
        $enName = trim((string) ($state['name'] ?? ''));
        $native = trim((string) ($state['native'] ?? ''));
        $stateNames = placeNameLocal($defaultLocale, $enName, $native);
        if ($stateNames === []) {
            continue;
        }
        $labelForKey = $stateNames[$defaultLocale];
        $stateKey = uniqueKey(placeKey($labelForKey), $stateUsed);

        $cities = is_array($state['cities'] ?? null) ? $state['cities'] : [];
        usort($cities, fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        $cityUsed = [];
        $packCities = [];
        foreach ($cities as $city) {
            if (! is_array($city)) {
                continue;
            }
            $cityEn = trim((string) ($city['name'] ?? ''));
            $cityNative = trim((string) ($city['native'] ?? ''));
            $cityNames = placeNameLocal($defaultLocale, $cityEn, $cityNative);
            if ($cityNames === []) {
                continue;
            }
            $cityKey = uniqueKey(placeKey($cityNames[$defaultLocale]), $cityUsed);
            $packCities[] = [
                'key' => $cityKey,
                'name' => $cityNames,
            ];
        }

        if ($packCities === []) {
            $packCities[] = [
                'key' => $stateKey,
                'name' => $stateNames,
            ];
        }

        $citiesTotal += count($packCities);
        $packStates[] = [
            'key' => $stateKey,
            'name' => $stateNames,
            'cities' => $packCities,
        ];
    }

    if ($packStates === []) {
        fwrite(STDERR, "WARNING: no states $iso2\n");
        continue;
    }

    $pack = [
        'id' => $id,
        'iso2' => $iso2,
        'code' => $iso3,
        'version' => $packVersion,
        'default_locale' => $defaultLocale,
        'notes' => [
            'tr' => 'Ülke adı 11 dil. İl/ilçe yalnızca ülkenin kendi dilinde.',
            'en' => 'Country name in 11 locales. States/cities in the country language only.',
        ],
        'country' => [
            'name' => $meta['name'],
            'nationality' => $meta['nationality'],
        ],
        'states' => $packStates,
    ];

    $outDir = $root.'/packs/'.$id;
    if (! is_dir($outDir)) {
        mkdir($outDir, 0755, true);
    }

    $locationsPath = $outDir.'/locations.json';
    file_put_contents($locationsPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    file_put_contents(
        $outDir.'/pack.json',
        json_encode([
            'id' => $id,
            'version' => $packVersion,
            'iso2' => $iso2,
            'code' => $iso3,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );

    $fileBytes = (int) filesize($locationsPath);
    $estimateDisk = 4096 + (count($packStates) * 400) + ($citiesTotal * 450) + 2048;

    $generated[] = [
        'id' => $id,
        'iso2' => $iso2,
        'code' => $iso3,
        'version' => $packVersion,
        'default_locale' => $defaultLocale,
        'path' => 'packs/'.$id.'/locations.json',
        'states_count' => count($packStates),
        'cities_count' => $citiesTotal,
        'file_bytes' => $fileBytes,
        'estimate_disk_bytes' => $estimateDisk,
        'name' => $meta['name'],
    ];

    echo "OK $iso2 states=".count($packStates)." cities=$citiesTotal size=".round($fileBytes / 1048576, 2)."MB\n";
}

$manifestPath = $root.'/manifest.json';
$existingPacks = [];
if (is_file($manifestPath)) {
    $existing = json_decode((string) file_get_contents($manifestPath), true);
    foreach (($existing['packs'] ?? []) as $row) {
        $pid = (string) ($row['id'] ?? '');
        if (in_array($pid, $preserveIds, true)) {
            $existingPacks[] = $row;
        }
    }
}

$byId = [];
foreach ($existingPacks as $row) {
    $byId[(string) $row['id']] = $row;
}
foreach ($generated as $row) {
    $byId[(string) $row['id']] = $row;
}

$ordered = [];
foreach ($preserveIds as $pid) {
    if (isset($byId[$pid])) {
        $ordered[] = $byId[$pid];
        unset($byId[$pid]);
    }
}
ksort($byId);
foreach ($byId as $row) {
    $ordered[] = $row;
}

file_put_contents(
    $manifestPath,
    json_encode(['version' => 1, 'packs' => $ordered], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo 'DONE generated='.count($generated).PHP_EOL;
