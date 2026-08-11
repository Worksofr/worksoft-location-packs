<?php
/**
 * Özel AZ paketindeki state/city adlarına dr5hn çevirilerini ekler (11 dil).
 * Hiyerarşi değişmez; yalnızca name map genişler.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('memory_limit', '1024M');

$root = __DIR__;
$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];
$packPath = $root.'/packs/az/locations.json';
$combinedJson = $root.'/sources/dr5hn/countries-states-cities.json';
$translationsCsv = $root.'/sources/dr5hn/translations.csv';

$pack = json_decode((string) file_get_contents($packPath), true, 512, JSON_THROW_ON_ERROR);
$countries = json_decode((string) file_get_contents($combinedJson), true, 512, JSON_THROW_ON_ERROR);

$az = null;
foreach ($countries as $c) {
    if (($c['iso2'] ?? '') === 'AZ') {
        $az = $c;
        break;
    }
}
if (! $az) {
    fwrite(STDERR, "AZ not in dr5hn\n");
    exit(1);
}

// İngilizce / native ad → place id
$nameToStateId = [];
$nameToCityId = [];
$stateIds = [];
$cityIds = [];
foreach ($az['states'] ?? [] as $s) {
    $sid = (int) $s['id'];
    $stateIds[$sid] = true;
    foreach (array_filter([(string) ($s['name'] ?? ''), (string) ($s['native'] ?? '')]) as $n) {
        $nameToStateId[mb_strtolower(trim($n))] = $sid;
    }
    foreach ($s['cities'] ?? [] as $city) {
        $cid = (int) $city['id'];
        $cityIds[$cid] = true;
        foreach (array_filter([(string) ($city['name'] ?? ''), (string) ($city['native'] ?? '')]) as $n) {
            $nameToCityId[mb_strtolower(trim($n))] = $cid;
        }
    }
}

$trMap = [];
$localeSet = array_fill_keys($locales, true);
$fh = fopen($translationsCsv, 'rb');
fgetcsv($fh, 0, ',', '"', '\\');
while (($row = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
    if (count($row) < 4) {
        continue;
    }
    $pid = (int) $row[0];
    $type = $row[1];
    $lang = $row[2];
    $text = trim((string) $row[3]);
    if ($text === '' || ! isset($localeSet[$lang])) {
        continue;
    }
    if ($type === 'state' && isset($stateIds[$pid])) {
        $trMap['state:'.$pid.':'.$lang] = $text;
    } elseif ($type === 'city' && isset($cityIds[$pid])) {
        $trMap['city:'.$pid.':'.$lang] = $text;
    }
}
fclose($fh);

function enrichNames(array $existing, ?int $placeId, string $type, array $trMap, array $locales): array
{
    $out = $existing;
    if ($placeId) {
        foreach ($locales as $loc) {
            $k = $type.':'.$placeId.':'.$loc;
            if (isset($trMap[$k]) && $trMap[$k] !== '') {
                $out[$loc] = $trMap[$k];
            }
        }
    }
    // Mevcut az/tr değerlerini koru; eksik locale’lere en veya az doldur
    $fallback = $out['en'] ?? $out['az'] ?? $out['tr'] ?? reset($out);
    foreach ($locales as $loc) {
        if (! isset($out[$loc]) || $out[$loc] === '') {
            $out[$loc] = $fallback;
        }
    }

    return $out;
}

$enrichedStates = 0;
$enrichedCities = 0;
foreach ($pack['states'] as &$state) {
    $azName = mb_strtolower(trim((string) ($state['name']['az'] ?? $state['name']['en'] ?? '')));
    $enName = mb_strtolower(trim((string) ($state['name']['en'] ?? '')));
    $sid = $nameToStateId[$azName] ?? $nameToStateId[$enName] ?? null;
    $before = count($state['name']);
    $state['name'] = enrichNames($state['name'], $sid, 'state', $trMap, $locales);
    if (count($state['name']) > $before || $sid) {
        $enrichedStates++;
    }
    foreach ($state['cities'] as &$city) {
        $caz = mb_strtolower(trim((string) ($city['name']['az'] ?? $city['name']['en'] ?? '')));
        $cen = mb_strtolower(trim((string) ($city['name']['en'] ?? '')));
        $cid = $nameToCityId[$caz] ?? $nameToCityId[$cen] ?? null;
        $city['name'] = enrichNames($city['name'], $cid, 'city', $trMap, $locales);
        $enrichedCities++;
    }
    unset($city);
}
unset($state);

$pack['version'] = '1.2.0';
$pack['notes'] = [
    'tr' => 'Büyük şehirler detaylı; diğer rayonlar merkez+kasaba. İl/ilçe adları 11 dil (dr5hn çeviri + AZ).',
    'en' => 'Major cities detailed; other rayons center+towns. Place names in 11 locales (dr5hn + AZ).',
];

file_put_contents($packPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents(
    $root.'/packs/az/pack.json',
    json_encode(['id' => 'az', 'version' => '1.2.0', 'iso2' => 'AZ', 'code' => 'AZE'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
);

// Manifest az satırını güncelle
$manifest = json_decode((string) file_get_contents($root.'/manifest.json'), true);
foreach ($manifest['packs'] as &$row) {
    if (($row['id'] ?? '') === 'az') {
        $row['version'] = '1.2.0';
        $row['file_bytes'] = filesize($packPath);
        $row['estimate_disk_bytes'] = 4096 + ((int) $row['states_count'] * 400) + ((int) $row['cities_count'] * 650) + 2048;
    }
}
unset($row);
file_put_contents($root.'/manifest.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

echo "enriched states~$enrichedStates cities=$enrichedCities size=".filesize($packPath).PHP_EOL;

// sample
foreach ($pack['states'] as $s) {
    if (($s['key'] ?? '') === 'baku') {
        echo 'Baku uk='.($s['name']['uk'] ?? '').' en='.($s['name']['en'] ?? '').PHP_EOL;
        break;
    }
}
