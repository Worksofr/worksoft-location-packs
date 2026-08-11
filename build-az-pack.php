<?php
/**
 * Azerbaycan örnek pack üretici (tek seferlik).
 * Çalıştır: php build-az-pack.php
 */

$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];

// key => [az, en] — diğer diller az/en’den türetilir
$rayons = [
    'absheron' => ['Abşeron', 'Absheron'],
    'agdam' => ['Ağdam', 'Agdam'],
    'agdash' => ['Ağdaş', 'Agdash'],
    'aghjabadi' => ['Ağcabədi', 'Aghjabadi'],
    'agsu' => ['Ağsu', 'Agsu'],
    'agstafa' => ['Ağstafa', 'Agstafa'],
    'astara' => ['Astara', 'Astara'],
    'babek' => ['Babək', 'Babek'],
    'baku' => ['Bakı', 'Baku'],
    'balakan' => ['Balakən', 'Balakan'],
    'barda' => ['Bərdə', 'Barda'],
    'beylagan' => ['Beyləqan', 'Beylagan'],
    'bilasuvar' => ['Biləsuvar', 'Bilasuvar'],
    'dashkasan' => ['Daşkəsən', 'Dashkasan'],
    'fuzuli' => ['Füzuli', 'Fuzuli'],
    'gadabay' => ['Gədəbəy', 'Gadabay'],
    'ganja' => ['Gəncə', 'Ganja'],
    'gobustan' => ['Qobustan', 'Gobustan'],
    'goranboy' => ['Goranboy', 'Goranboy'],
    'goychay' => ['Göyçay', 'Goychay'],
    'goygol' => ['Göygöl', 'Goygol'],
    'hajigabul' => ['Hacıqabul', 'Hajigabul'],
    'imishli' => ['İmişli', 'Imishli'],
    'ismailli' => ['İsmayıllı', 'Ismailli'],
    'jabrayil' => ['Cəbrayıl', 'Jabrayil'],
    'jalilabad' => ['Cəlilabad', 'Jalilabad'],
    'julfa' => ['Culfa', 'Julfa'],
    'kalbajar' => ['Kəlbəcər', 'Kalbajar'],
    'kangarli' => ['Kəngərli', 'Kangarli'],
    'khachmaz' => ['Xaçmaz', 'Khachmaz'],
    'khankendi' => ['Xankəndi', 'Khankendi'],
    'khizi' => ['Xızı', 'Khizi'],
    'khojaly' => ['Xocalı', 'Khojaly'],
    'khojavend' => ['Xocavənd', 'Khojavend'],
    'kurdamir' => ['Kürdəmir', 'Kurdamir'],
    'lachin' => ['Laçın', 'Lachin'],
    'lankaran' => ['Lənkəran', 'Lankaran'],
    'lerik' => ['Lerik', 'Lerik'],
    'masally' => ['Masallı', 'Masally'],
    'mingachevir' => ['Mingəçevir', 'Mingachevir'],
    'naftalan' => ['Naftalan', 'Naftalan'],
    'nakhchivan' => ['Naxçıvan', 'Nakhchivan'],
    'neftchala' => ['Neftçala', 'Neftchala'],
    'oghuz' => ['Oğuz', 'Oghuz'],
    'ordubad' => ['Ordubad', 'Ordubad'],
    'qabala' => ['Qəbələ', 'Gabala'],
    'qakh' => ['Qax', 'Gakh'],
    'qazakh' => ['Qazax', 'Gazakh'],
    'quba' => ['Quba', 'Guba'],
    'qubadli' => ['Qubadlı', 'Gubadli'],
    'qusar' => ['Qusar', 'Gusar'],
    'saatly' => ['Saatlı', 'Saatly'],
    'sabirabad' => ['Sabirabad', 'Sabirabad'],
    'sadarak' => ['Sədərək', 'Sadarak'],
    'salyan' => ['Salyan', 'Salyan'],
    'samukh' => ['Samux', 'Samukh'],
    'shabran' => ['Şabran', 'Shabran'],
    'shahbuz' => ['Şahbuz', 'Shahbuz'],
    'shaki' => ['Şəki', 'Shaki'],
    'shamakhi' => ['Şamaxı', 'Shamakhi'],
    'shamkir' => ['Şəmkir', 'Shamkir'],
    'sharur' => ['Şərur', 'Sharur'],
    'shirvan' => ['Şirvan', 'Shirvan'],
    'shusha' => ['Şuşa', 'Shusha'],
    'siazan' => ['Siyəzən', 'Siazan'],
    'sumqayit' => ['Sumqayıt', 'Sumgayit'],
    'tartar' => ['Tərtər', 'Tartar'],
    'tovuz' => ['Tovuz', 'Tovuz'],
    'ujar' => ['Ucar', 'Ujar'],
    'yardymli' => ['Yardımlı', 'Yardymli'],
    'yevlakh' => ['Yevlax', 'Yevlakh'],
    'zangilan' => ['Zəngilan', 'Zangilan'],
    'zaqatala' => ['Zaqatala', 'Zaqatala'],
    'zardab' => ['Zərdab', 'Zardab'],
];

