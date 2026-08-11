<?php
/**
 * manifest.json içine her paket için file_bytes + estimate_disk_bytes yazar.
 */

$root = __DIR__;
$manifestPath = $root.'/manifest.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

foreach ($manifest['packs'] as &$pack) {
    $id = (string) ($pack['id'] ?? '');
    $path = $root.'/'.str_replace('/', DIRECTORY_SEPARATOR, (string) ($pack['path'] ?? ''));
    $bytes = is_file($path) ? (int) filesize($path) : 0;
    $states = (int) ($pack['states_count'] ?? 0);
    $cities = (int) ($pack['cities_count'] ?? 0);
    // Kabaca DB satır + indeks + çeviri overhead (ülke çevirileri dahil)
    $estimate = 4096 + ($states * 400) + ($cities * 450) + 2048;

    $pack['file_bytes'] = $bytes;
    $pack['estimate_disk_bytes'] = $estimate;
    echo $id.' file='.$bytes.' est='.$estimate.PHP_EOL;
}
unset($pack);

file_put_contents(
    $manifestPath,
    json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo "OK\n";
