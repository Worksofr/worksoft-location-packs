<?php
/**
 * Arapça default_locale paketleri (sy, ae) v1.3.0
 * İl/ilçe yalnızca Arapça (Arap alfabesi). Geonames alternatif adları.
 *
 * Kullanım: php enrich-ar-packs.php
 */
declare(strict_types=1);

ini_set('memory_limit', '1024M');

$root = __DIR__;
$srcJson = $root.'/sources/dr5hn/countries-states-cities.json';
$i18nPath = $root.'/data/country-i18n.php';

if (! is_file($srcJson) || ! is_file($i18nPath)) {
    fwrite(STDERR, "Missing dr5hn/i18n\n");
    exit(1);
}

/** @var array<string, array<string, mixed>> $countriesMeta */
$countriesMeta = include $i18nPath;

$targets = [];
foreach ($countriesMeta as $row) {
    if (($row['default_locale'] ?? '') !== 'ar') {
        continue;
    }
    $targets[] = [
        'id' => (string) $row['id'],
        'iso2' => strtoupper((string) $row['iso2']),
        'iso3' => (string) $row['iso3'],
        'meta' => $row,
    ];
}

if ($targets === []) {
    fwrite(STDERR, "No Arabic default_locale packs\n");
    exit(1);
}

function placeKey(string $name): string
{
    $s = mb_strtolower($name, 'UTF-8');
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s) ?? '';
    $s = trim($s, '-');
    if ($s === '' || ! preg_match('/^[a-z0-9-]+$/u', $s)) {
        return 'x'.substr(md5($name), 0, 10);
    }

    return mb_substr($s, 0, 60);
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

function foldLatin(string $s): string
{
    $map = [
        'ā' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
        'ē' => 'e', 'é' => 'e', 'è' => 'e', 'ê' => 'e',
        'ī' => 'i', 'í' => 'i', 'ì' => 'i', 'î' => 'i',
        'ō' => 'o', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o',
        'ū' => 'u', 'ú' => 'u', 'ù' => 'u', 'û' => 'u',
        'ş' => 's', 'ș' => 's', 'ţ' => 't', 'ț' => 't',
        '’' => '', 'ʻ' => '', '`' => '', "'" => '', 'ʹ' => '', 'ʼ' => '', '′' => '',
        'ʿ' => '', 'ʾ' => '',
    ];
    $s = strtr($s, $map);
    $s = mb_strtolower($s, 'UTF-8');
    // Al-/Ad-/Ash- öneklerini yumuşat
    $s = preg_replace('/^(al|ad|ar|as|ash|az|an|el)-?/u', '', $s) ?? $s;
    $s = str_replace(['kh', 'gh', 'sh', 'th', 'dh', 'zh', 'ch'], ['h', 'g', 's', 't', 'd', 'z', 'c'], $s);
    $s = preg_replace('/[^a-z0-9]+/u', '', $s) ?? '';

    return $s;
}

function pickArabicFromAlts(string $primary, string $altsCsv): ?string
{
    $parts = array_merge([$primary], explode(',', $altsCsv));
    $ar = [];
    foreach ($parts as $raw) {
        $n = trim($raw);
        if ($n === '' || ! preg_match('/\p{Arabic}/u', $n)) {
            continue;
        }
        // Latin / Kiril karışık ele
        if (preg_match('/[A-Za-zА-Яа-яЁё]/u', $n)) {
            continue;
        }
        // Çok uzun açıklamalar
        if (mb_strlen($n) > 80) {
            continue;
        }
        $ar[] = $n;
    }
    if ($ar === []) {
        return null;
    }
    usort($ar, static fn (string $a, string $b): int => mb_strlen($a) <=> mb_strlen($b));

    return $ar[0];
}

function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

    return 2 * $r * asin(min(1.0, sqrt($a)));
}

/**
 * @return list<array{ascii:string,fold:string,ar:string,lat:float,lng:float,rank:int}>
 */
function loadGeonamesPlaces(string $path): array
{
    $rank = [
        'PPLC' => 1, 'PPLA' => 2, 'PPLA2' => 3, 'PPLA3' => 4, 'PPLA4' => 5,
        'PPL' => 6, 'PPLS' => 7, 'PPLX' => 8, 'PPLL' => 9,
        'ADM1' => 10, 'ADM2' => 11, 'ADM3' => 12, 'ADM4' => 13,
    ];
    $out = [];
    $f = fopen($path, 'r');
    if ($f === false) {
        throw new RuntimeException("Cannot open $path");
    }
    while (($line = fgets($f)) !== false) {
        $p = explode("\t", $line);
        $fcode = $p[7] ?? '';
        if (! isset($rank[$fcode])) {
            continue;
        }
        $name = trim($p[1] ?? '');
        $ascii = trim($p[2] ?? '');
        if ($ascii === '') {
            $ascii = $name;
        }
        $ar = pickArabicFromAlts($name, $p[3] ?? '');
        if ($ar === null) {
            continue;
        }
        $out[] = [
            'ascii' => $ascii,
            'fold' => foldLatin($ascii),
            'ar' => $ar,
            'lat' => (float) ($p[4] ?? 0),
            'lng' => (float) ($p[5] ?? 0),
            'rank' => $rank[$fcode],
        ];
    }
    fclose($f);

    return $out;
}

