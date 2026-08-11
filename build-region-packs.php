<?php
/**
 * AB-27 + GE/UA/SY/AE konum paketleri üretici.
 * Kaynak: dr5hn/countries-states-cities-database (ODbL).
 * AZ/TR paketlerine dokunmaz.
 */

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
    echo "Downloading combined countries+states+cities...\n";
    $gzPath = $sourcesDir.'/countries-states-cities.json.gz';
    // curl daha güvenilir (redirect + SSL)
    // Windows: + içeren URL’yi doğrudan tırnakla ver
    $cmd = 'curl.exe -L --fail --retry 3 -o "'.$gzPath.'" "'.$combinedUrl.'"';
    passthru($cmd, $code);
    if ($code !== 0 || ! is_file($gzPath)) {
        fwrite(STDERR, "Download failed (exit $code)\n");
        exit(1);
    }
    $raw = file_get_contents($gzPath);
    $json = gzdecode($raw);
    if ($json === false) {
        fwrite(STDERR, "gunzip failed\n");
        exit(1);
    }
    file_put_contents($combinedJson, $json);
    echo 'Saved '.strlen($json)." bytes\n";
} else {
    echo "Cache hit: $combinedJson\n";
}

echo "Loading JSON (memory)...\n";
ini_set('memory_limit', '1024M');
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

echo 'Wanted='.count($wantedIso2).' matched='.count($byIso2).PHP_EOL;

$generated = [];
$preserveIds = ['az', 'tr'];

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
        $stateName = trim((string) ($state['native'] ?? ''));
        if ($stateName === '') {
            $stateName = trim((string) ($state['name'] ?? ''));
        }
        if ($stateName === '') {
            continue;
        }
        $stateKey = uniqueKey(placeKey($stateName), $stateUsed);
        $cities = is_array($state['cities'] ?? null) ? $state['cities'] : [];
        usort($cities, fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        $cityUsed = [];
        $packCities = [];
        foreach ($cities as $city) {
            if (! is_array($city)) {
                continue;
            }
            $cityName = trim((string) ($city['native'] ?? ''));
            if ($cityName === '') {
                $cityName = trim((string) ($city['name'] ?? ''));
            }
            if ($cityName === '') {
                continue;
            }
            $cityKey = uniqueKey(placeKey($cityName), $cityUsed);
            $packCities[] = [
                'key' => $cityKey,
                'name' => [$defaultLocale => $cityName],
            ];
        }

        if ($packCities === []) {
            $packCities[] = [
                'key' => $stateKey,
                'name' => [$defaultLocale => $stateName],
            ];
        }

        $citiesTotal += count($packCities);
        $packStates[] = [
            'key' => $stateKey,
            'name' => [$defaultLocale => $stateName],
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
        'version' => '1.0.0',
        'default_locale' => $defaultLocale,
        'notes' => [
            'tr' => 'Bölge/eyalet + şehir (dr5hn). Ülke adı 11 dilde. Kaynak ODbL.',
            'en' => 'States/regions + cities (dr5hn). Country name in 11 locales. ODbL source.',
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

    // Compact JSON (büyük ülkeler için boyut)
    file_put_contents(
        $outDir.'/locations.json',
        json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
    );
    file_put_contents(
        $outDir.'/pack.json',
        json_encode([
            'id' => $id,
            'version' => '1.0.0',
            'iso2' => $iso2,
            'code' => $iso3,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );

    $generated[] = [
        'id' => $id,
        'iso2' => $iso2,
        'code' => $iso3,
        'version' => '1.0.0',
        'default_locale' => $defaultLocale,
        'path' => 'packs/'.$id.'/locations.json',
        'states_count' => count($packStates),
        'cities_count' => $citiesTotal,
        'name' => $meta['name'],
    ];

    $sizeMb = round(filesize($outDir.'/locations.json') / 1048576, 2);
    echo "OK $iso2 states=".count($packStates)." cities=$citiesTotal size={$sizeMb}MB\n";
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
