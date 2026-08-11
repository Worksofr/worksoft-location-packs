<?php
/**
 * Türkiye konum paketi v1.0.0
 * Yalnızca il (state) + ilçe (city). Köy yok.
 * Ülke adı / uyruk: 11 dil. İl ve ilçe adları Türkçe (özel ad).
 *
 * Kaynak: sources/turkey-locations.json (turkiyeapi.dev il/ilçe).
 */

$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];

$sourcePath = __DIR__.'/sources/turkey-locations.json';
if (! is_file($sourcePath)) {
    fwrite(STDERR, "Missing sources/turkey-locations.json\n");
    exit(1);
}

/** @var array<string, list<string>> $map */
$map = json_decode((string) file_get_contents($sourcePath), true, 512, JSON_THROW_ON_ERROR);

/**
 * İl/ilçe için tüm locale’lerde aynı Türkçe ad (özel isimler çevrilmez).
 *
 * @return array<string, string>
 */
function placeName(string $tr, array $locales): array
{
    $out = [];
    foreach ($locales as $loc) {
        $out[$loc] = $tr;
    }

    return $out;
}

/**
 * ASCII slug anahtarı (Türkçe karakterler).
 */
function placeKey(string $name): string
{
    $map = [
        'ş' => 's', 'Ş' => 's', 'ı' => 'i', 'İ' => 'i', 'I' => 'i',
        'ğ' => 'g', 'Ğ' => 'g', 'ü' => 'u', 'Ü' => 'u',
        'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
    ];
    $s = strtr($name, $map);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    $s = trim($s, '-');

    return $s !== '' ? $s : 'x'.substr(md5($name), 0, 8);
}

$countryNames = [
    'tr' => 'Türkiye',
    'en' => 'Turkey',
    'ar' => 'تركيا',
    'az' => 'Türkiyə',
    'de' => 'Türkei',
    'fr' => 'Turquie',
    'ka' => 'თურქეთი',
    'pl' => 'Turcja',
    'ro' => 'Turcia',
    'ru' => 'Турция',
    'uk' => 'Туреччина',
];

$nationality = [
    'tr' => 'Türk',
    'en' => 'Turkish',
    'ar' => 'تركي',
    'az' => 'Türk',
    'de' => 'Türke',
    'fr' => 'Turc',
    'ka' => 'თურქი',
    'pl' => 'Turek',
    'ro' => 'Turc',
    'ru' => 'Турок',
    'uk' => 'Турок',
];

$states = [];
$citiesTotal = 0;
$usedStateKeys = [];

foreach ($map as $province => $districts) {
    $stateKey = placeKey($province);
    if (isset($usedStateKeys[$stateKey])) {
        $stateKey .= '-'.substr(md5($province), 0, 4);
    }
    $usedStateKeys[$stateKey] = true;

    $cities = [];
    $usedCityKeys = [];
    foreach ($districts as $district) {
        $district = trim((string) $district);
        if ($district === '') {
            continue;
        }
        $cityKey = placeKey($district);
        if (isset($usedCityKeys[$cityKey])) {
            $cityKey .= '-'.substr(md5($district), 0, 4);
        }
        $usedCityKeys[$cityKey] = true;

        $cities[] = [
            'key' => $cityKey,
            'name' => placeName($district, $locales),
        ];
    }

    $citiesTotal += count($cities);

    $states[] = [
        'key' => $stateKey,
        'name' => placeName($province, $locales),
        'cities' => $cities,
    ];
}

$pack = [
    'id' => 'tr',
    'iso2' => 'TR',
    'code' => 'TR',
    'version' => '1.0.0',
    'default_locale' => 'tr',
    'notes' => [
        'tr' => 'Yalnızca 81 il ve ilçeler. Köy / mahalle yok. Ülke adı 11 dilde.',
        'en' => '81 provinces and districts only. No villages. Country name in 11 locales.',
    ],
    'country' => [
        'name' => $countryNames,
        'nationality' => $nationality,
    ],
    'states' => $states,
];

$outDir = __DIR__.'/packs/tr';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

file_put_contents(
    $outDir.'/locations.json',
    json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

file_put_contents(
    $outDir.'/pack.json',
    json_encode([
        'id' => 'tr',
        'version' => '1.0.0',
        'iso2' => 'TR',
        'code' => 'TR',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

// Manifest: mevcut paketleri koru, tr ekle/güncelle
$manifestPath = __DIR__.'/manifest.json';
$manifest = [
    'version' => 1,
    'packs' => [],
];
if (is_file($manifestPath)) {
    $existing = json_decode((string) file_get_contents($manifestPath), true);
    if (is_array($existing)) {
        $manifest['version'] = (int) ($existing['version'] ?? 1);
        $manifest['packs'] = is_array($existing['packs'] ?? null) ? $existing['packs'] : [];
    }
}

$trMeta = [
    'id' => 'tr',
    'iso2' => 'TR',
    'code' => 'TR',
    'version' => '1.0.0',
    'default_locale' => 'tr',
    'path' => 'packs/tr/locations.json',
    'states_count' => count($states),
    'cities_count' => $citiesTotal,
    'name' => $countryNames,
];

$found = false;
foreach ($manifest['packs'] as $i => $p) {
    if (($p['id'] ?? '') === 'tr') {
        $manifest['packs'][$i] = $trMeta;
        $found = true;
        break;
    }
}
if (! $found) {
    $manifest['packs'][] = $trMeta;
}

file_put_contents(
    $manifestPath,
    json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo 'OK states='.count($states).' cities='.$citiesTotal.PHP_EOL;
