<?php
/**
 * GE konum paketi v1.3.0 — il/ilçe yalnızca Gürcüce (Mkhedruli).
 * Kaynak: dr5hn hiyerarşi + native bölgeler + Geonames GE alternatif adları.
 */
declare(strict_types=1);

ini_set('memory_limit', '512M');

$root = __DIR__;
$geonamesPath = $root.'/sources/geonames/GE/GE.txt';
$srcJson = $root.'/sources/dr5hn/countries-states-cities.json';
$i18nPath = $root.'/data/country-i18n.php';
$outPath = $root.'/packs/ge/locations.json';

if (! is_file($geonamesPath) || ! is_file($srcJson) || ! is_file($i18nPath)) {
    fwrite(STDERR, "Missing geonames/dr5hn/i18n source\n");
    exit(1);
}

/** @var array<string, array<string, mixed>> $countriesMeta */
$countriesMeta = include $i18nPath;
$meta = null;
foreach ($countriesMeta as $row) {
    if (strtoupper((string) ($row['iso2'] ?? '')) === 'GE') {
        $meta = $row;
        break;
    }
}
if ($meta === null) {
    fwrite(STDERR, "GE missing in country-i18n.php\n");
    exit(1);
}

$regionMap = [
    'Abkhazia' => 'აფხაზეთი',
    'Adjara' => 'აჭარა',
    'Guria' => 'გურია',
    'Imereti' => 'იმერეთი',
    'Kakheti' => 'კახეთი',
    'Kvemo Kartli' => 'ქვემო ქართლი',
    'Mtskheta-Mtianeti' => 'მცხეთა-მთიანეთი',
    'Racha-Lechkhumi and Kvemo Svaneti' => 'რაჭა-ლეჩხუმი და ქვემო სვანეთი',
    'Samegrelo-Zemo Svaneti' => 'სამეგრელო-ზემო სვანეთი',
    'Samtskhe-Javakheti' => 'სამცხე-ჯავახეთი',
    'Shida Kartli' => 'შიდა ქართლი',
    'Tbilisi' => 'თბილისი',
];

function placeKey(string $name): string
{
    $s = mb_strtolower($name, 'UTF-8');
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s) ?? '';
    $s = trim($s, '-');
    if ($s === '') {
        return 'x'.substr(md5($name), 0, 8);
    }
    // Latin değilse kısa hash (slug ASCII kalsın)
    if (! preg_match('/^[a-z0-9-]+$/u', $s)) {
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
    $s = strtr($s, ['’' => '', 'ʻ' => '', '`' => '', "'" => '', 'ʹ' => '', 'ʼ' => '', '′' => '']);
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(['kh', 'zh', 'ch', 'sh', 'ts', 'gh', 'dz'], ['h', 'z', 'c', 's', 'c', 'g', 'z'], $s);
    $s = preg_replace('/[^a-z0-9]+/u', '', $s) ?? '';

    return $s;
}

function pickGeorgianFromAlts(string $primary, string $altsCsv): ?string
{
    $parts = array_merge([$primary], explode(',', $altsCsv));
    $geo = [];
    foreach ($parts as $raw) {
        $n = trim($raw);
        if ($n === '' || ! preg_match('/\p{Georgian}/u', $n)) {
            continue;
        }
        if (preg_match('/[A-Za-zА-Яа-яЁё]/u', $n)) {
            continue;
        }
        $geo[] = $n;
    }
    if ($geo === []) {
        return null;
    }
    usort($geo, static fn (string $a, string $b): int => mb_strlen($a) <=> mb_strlen($b));

    return $geo[0];
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
 * @return list<array{ascii:string,fold:string,ka:string,lat:float,lng:float,rank:int}>
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
        $ka = pickGeorgianFromAlts($name, $p[3] ?? '');
        if ($ka === null) {
            continue;
        }
        $out[] = [
            'ascii' => $ascii,
            'fold' => foldLatin($ascii),
            'ka' => $ka,
            'lat' => (float) ($p[4] ?? 0),
            'lng' => (float) ($p[5] ?? 0),
            'rank' => $rank[$fcode],
        ];
    }
    fclose($f);

    return $out;
}

/**
 * @param  list<array{ascii:string,fold:string,ka:string,lat:float,lng:float,rank:int}>  $places
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
 * @param  list<array{ascii:string,fold:string,ka:string,lat:float,lng:float,rank:int}>  $places
 * @param  array{byFold: array<string, list<int>>, grid: array<string, list<int>>}  $indexes
 */
