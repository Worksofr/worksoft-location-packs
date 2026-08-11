<?php
/**
 * UA konum paketi v1.3.0 — il/ilçe yalnızca Ukraynaca (Kiril).
 * Kaynak: dr5hn hiyerarşi + resmi oblast adları + Geonames UA alternatif adları.
 */
declare(strict_types=1);

ini_set('memory_limit', '1024M');

$root = __DIR__;
$geonamesPath = $root.'/sources/geonames/UA/UA.txt';
$srcJson = $root.'/sources/dr5hn/countries-states-cities.json';
$i18nPath = $root.'/data/country-i18n.php';
$outPath = $root.'/packs/ua/locations.json';

if (! is_file($geonamesPath) || ! is_file($srcJson) || ! is_file($i18nPath)) {
    fwrite(STDERR, "Missing geonames/dr5hn/i18n source\n");
    exit(1);
}

/** @var array<string, array<string, mixed>> $countriesMeta */
$countriesMeta = include $i18nPath;
$meta = $countriesMeta['ua'] ?? null;
if ($meta === null) {
    foreach ($countriesMeta as $row) {
        if (strtoupper((string) ($row['iso2'] ?? '')) === 'UA') {
            $meta = $row;
            break;
        }
    }
}
if ($meta === null) {
    fwrite(STDERR, "UA missing in country-i18n.php\n");
    exit(1);
}

$oblastMap = [
    'Autonomous Republic of Crimea' => 'Автономна Республіка Крим',
    'Cherkaska' => 'Черкаська',
    'Chernihivska' => 'Чернігівська',
    'Chernivetska' => 'Чернівецька',
    'Dnipropetrovska' => 'Дніпропетровська',
    'Donetska' => 'Донецька',
    'Ivano-Frankivska' => 'Івано-Франківська',
    'Kharkivska' => 'Харківська',
    'Khersonska' => 'Херсонська',
    'Khmelnytska' => 'Хмельницька',
    'Kirovohradska' => 'Кіровоградська',
    'Kyiv' => 'Київ',
    'Kyivska' => 'Київська',
    'Luhanska' => 'Луганська',
    'Lvivska' => 'Львівська',
    'Mykolaivska' => 'Миколаївська',
    'Odeska' => 'Одеська',
    'Poltavska' => 'Полтавська',
    'Rivnenska' => 'Рівненська',
    'Sevastopol' => 'Севастополь',
    'Sumska' => 'Сумська',
    'Ternopilska' => 'Тернопільська',
    'Vinnytska' => 'Вінницька',
    'Volynska' => 'Волинська',
    'Zakarpatska' => 'Закарпатська',
    'Zaporizka' => 'Запорізька',
    'Zhytomyrska' => 'Житомирська',
];

