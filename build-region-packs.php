<?php
/**
 * AB-27 + GE/UA/SY/AE konum paketleri üretici (v1.1.0).
 * İl/ilçe adları: dr5hn translations.csv (11 dil) + name/native.
 * AZ/TR dokunulmaz.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('memory_limit', '1024M');

$root = __DIR__;
$sourcesDir = $root.'/sources/dr5hn';
$dataI18n = $root.'/data/country-i18n.php';
$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];

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
$translationsCsv = $sourcesDir.'/translations.csv';
$translationsGzUrl = 'https://github.com/dr5hn/countries-states-cities-database/releases/download/v3.2-export.7/csv-translations.csv.gz';

if (! is_file($combinedJson) || filesize($combinedJson) < 1000) {
    echo "Downloading combined JSON...\n";
    $gzPath = $sourcesDir.'/countries-states-cities.json.gz';
    passthru('curl.exe -L --fail --retry 3 -o "'.$gzPath.'" "'.$combinedUrl.'"', $code);
    if ($code !== 0) {
        exit(1);
    }
    file_put_contents($combinedJson, gzdecode((string) file_get_contents($gzPath)));
}

if (! is_file($translationsCsv) || filesize($translationsCsv) < 1000) {
    echo "Downloading translations CSV...\n";
    $gzPath = $sourcesDir.'/translations.csv.gz';
    passthru('curl.exe -L --fail --retry 3 -o "'.$gzPath.'" "'.$translationsGzUrl.'"', $code);
    if ($code !== 0) {
        exit(1);
    }
    file_put_contents($translationsCsv, gzdecode((string) file_get_contents($gzPath)));
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
 * @param  array<string, string>  $trMap  "state:123:uk" => "Харківська"
 * @param  list<string>  $locales
 * @return array<string, string>
 */
function buildPlaceNames(int $placeId, string $type, string $enName, string $native, string $defaultLocale, array $trMap, array $locales): array
{
    $out = [];
    foreach ($locales as $loc) {
        $k = $type.':'.$placeId.':'.$loc;
        if (isset($trMap[$k]) && trim($trMap[$k]) !== '') {
            $out[$loc] = trim($trMap[$k]);
        }
    }
    if ($enName !== '') {
        $out['en'] = $out['en'] ?? $enName;
    }
    if ($native !== '') {
        // Yerel yazım varsayılan dilde (CSV yoksa)
        $out[$defaultLocale] = $out[$defaultLocale] ?? $native;
    }
    if ($out === [] && $enName !== '') {
        $out[$defaultLocale] = $enName;
        $out['en'] = $enName;
    }

    return $out;
}

$wantedIso2 = [];
foreach ($countriesMeta as $meta) {
    $wantedIso2[strtoupper((string) $meta['iso2'])] = true;
}

$byIso2 = [];
$wantedStateIds = [];
$wantedCityIds = [];
foreach ($countriesRaw as $c) {
    if (! is_array($c)) {
        continue;
    }
    $iso2 = strtoupper((string) ($c['iso2'] ?? ''));
    if ($iso2 === '' || ! isset($wantedIso2[$iso2])) {
        continue;
    }
    $byIso2[$iso2] = $c;
    foreach ($c['states'] ?? [] as $s) {
        if (! is_array($s)) {
            continue;
        }
        $sid = (int) ($s['id'] ?? 0);
        if ($sid > 0) {
            $wantedStateIds[$sid] = true;
        }
        foreach ($s['cities'] ?? [] as $city) {
            if (! is_array($city)) {
                continue;
            }
            $cid = (int) ($city['id'] ?? 0);
            if ($cid > 0) {
                $wantedCityIds[$cid] = true;
            }
        }
    }
}

echo 'Wanted countries='.count($byIso2).' states='.count($wantedStateIds).' cities='.count($wantedCityIds).PHP_EOL;
echo "Indexing translations CSV (stream)...\n";