/**
 * @param  list<array{ascii:string,fold:string,ar:string,lat:float,lng:float,rank:int}>  $places
 * @return array{byFold: array<string, list<int>>, grid: array<string, list<int>>}
 */
function buildIndexes(array $places): array
{
    $byFold = [];
    $grid = [];
    foreach ($places as $i => $place) {
        if ($place['fold'] !== '') {
            $byFold[$place['fold']][] = $i;
        }
        $gk = ((int) floor($place['lat'] * 10)).':'.((int) floor($place['lng'] * 10));
        $grid[$gk][] = $i;
    }

    return ['byFold' => $byFold, 'grid' => $grid];
}

/**
 * @param  list<array{ascii:string,fold:string,ar:string,lat:float,lng:float,rank:int}>  $places
 * @param  array{byFold: array<string, list<int>>, grid: array<string, list<int>>}  $indexes
 */
function resolveArabicName(string $enName, ?float $lat, ?float $lng, array $places, array $indexes): ?string
{
    $clean = trim($enName);
    $fold = foldLatin($clean);
    $candidateIdx = [];
    if ($fold !== '') {
        foreach ($indexes['byFold'][$fold] ?? [] as $i) {
            $candidateIdx[$i] = true;
        }
    }
    // öneksiz varyant
    $stripped = preg_replace('/^(Al|Ad|Ar|As|Ash|Az|An|El)[\s\-]+/iu', '', $clean) ?? $clean;
    $fold2 = foldLatin($stripped);
    if ($fold2 !== '' && $fold2 !== $fold) {
        foreach ($indexes['byFold'][$fold2] ?? [] as $i) {
            $candidateIdx[$i] = true;
        }
    }

    $pickBest = static function (array $ids) use ($places, $lat, $lng): ?string {
        if ($ids === []) {
            return null;
        }
        $best = null;
        $bestScore = INF;
        foreach ($ids as $i) {
            $p = $places[$i];
            $dist = ($lat !== null && $lng !== null)
                ? haversineKm($lat, $lng, $p['lat'], $p['lng'])
                : 0.0;
            $score = ($p['rank'] * 1000) + $dist;
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $p['ar'];
            }
        }

        return $best;
    };

    if ($candidateIdx !== []) {
        $ids = array_keys($candidateIdx);
        if ($lat !== null && $lng !== null) {
            $ids = array_values(array_filter($ids, static function (int $i) use ($places, $lat, $lng): bool {
                return haversineKm($lat, $lng, $places[$i]['lat'], $places[$i]['lng']) <= 100;
            }));
        }
        $got = $pickBest($ids);
        if ($got !== null) {
            return $got;
        }
    }

    if ($lat === null || $lng === null) {
        return null;
    }

    $near = [];
    for ($dLat = -1; $dLat <= 1; $dLat++) {
        for ($dLng = -1; $dLng <= 1; $dLng++) {
            $gk = ((int) floor($lat * 10) + $dLat).':'.((int) floor($lng * 10) + $dLng);
            foreach ($indexes['grid'][$gk] ?? [] as $i) {
                $dist = haversineKm($lat, $lng, $places[$i]['lat'], $places[$i]['lng']);
                if ($dist > 6.0) {
                    continue;
                }
                $sim = 0.0;
                if ($fold !== '' && $places[$i]['fold'] !== '') {
                    similar_text($fold, $places[$i]['fold'], $sim);
                }
                if ($sim >= 50 || $dist <= 1.5) {
                    $near[$i] = true;
                }
            }
        }
    }

    return $pickBest(array_keys($near));
}

echo "Loading dr5hn…\n";
$raw = json_decode((string) file_get_contents($srcJson), true);
if (isset($raw['data'])) {
    $raw = $raw['data'];
}
$byIso2 = [];
foreach ($raw as $c) {
    $iso = strtoupper((string) ($c['iso2'] ?? ''));
    if ($iso !== '') {
        $byIso2[$iso] = $c;
    }
}

$manifestPath = $root.'/manifest.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true);