function placeKey(string $name): string
{
    $map = [
        'ş' => 's', 'Ş' => 's', 'ı' => 'i', 'İ' => 'i',
        'ğ' => 'g', 'Ğ' => 'g', 'ü' => 'u', 'Ü' => 'u',
        'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g',
        'д' => 'd', 'е' => 'e', 'є' => 'ye', 'ж' => 'zh', 'з' => 'z',
        'и' => 'y', 'і' => 'i', 'ї' => 'yi', 'й' => 'i', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
        'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '',
        'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'h', 'Ґ' => 'g',
        'Д' => 'd', 'Е' => 'e', 'Є' => 'ye', 'Ж' => 'zh', 'З' => 'z',
        'И' => 'y', 'І' => 'i', 'Ї' => 'yi', 'Й' => 'i', 'К' => 'k',
        'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p',
        'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ф' => 'f',
        'Х' => 'kh', 'Ц' => 'ts', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'shch',
        'Ь' => '', 'Ю' => 'yu', 'Я' => 'ya',
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

function foldLatin(string $s): string
{
    // Önce Kiril → Latin (skorlama için)
    $cyr = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g',
        'д' => 'd', 'е' => 'e', 'є' => 'ye', 'ж' => 'zh', 'з' => 'z',
        'и' => 'y', 'і' => 'i', 'ї' => 'yi', 'й' => 'i', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
        'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ы' => 'y', 'э' => 'e', 'ё' => 'e',
        'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'h', 'Ґ' => 'g',
        'Д' => 'd', 'Е' => 'e', 'Є' => 'ye', 'Ж' => 'zh', 'З' => 'z',
        'И' => 'y', 'І' => 'i', 'Ї' => 'yi', 'Й' => 'i', 'К' => 'k',
        'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p',
        'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u', 'Ф' => 'f',
        'Х' => 'kh', 'Ц' => 'ts', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'shch',
        'Ь' => '', 'Ю' => 'yu', 'Я' => 'ya', 'Ъ' => '', 'Ы' => 'y', 'Э' => 'e', 'Ё' => 'e',
    ];
    $s = strtr($s, $cyr);
    $s = strtr($s, ['’' => '', 'ʻ' => '', '`' => '', "'" => '', 'ʹ' => '', 'ʼ' => '', '′' => '']);
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(
        ['kh', 'zh', 'ch', 'shch', 'sh', 'ts', 'yo', 'yu', 'ya', 'yi', 'ye', 'yy', 'iy', 'yj'],
        ['h', 'z', 'c', 's', 's', 'c', 'e', 'u', 'a', 'i', 'e', 'y', 'i', 'i'],
        $s
    );
    $s = strtr($s, ['g' => 'h', 'w' => 'v']);
    $s = preg_replace('/[^a-z0-9]+/u', '', $s) ?? '';

    return $s;
}

function stripAdminNoise(string $name): string
{
    $n = trim($name);
    $n = preg_replace('/^(gorodskoy okrug|municipal(?:ity)?)\s+/iu', '', $n) ?? $n;
    $n = preg_replace('/\s+(raion|rayon|oblast|region|mis.?ka\s+rada|mis.?krada)$/iu', '', $n) ?? $n;

    return trim($n);
}

function pickUkrainianFromAlts(string $primary, string $altsCsv, string $preferFold = ''): ?string
{
    $parts = array_merge([$primary], explode(',', $altsCsv));
    $uk = [];
    $cyr = [];
    $ru = [];

    foreach ($parts as $raw) {
        $n = trim($raw);
        if ($n === '' || ! preg_match('/\p{Cyrillic}/u', $n)) {
            continue;
        }
        // Latin karışık / Tatar vb. ele
        if (preg_match('/[A-Za-zəҗүәөңһ]/u', $n)) {
            continue;
        }
        if (preg_match('/^\d/', $n)) {
            continue;
        }
        // Rus harfi varsa uk havuzuna alma (і+э karışımı vb.)
        if (preg_match('/[ыэъёЫЭЪЁ]/u', $n)) {
            $ru[] = $n;
        } elseif (preg_match('/[іїєґІЇЄҐ]/u', $n)) {
            $uk[] = $n;
        } else {
            $cyr[] = $n;
        }
    }

    $score = static function (string $candidate) use ($preferFold): float {
        if ($preferFold === '') {
            return 100.0 - mb_strlen($candidate);
        }
        $fold = foldLatin($candidate);
        if ($fold === '') {
            return -1.0;
        }
        similar_text($preferFold, $fold, $pct);

        // kısa resmi ad biraz avantajlı
        return $pct - (mb_strlen($candidate) * 0.05);
    };

    foreach ([$uk, $cyr, $ru] as $pool) {
        if ($pool === []) {
            continue;
        }
        usort($pool, static function (string $a, string $b) use ($score): int {
            return $score($b) <=> $score($a);
        });

        return $pool[0];
    }

    return null;
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
 * @return list<array{ascii:string,fold:string,uk:string,lat:float,lng:float,rank:int}>
 */
function loadGeonamesPlaces(string $path): array
{
    $rank = [
        'PPLC' => 1, 'PPLA' => 2, 'PPLA2' => 3, 'PPLA3' => 4, 'PPLA4' => 5,
        'PPL' => 6, 'PPLS' => 7, 'PPLX' => 8, 'PPLL' => 9,
        'ADM1' => 10, 'ADM2' => 11, 'ADM3' => 12, 'ADM4' => 13,
        'ADM2H' => 14, 'ADM3H' => 15,
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
        $fold = foldLatin($ascii);
        $uk = pickUkrainianFromAlts($name, $p[3] ?? '', $fold);
        if ($uk === null) {
            continue;
        }
        $out[] = [
            'ascii' => $ascii,
            'fold' => $fold,
            'uk' => $uk,
            'lat' => (float) ($p[4] ?? 0),
            'lng' => (float) ($p[5] ?? 0),
            'rank' => $rank[$fcode],
        ];
    }
    fclose($f);

    return $out;
}

/**
 * @param  list<array{ascii:string,fold:string,uk:string,lat:float,lng:float,rank:int}>  $places
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
        // ~11 km hücre (0.1 derece)
        $gk = ((int) floor($place['lat'] * 10)).':'.((int) floor($place['lng'] * 10));
        $grid[$gk][] = $i;
    }

    return ['byFold' => $byFold, 'grid' => $grid];
}

/**
 * @param  list<array{ascii:string,fold:string,uk:string,lat:float,lng:float,rank:int}>  $places
 * @param  array{byFold: array<string, list<int>>, grid: array<string, list<int>>}  $indexes
 */
function resolveUkrainianName(string $enName, ?float $lat, ?float $lng, array $places, array $indexes): ?string
{
    $variants = array_unique(array_filter([
        $enName,
        stripAdminNoise($enName),
    ]));

    $candidateIdx = [];
    foreach ($variants as $v) {
        $fold = foldLatin($v);
        if ($fold === '') {
            continue;
        }
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
            // rank + mesafe
            $score = ($p['rank'] * 1000) + $dist;
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $p['uk'];
            }
        }

        return $best;
    };

    if ($candidateIdx !== []) {
        $ids = array_keys($candidateIdx);
        // koordinat varsa 80 km filtresi
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

    // Koordinat yakınlığı (isim eşleşmezse)
    if ($lat === null || $lng === null) {
        return null;
    }

    $near = [];
    for ($dLat = -1; $dLat <= 1; $dLat++) {
        for ($dLng = -1; $dLng <= 1; $dLng++) {
            $gk = ((int) floor($lat * 10) + $dLat).':'.((int) floor($lng * 10) + $dLng);
            foreach ($indexes['grid'][$gk] ?? [] as $i) {
                $dist = haversineKm($lat, $lng, $places[$i]['lat'], $places[$i]['lng']);
                if ($dist <= 4.0) {
                    // isim benzerliği bonus
                    $foldEn = foldLatin(stripAdminNoise($enName));
                    $sim = 0;
                    if ($foldEn !== '' && $places[$i]['fold'] !== '') {
                        similar_text($foldEn, $places[$i]['fold'], $pct);
                        $sim = $pct;
                    }
                    if ($sim >= 55 || $dist <= 1.2) {
                        $near[$i] = true;
                    }
                }
            }
        }
    }

    return $pickBest(array_keys($near));
}

echo "Loading Geonames…\n";
$places = loadGeonamesPlaces($geonamesPath);
$indexes = buildIndexes($places);
echo 'geonames UK places='.count($places)."\n";

echo "Loading dr5hn…\n";
$raw = json_decode((string) file_get_contents($srcJson), true);
if (isset($raw['data'])) {
    $raw = $raw['data'];
}
$ua = null;
foreach ($raw as $c) {
    if (($c['iso2'] ?? '') === 'UA') {
        $ua = $c;
        break;
    }
}
if ($ua === null) {
    fwrite(STDERR, "UA not in dr5hn\n");
    exit(1);
}

$states = is_array($ua['states'] ?? null) ? $ua['states'] : [];
usort($states, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

$stateUsed = [];
$packStates = [];
$matched = 0;
$unmatched = 0;
$unmatchedSamples = [];

foreach ($states as $state) {
    $enState = trim((string) ($state['name'] ?? ''));
    if ($enState === '' || ! isset($oblastMap[$enState])) {
        fwrite(STDERR, "Unknown oblast: $enState\n");
        continue;
    }
    $ukState = $oblastMap[$enState];
    $stateKey = uniqueKey(placeKey($ukState), $stateUsed);

    $cities = is_array($state['cities'] ?? null) ? $state['cities'] : [];
    usort($cities, static fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

    $cityUsed = [];
    $seenUk = [];
    $packCities = [];
    foreach ($cities as $city) {
        $enCity = trim((string) ($city['name'] ?? ''));
        if ($enCity === '') {
            continue;
        }
        $lat = isset($city['latitude']) ? (float) $city['latitude'] : null;
        $lng = isset($city['longitude']) ? (float) $city['longitude'] : null;
        $ukCity = resolveUkrainianName($enCity, $lat, $lng, $places, $indexes);

        // Elle düzeltmeler (yeniden adlandırılmış / nadir yazım)
        $manual = [
            'Kamianske' => 'Кам’янське',
            'Novomoskovsk' => 'Новомосковськ',
            "Novomoskovs’k" => 'Новомосковськ',
            'Ordzhonikidze' => 'Покров',
            'Pervomaiskyi' => 'Первомайський',
            'Novopskov' => 'Новопсков',
            'Tiachiv' => 'Тячів',
            'Berdiansk' => 'Бердянськ',
            'Zaporizhia' => 'Запоріжжя',
            'Chapayevka' => 'Чапаєвка',
            'Kryva Hora' => 'Крива Гора',
            'Derhanivka' => 'Дерганівка',
            'Velykyi Bereznyi Raion' => 'Великий Березний',
        ];
        if (isset($manual[$enCity])) {
            $ukCity = $manual[$enCity];
        }

        // Rayon / mis'krada: "…kyy Rayon" → ADM eşlemesi
        if ($ukCity === null && preg_match('/^(.+?)[’\']?kyy\s+rayon$/iu', $enCity, $m)) {
            $ukCity = resolveUkrainianName(trim($m[1]).' Raion', $lat, $lng, $places, $indexes)
                ?? resolveUkrainianName(trim($m[1]), $lat, $lng, $places, $indexes);
        }
        if ($ukCity === null && preg_match('/^(.+?)[’\']?ka\s+mis[’\']?krada$/iu', $enCity, $m)) {
            $ukCity = resolveUkrainianName(trim($m[1]), $lat, $lng, $places, $indexes);
        }

        if ($ukCity === null) {
            $unmatched++;
            if (count($unmatchedSamples) < 30) {
                $unmatchedSamples[] = $enCity;
            }
            continue;
        }

        $dedupe = mb_strtolower($ukCity, 'UTF-8');
        if (isset($seenUk[$dedupe])) {
            continue;
        }
        $seenUk[$dedupe] = true;

        $matched++;
        $packCities[] = [
            'key' => uniqueKey(placeKey($ukCity), $cityUsed),
            'name' => ['uk' => $ukCity],
        ];
    }

    if ($packCities === []) {
        $packCities[] = [
            'key' => $stateKey,
            'name' => ['uk' => $ukState],
        ];
    }

    $packStates[] = [
        'key' => $stateKey,
        'name' => ['uk' => $ukState],
        'cities' => $packCities,
    ];
}

$cityCount = 0;
foreach ($packStates as $s) {
    $cityCount += count($s['cities']);
}

$pack = [
    'id' => 'ua',
    'iso2' => 'UA',
    'code' => 'UKR',
    'version' => '1.3.0',
    'default_locale' => 'uk',
    'notes' => [
        'tr' => 'Ülke adı 11 dil. İl/ilçe Ukraynaca (Kiril); Geonames + resmi oblast haritası.',
        'en' => 'Country name in 11 locales. States/cities in Ukrainian Cyrillic (Geonames + official oblast map).',
    ],
    'country' => [
        'name' => $meta['name'],
        'nationality' => $meta['nationality'],
    ],
    'states' => $packStates,
];

if (! is_dir($root.'/packs/ua')) {
    mkdir($root.'/packs/ua', 0755, true);
}
file_put_contents($outPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents(
    $root.'/packs/ua/pack.json',
    json_encode(['id' => 'ua', 'version' => '1.3.0', 'iso2' => 'UA', 'code' => 'UKR'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

$manifestPath = $root.'/manifest.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true);
foreach ($manifest['packs'] as &$row) {
    if (($row['id'] ?? '') !== 'ua') {
        continue;
    }
    $row['version'] = '1.3.0';
    $row['states_count'] = count($packStates);
    $row['cities_count'] = $cityCount;
    $row['file_bytes'] = filesize($outPath);
    $row['estimate_disk_bytes'] = 4096 + (count($packStates) * 400) + ($cityCount * 450) + 2048;
    $row['name'] = $pack['country']['name'];
    $row['default_locale'] = 'uk';
}
unset($row);
file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

echo "OK states=".count($packStates)." cities=$cityCount\n";
echo "matched=$matched skipped_unmatched=$unmatched\n";
if ($unmatchedSamples !== []) {
    echo 'skipped samples: '.implode(', ', $unmatchedSamples)."\n";
}
echo "states:\n";
foreach ($packStates as $s) {
    echo '  '.$s['name']['uk'].' ('.count($s['cities']).")\n";
}
// Dnipro sample
foreach ($packStates as $s) {
    if ($s['name']['uk'] === 'Дніпропетровська') {
        echo "Dnipropetrovska sample:\n";
        foreach (array_slice($s['cities'], 0, 12) as $c) {
            echo '  '.$c['name']['uk']."\n";
        }
        break;
    }
}