$countryNames = [
    'az' => 'Azərbaycan',
    'tr' => 'Azerbaycan',
    'en' => 'Azerbaijan',
    'ar' => 'أذربيجان',
    'de' => 'Aserbaidschan',
    'fr' => 'Azerbaïdjan',
    'ka' => 'აზერბაიჯანი',
    'pl' => 'Azerbejdżan',
    'ro' => 'Azerbaidjan',
    'ru' => 'Азербайджан',
    'uk' => 'Азербайджан',
];

$nationality = [
    'az' => 'Azərbaycanlı',
    'tr' => 'Azerbaycanlı',
    'en' => 'Azerbaijani',
    'ar' => 'أذربيجاني',
    'de' => 'Aserbaidschaner',
    'fr' => 'Azerbaïdjanais',
    'ka' => 'აზერბაიჯანელი',
    'pl' => 'Azerbejdżanin',
    'ro' => 'Azerbaidjan',
    'ru' => 'Азербайджанец',
    'uk' => 'Азербайджанець',
];

function nameMap(string $az, string $en, array $locales): array
{
    // Latin diller çoğunlukla EN; az/tr/ru özel
    $tr = $az; // Azerbaycan adları TR’de genelde aynı yazım
    $map = [
        'az' => $az,
        'tr' => $tr,
        'en' => $en,
        'de' => $en,
        'fr' => $en,
        'pl' => $en,
        'ro' => $en,
        'ka' => $en,
        'ar' => $en,
        'ru' => $az, // pratikte az adları da kullanılır; EN fallback ok
        'uk' => $en,
    ];
    // Bilinen Rusça karşılıklar
    $ruKnown = [
        'Bakı' => 'Баку',
        'Gəncə' => 'Гянджа',
        'Sumqayıt' => 'Сумгаит',
        'Naxçıvan' => 'Нахичевань',
        'Lənkəran' => 'Ленкорань',
        'Mingəçevir' => 'Мингечевир',
        'Şəki' => 'Шеки',
        'Şirvan' => 'Ширван',
        'Yevlax' => 'Евлах',
        'Şuşa' => 'Шуша',
        'Xankəndi' => 'Ханкенди',
    ];
    if (isset($ruKnown[$az])) {
        $map['ru'] = $ruKnown[$az];
        $map['uk'] = $ruKnown[$az];
    }

    $out = [];
    foreach ($locales as $loc) {
        $out[$loc] = $map[$loc] ?? $en;
    }

    return $out;
}

$states = [];
foreach ($rayons as $key => [$az, $en]) {
    $names = nameMap($az, $en, $locales);
    $states[] = [
        'key' => $key,
        'name' => $names,
        'cities' => [
            [
                'key' => $key,
                'name' => $names,
            ],
        ],
    ];
}

$pack = [
    'id' => 'az',
    'iso2' => 'AZ',
    'code' => 'AZE',
    'version' => '1.0.0',
    'default_locale' => 'az',
    'country' => [
        'name' => $countryNames,
        'nationality' => $nationality,
    ],
    'states' => $states,
];

$dir = __DIR__.'/packs/az';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$json = json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
file_put_contents($dir.'/locations.json', $json."\n");
file_put_contents($dir.'/pack.json', json_encode([
    'id' => 'az',
    'version' => '1.0.0',
    'iso2' => 'AZ',
    'code' => 'AZE',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");

$manifest = [
    'version' => 1,
    'packs' => [
        [
            'id' => 'az',
            'iso2' => 'AZ',
            'code' => 'AZE',
            'version' => '1.0.0',
            'default_locale' => 'az',
            'path' => 'packs/az/locations.json',
            'states_count' => count($states),
            'cities_count' => count($states),
            'name' => $countryNames,
        ],
    ],
];
file_put_contents(__DIR__.'/manifest.json', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");

echo 'OK states='.count($states).' bytes='.strlen($json).PHP_EOL;