foreach ($targets as $target) {
    $iso2 = $target['iso2'];
    $id = $target['id'];
    $meta = $target['meta'];
    $geonamesPath = $root.'/sources/geonames/'.$iso2.'/'.$iso2.'.txt';
    if (! is_file($geonamesPath)) {
        fwrite(STDERR, "Missing Geonames $geonamesPath — download {$iso2}.zip first\n");
        continue;
    }
    $src = $byIso2[$iso2] ?? null;
    if ($src === null) {
        fwrite(STDERR, "Missing dr5hn $iso2\n");
        continue;
    }

    echo "=== $iso2 ($id) ===\n";
    $places = loadGeonamesPlaces($geonamesPath);
    $indexes = buildIndexes($places);
    echo 'geonames AR places='.count($places)."\n";

    $states = is_array($src['states'] ?? null) ? $src['states'] : [];
    usort($states, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

    $manualCities = [
        // AE
        'Liwa Oasis' => 'واحة ليوا',
        'Manama' => 'منامة',
        'Masfut' => 'مصفوت',
        'Al Batayih' => 'البطائح',
        'Kalba' => 'كلباء',
        'Milehah' => 'مليحة',
        'Murbaḩ' => 'مربح',
        'Murbah' => 'مربح',
    ];

    $stateUsed = [];
    $packStates = [];
    $matched = 0;
    $unmatched = 0;
    $unmatchedSamples = [];

    foreach ($states as $state) {
        $enState = trim((string) ($state['name'] ?? ''));
        $nativeState = trim((string) ($state['native'] ?? ''));
        $arState = (preg_match('/\p{Arabic}/u', $nativeState) ? $nativeState : null)
            ?? pickArabicFromAlts($enState, '')
            ?? null;

        // Geonames ADM1 ile eyalet adı
        if ($arState === null) {
            $arState = resolveArabicName($enState, null, null, $places, $indexes);
        }
        if ($arState === null || ! preg_match('/\p{Arabic}/u', $arState)) {
            fwrite(STDERR, "  skip state (no Arabic): $enState\n");
            continue;
        }

        $stateKey = uniqueKey(placeKey($enState), $stateUsed);
        $cities = is_array($state['cities'] ?? null) ? $state['cities'] : [];
        usort($cities, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        $cityUsed = [];
        $seenAr = [];
        $packCities = [];
        foreach ($cities as $city) {
            $enCity = trim((string) ($city['name'] ?? ''));
            if ($enCity === '') {
                continue;
            }
            $lat = isset($city['latitude']) ? (float) $city['latitude'] : null;
            $lng = isset($city['longitude']) ? (float) $city['longitude'] : null;
            $arCity = $manualCities[$enCity] ?? resolveArabicName($enCity, $lat, $lng, $places, $indexes);
            if ($arCity === null) {
                $unmatched++;
                if (count($unmatchedSamples) < 15) {
                    $unmatchedSamples[] = $enCity;
                }
                continue;
            }
            $dedupe = mb_strtolower($arCity, 'UTF-8');
            if (isset($seenAr[$dedupe])) {
                continue;
            }
            $seenAr[$dedupe] = true;
            $matched++;
            $packCities[] = [
                'key' => uniqueKey(placeKey($enCity), $cityUsed),
                'name' => ['ar' => $arCity],
            ];
        }

        if ($packCities === []) {
            $packCities[] = [
                'key' => $stateKey,
                'name' => ['ar' => $arState],
            ];
        }

        $packStates[] = [
            'key' => $stateKey,
            'name' => ['ar' => $arState],
            'cities' => $packCities,
        ];
    }

    $cityCount = 0;
    foreach ($packStates as $s) {
        $cityCount += count($s['cities']);
    }

    $pack = [
        'id' => $id,
        'iso2' => $iso2,
        'code' => $target['iso3'],
        'version' => '1.3.0',
        'default_locale' => 'ar',
        'notes' => [
            'tr' => 'Ülke adı 11 dil. İl/ilçe yalnızca Arapça (Arap alfabesi); Geonames.',
            'en' => 'Country name in 11 locales. States/cities in Arabic script only (Geonames).',
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
    $outPath = $outDir.'/locations.json';
    file_put_contents($outPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
    file_put_contents(
        $outDir.'/pack.json',
        json_encode(['id' => $id, 'version' => '1.3.0', 'iso2' => $iso2, 'code' => $target['iso3']], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );

    foreach ($manifest['packs'] as &$row) {
        if (($row['id'] ?? '') !== $id) {
            continue;
        }
        $row['version'] = '1.3.0';
        $row['states_count'] = count($packStates);
        $row['cities_count'] = $cityCount;
        $row['file_bytes'] = filesize($outPath);
        $row['estimate_disk_bytes'] = 4096 + (count($packStates) * 400) + ($cityCount * 450) + 2048;
        $row['name'] = $pack['country']['name'];
        $row['default_locale'] = 'ar';
    }
    unset($row);

    echo "OK states=".count($packStates)." cities=$cityCount matched=$matched unmatched=$unmatched\n";
    if ($unmatchedSamples !== []) {
        echo '  unmatched: '.implode(', ', $unmatchedSamples)."\n";
    }
    // örnek
    foreach ($packStates as $s) {
        echo '  '.$s['name']['ar'].' → '.(isset($s['cities'][0]) ? $s['cities'][0]['name']['ar'] : '-')."\n";
        break;
    }
}

file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo "DONE\n";