/** @var array<string, string> $trMap */
$trMap = [];
$localeSet = array_fill_keys($locales, true);
$fh = fopen($translationsCsv, 'rb');
if ($fh === false) {
    fwrite(STDERR, "Cannot open translations.csv\n");
    exit(1);
}
fgetcsv($fh, 0, ',', '"', '\\');
$matched = 0;
while (($row = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
    if (count($row) < 4) {
        continue;
    }
    $pid = (int) $row[0];
    $type = (string) $row[1];
    $lang = (string) $row[2];
    $text = trim((string) $row[3]);
    if ($text === '' || ! isset($localeSet[$lang])) {
        continue;
    }
    if ($type === 'state' && isset($wantedStateIds[$pid])) {
        $trMap['state:'.$pid.':'.$lang] = $text;
        $matched++;
    } elseif ($type === 'city' && isset($wantedCityIds[$pid])) {
        $trMap['city:'.$pid.':'.$lang] = $text;
        $matched++;
    }
}
fclose($fh);
echo "Translation entries matched=$matched\n";

$generated = [];
$preserveIds = ['az', 'tr'];
$packVersion = '1.1.0';

foreach ($countriesMeta as $meta) {
    $iso2 = strtoupper((string) $meta['iso2']);
    $id = (string) $meta['id'];
    $iso3 = (string) $meta['iso3'];
    $defaultLocale = (string) $meta['default_locale'];

    $src = $byIso2[$iso2] ?? null;
    if ($src === null) {
        fwrite(STDERR, "WARNING: no dr5hn country for $iso2 — skip\n");
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
        $labelForKey = $native !== '' ? $native : $enName;
        if ($labelForKey === '') {
            continue;
        }
        $stateId = (int) ($state['id'] ?? 0);
        $stateKey = uniqueKey(placeKey($labelForKey), $stateUsed);
        $stateNames = buildPlaceNames($stateId, 'state', $enName, $native, $defaultLocale, $trMap, $locales);

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
            $cityLabel = $cityNative !== '' ? $cityNative : $cityEn;
            if ($cityLabel === '') {
                continue;
            }
            $cityId = (int) ($city['id'] ?? 0);
            $cityKey = uniqueKey(placeKey($cityLabel), $cityUsed);
            $packCities[] = [
                'key' => $cityKey,
                'name' => buildPlaceNames($cityId, 'city', $cityEn, $cityNative, $defaultLocale, $trMap, $locales),
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
        fwrite(STDERR, "WARNING: no states for $iso2 — skip\n");
        continue;
    }

    $pack = [
        'id' => $id,
        'iso2' => $iso2,
        'code' => $iso3,
        'version' => $packVersion,
        'default_locale' => $defaultLocale,
        'notes' => [
            'tr' => 'Bölge + şehir; adlar dr5hn çevirileri (11 dil) + native/en. ODbL.',
            'en' => 'States + cities; names from dr5hn translations (11 locales) + native/en. ODbL.',
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
    $estimateDisk = 4096 + (count($packStates) * 400) + ($citiesTotal * 450) + ($citiesTotal * 200) + 2048;

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

    echo 'OK '.$iso2.' states='.count($packStates).' cities='.$citiesTotal.' size='.round($fileBytes / 1048576, 2)."MB\n";
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

echo 'DONE packs='.count($ordered).' generated='.count($generated).PHP_EOL;

// Hızlı doğrulama: UA Kharkiv
$ua = json_decode((string) file_get_contents($root.'/packs/ua/locations.json'), true);
foreach ($ua['states'] as $s) {
    $en = $s['name']['en'] ?? '';
    if (stripos($en, 'Kharkiv') !== false || stripos($en, 'Kharkov') !== false) {
        echo 'SAMPLE Kharkiv en='.$en.' uk='.($s['name']['uk'] ?? '').' ru='.($s['name']['ru'] ?? '').PHP_EOL;
        break;
    }
}