function resolveGeorgianName(string $enName, ?float $lat, ?float $lng, array $places, array $indexes): ?string
{
    $clean = trim(strtr($enName, ['’' => "'", 'ʻ' => "'"]));
    $fold = foldLatin($clean);
    $candidateIdx = [];
    if ($fold !== '') {
        foreach ($indexes['byFold'][$fold] ?? [] as $i) {
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
                $best = $p['ka'];
            }
        }

        return $best;
    };

    if ($candidateIdx !== []) {
        $ids = array_keys($candidateIdx);
        if ($lat !== null && $lng !== null) {
            $ids = array_values(array_filter($ids, static function (int $i) use ($places, $lat, $lng): bool {
                return haversineKm($lat, $lng, $places[$i]['lat'], $places[$i]['lng']) <= 80;
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
                if ($dist > 5.0) {
                    continue;
                }
                $sim = 0.0;
                if ($fold !== '' && $places[$i]['fold'] !== '') {
                    similar_text($fold, $places[$i]['fold'], $sim);
                }
                if ($sim >= 55 || $dist <= 1.5) {
                    $near[$i] = true;
                }
            }
        }
    }

    return $pickBest(array_keys($near));
}

echo "Loading Geonames GE…\n";
$places = loadGeonamesPlaces($geonamesPath);
$indexes = buildIndexes($places);
echo 'geonames KA places='.count($places)."\n";

echo "Loading dr5hn…\n";
$raw = json_decode((string) file_get_contents($srcJson), true);
if (isset($raw['data'])) {
    $raw = $raw['data'];
}
$ge = null;
foreach ($raw as $c) {
    if (($c['iso2'] ?? '') === 'GE') {
        $ge = $c;
        break;
    }
}
if ($ge === null) {
    fwrite(STDERR, "GE not in dr5hn\n");
    exit(1);
}

$states = is_array($ge['states'] ?? null) ? $ge['states'] : [];
usort($states, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

$manual = [
    "P’asanauri" => 'ფასანაური',
    "Step’antsminda" => 'სტეფანწმინდა',
    'Java' => 'ჯავა',
];

$stateUsed = [];
$packStates = [];
$matched = 0;
$unmatched = 0;
$unmatchedSamples = [];

foreach ($states as $state) {
    $enState = trim((string) ($state['name'] ?? ''));
    if ($enState === '' || ! isset($regionMap[$enState])) {
        fwrite(STDERR, "Unknown region: $enState\n");
        continue;
    }
    $kaState = $regionMap[$enState];
    $stateKey = uniqueKey(placeKey($enState), $stateUsed);

    $cities = is_array($state['cities'] ?? null) ? $state['cities'] : [];
    usort($cities, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

    $cityUsed = [];
    $seenKa = [];
    $packCities = [];
    foreach ($cities as $city) {
        $enCity = trim((string) ($city['name'] ?? ''));
        if ($enCity === '') {
            continue;
        }
        $lat = isset($city['latitude']) ? (float) $city['latitude'] : null;
        $lng = isset($city['longitude']) ? (float) $city['longitude'] : null;

        $kaCity = $manual[$enCity] ?? resolveGeorgianName($enCity, $lat, $lng, $places, $indexes);
        if ($kaCity === null) {
            $unmatched++;
            if (count($unmatchedSamples) < 20) {
                $unmatchedSamples[] = $enCity;
            }
            continue;
        }

        $dedupe = mb_strtolower($kaCity, 'UTF-8');
        if (isset($seenKa[$dedupe])) {
            continue;
        }
        $seenKa[$dedupe] = true;
        $matched++;
        $packCities[] = [
            'key' => uniqueKey(placeKey($enCity), $cityUsed),
            'name' => ['ka' => $kaCity],
        ];
    }

    if ($packCities === []) {
        $packCities[] = [
            'key' => $stateKey,
            'name' => ['ka' => $kaState],
        ];
    }

    $packStates[] = [
        'key' => $stateKey,
        'name' => ['ka' => $kaState],
        'cities' => $packCities,
    ];
}

$cityCount = 0;
foreach ($packStates as $s) {
    $cityCount += count($s['cities']);
}

$pack = [
    'id' => 'ge',
    'iso2' => 'GE',
    'code' => 'GEO',
    'version' => '1.3.0',
    'default_locale' => 'ka',
    'notes' => [
        'tr' => 'Ülke adı 11 dil. İl/ilçe Gürcüce (Mkhedruli); Geonames + bölge haritası.',
        'en' => 'Country name in 11 locales. States/cities in Georgian Mkhedruli (Geonames + region map).',
    ],
    'country' => [
        'name' => $meta['name'],
        'nationality' => $meta['nationality'],
    ],
    'states' => $packStates,
];

if (! is_dir($root.'/packs/ge')) {
    mkdir($root.'/packs/ge', 0755, true);
}
file_put_contents($outPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents(
    $root.'/packs/ge/pack.json',
    json_encode(['id' => 'ge', 'version' => '1.3.0', 'iso2' => 'GE', 'code' => 'GEO'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

$manifestPath = $root.'/manifest.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true);
foreach ($manifest['packs'] as &$row) {
    if (($row['id'] ?? '') !== 'ge') {
        continue;
    }
    $row['version'] = '1.3.0';
    $row['states_count'] = count($packStates);
    $row['cities_count'] = $cityCount;
    $row['file_bytes'] = filesize($outPath);
    $row['estimate_disk_bytes'] = 4096 + (count($packStates) * 400) + ($cityCount * 450) + 2048;
    $row['name'] = $pack['country']['name'];
    $row['default_locale'] = 'ka';
}
unset($row);
file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

echo "OK states=".count($packStates)." cities=$cityCount matched=$matched unmatched=$unmatched\n";
if ($unmatchedSamples !== []) {
    echo 'unmatched: '.implode(', ', $unmatchedSamples)."\n";
}
foreach ($packStates as $s) {
    if ($s['name']['ka'] === 'მცხეთა-მთიანეთი') {
        echo "Mtskheta-Mtianeti cities:\n";
        foreach ($s['cities'] as $c) {
            echo '  '.$c['name']['ka']."\n";
        }
    }
}
