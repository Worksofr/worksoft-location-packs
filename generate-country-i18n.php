<?php
/**
 * 31 ülke × 11 dil: ad + uyruk haritası üretir (data/country-i18n.php).
 * Kaynak: Worksoft ecommerce countries.php + elle düzeltilmiş az/ulusal adlar.
 */

$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];
$iso2List = [
    'at', 'be', 'bg', 'hr', 'cy', 'cz', 'dk', 'ee', 'fi', 'fr', 'de', 'gr', 'hu', 'ie', 'it',
    'lv', 'lt', 'lu', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'si', 'es', 'se',
    'ge', 'ua', 'sy', 'ae',
];

$langRoot = 'D:/Ecommerce-proje/WORKSOFT_LARAVEL_PROJE/platform/plugins/ecommerce/resources/lang';
$namesByLocale = [];
foreach ($locales as $loc) {
    $path = $langRoot.'/'.$loc.'/countries.php';
    if (! is_file($path)) {
        fwrite(STDERR, "Missing $path\n");
        exit(1);
    }
    /** @var array<string, string> $arr */
    $arr = include $path;
    $namesByLocale[$loc] = $arr;
}

// Azerbaycanca dosyada çoğu İngilizce — düzeltmeler
$azFixes = [
    'at' => 'Avstriya', 'be' => 'Belçika', 'bg' => 'Bolqarıstan', 'hr' => 'Xorvatiya',
    'cy' => 'Kipr', 'cz' => 'Çexiya', 'dk' => 'Danimarka', 'ee' => 'Estoniya',
    'fi' => 'Finlandiya', 'fr' => 'Fransa', 'de' => 'Almaniya', 'gr' => 'Yunanıstan',
    'hu' => 'Macarıstan', 'ie' => 'İrlandiya', 'it' => 'İtaliya', 'lv' => 'Latviya',
    'lt' => 'Litva', 'lu' => 'Lüksemburq', 'mt' => 'Malta', 'nl' => 'Niderland',
    'pl' => 'Polşa', 'pt' => 'Portuqaliya', 'ro' => 'Rumıniya', 'sk' => 'Slovakiya',
    'si' => 'Sloveniya', 'es' => 'İspaniya', 'se' => 'İsveç', 'ge' => 'Gürcüstan',
    'ua' => 'Ukrayna', 'sy' => 'Suriya', 'ae' => 'Birləşmiş Ərəb Əmirlikləri',
];
foreach ($azFixes as $code => $name) {
    $namesByLocale['az'][$code] = $name;
}

// Almanca "Georgia" (ABD karışıklığı) → Georgien
$namesByLocale['de']['ge'] = 'Georgien';

$iso3 = [
    'at' => 'AUT', 'be' => 'BEL', 'bg' => 'BGR', 'hr' => 'HRV', 'cy' => 'CYP',
    'cz' => 'CZE', 'dk' => 'DNK', 'ee' => 'EST', 'fi' => 'FIN', 'fr' => 'FRA',
    'de' => 'DEU', 'gr' => 'GRC', 'hu' => 'HUN', 'ie' => 'IRL', 'it' => 'ITA',
    'lv' => 'LVA', 'lt' => 'LTU', 'lu' => 'LUX', 'mt' => 'MLT', 'nl' => 'NLD',
    'pl' => 'POL', 'pt' => 'PRT', 'ro' => 'ROU', 'sk' => 'SVK', 'si' => 'SVN',
    'es' => 'ESP', 'se' => 'SWE', 'ge' => 'GEO', 'ua' => 'UKR', 'sy' => 'SYR',
    'ae' => 'ARE',
];

$defaultLocale = [
    'at' => 'de', 'be' => 'nl', 'bg' => 'en', 'hr' => 'en', 'cy' => 'en',
    'cz' => 'en', 'dk' => 'en', 'ee' => 'en', 'fi' => 'en', 'fr' => 'fr',
    'de' => 'de', 'gr' => 'en', 'hu' => 'en', 'ie' => 'en', 'it' => 'en',
    'lv' => 'en', 'lt' => 'en', 'lu' => 'fr', 'mt' => 'en', 'nl' => 'nl',
    'pl' => 'pl', 'pt' => 'en', 'ro' => 'ro', 'sk' => 'en', 'si' => 'en',
    'es' => 'en', 'se' => 'en', 'ge' => 'ka', 'ua' => 'uk', 'sy' => 'ar',
    'ae' => 'ar',
];

// Uyruk (demonym) — 11 dil
$nationality = [
    'at' => [
        'tr' => 'Avusturyalı', 'en' => 'Austrian', 'ar' => 'نمساوي', 'az' => 'Avstriyalı',
        'de' => 'Österreicher', 'fr' => 'Autrichien', 'ka' => 'ავსტრიელი', 'pl' => 'Austriak',
        'ro' => 'Austriac', 'ru' => 'Австриец', 'uk' => 'Австрієць',
    ],
    'be' => [
        'tr' => 'Belçikalı', 'en' => 'Belgian', 'ar' => 'بلجيكي', 'az' => 'Belçikalı',
        'de' => 'Belgier', 'fr' => 'Belge', 'ka' => 'ბელგიელი', 'pl' => 'Belg',
        'ro' => 'Belgian', 'ru' => 'Бельгиец', 'uk' => 'Бельгієць',
    ],
    'bg' => [
        'tr' => 'Bulgar', 'en' => 'Bulgarian', 'ar' => 'بلغاري', 'az' => 'Bolqar',
        'de' => 'Bulgare', 'fr' => 'Bulgare', 'ka' => 'ბულგარელი', 'pl' => 'Bułgar',
        'ro' => 'Bulgar', 'ru' => 'Болгарин', 'uk' => 'Болгарин',
    ],
    'hr' => [
        'tr' => 'Hırvat', 'en' => 'Croatian', 'ar' => 'كرواتي', 'az' => 'Xorvat',
        'de' => 'Kroate', 'fr' => 'Croate', 'ka' => 'ხორვატი', 'pl' => 'Chorwat',
        'ro' => 'Croat', 'ru' => 'Хорват', 'uk' => 'Хорват',
    ],
    'cy' => [
        'tr' => 'Kıbrıslı', 'en' => 'Cypriot', 'ar' => 'قبرصي', 'az' => 'Kiprlі',
        'de' => 'Zyprer', 'fr' => 'Chypriote', 'ka' => 'კვიპროსელი', 'pl' => 'Cypryjczyk',
        'ro' => 'Cipriot', 'ru' => 'Киприот', 'uk' => 'Кіпріот',
    ],
    'cz' => [
        'tr' => 'Çek', 'en' => 'Czech', 'ar' => 'تشيكي', 'az' => 'Çex',
        'de' => 'Tscheche', 'fr' => 'Tchèque', 'ka' => 'ჩეხი', 'pl' => 'Czech',
        'ro' => 'Ceh', 'ru' => 'Чех', 'uk' => 'Чех',
    ],
    'dk' => [
        'tr' => 'Danimarkalı', 'en' => 'Danish', 'ar' => 'دنماركي', 'az' => 'Danimarkalı',
        'de' => 'Däne', 'fr' => 'Danois', 'ka' => 'დანიელი', 'pl' => 'Duńczyk',
        'ro' => 'Danez', 'ru' => 'Датчанин', 'uk' => 'Данець',
    ],
    'ee' => [
        'tr' => 'Estonyalı', 'en' => 'Estonian', 'ar' => 'إستوني', 'az' => 'Eston',
        'de' => 'Este', 'fr' => 'Estonien', 'ka' => 'ესტონელი', 'pl' => 'Estończyk',
        'ro' => 'Estonian', 'ru' => 'Эстонец', 'uk' => 'Естонець',
    ],
    'fi' => [
        'tr' => 'Fin', 'en' => 'Finnish', 'ar' => 'فنلندي', 'az' => 'Fin',
        'de' => 'Finne', 'fr' => 'Finlandais', 'ka' => 'ფინელი', 'pl' => 'Fin',
        'ro' => 'Finlandez', 'ru' => 'Финн', 'uk' => 'Фін',
    ],
    'fr' => [
        'tr' => 'Fransız', 'en' => 'French', 'ar' => 'فرنسي', 'az' => 'Fransız',
        'de' => 'Franzose', 'fr' => 'Français', 'ka' => 'ფრანგი', 'pl' => 'Francuz',
        'ro' => 'Francez', 'ru' => 'Француз', 'uk' => 'Француз',
    ],
    'de' => [
        'tr' => 'Alman', 'en' => 'German', 'ar' => 'ألماني', 'az' => 'Alman',
        'de' => 'Deutscher', 'fr' => 'Allemand', 'ka' => 'გერმანელი', 'pl' => 'Niemiec',
        'ro' => 'German', 'ru' => 'Немец', 'uk' => 'Німець',
    ],
    'gr' => [
        'tr' => 'Yunan', 'en' => 'Greek', 'ar' => 'يوناني', 'az' => 'Yunan',
        'de' => 'Grieche', 'fr' => 'Grec', 'ka' => 'ბერძენი', 'pl' => 'Grek',
        'ro' => 'Grec', 'ru' => 'Грек', 'uk' => 'Грек',
    ],
    'hu' => [
        'tr' => 'Macar', 'en' => 'Hungarian', 'ar' => 'هنغاري', 'az' => 'Macar',
        'de' => 'Ungar', 'fr' => 'Hongrois', 'ka' => 'უნგრელი', 'pl' => 'Węgier',
        'ro' => 'Maghiar', 'ru' => 'Венгр', 'uk' => 'Угорець',
    ],
    'ie' => [
        'tr' => 'İrlandalı', 'en' => 'Irish', 'ar' => 'أيرلندي', 'az' => 'İrland',
        'de' => 'Ire', 'fr' => 'Irlandais', 'ka' => 'ირლანდიელი', 'pl' => 'Irlandczyk',
        'ro' => 'Irlandez', 'ru' => 'Ирландец', 'uk' => 'Ірландець',
    ],
    'it' => [
        'tr' => 'İtalyan', 'en' => 'Italian', 'ar' => 'إيطالي', 'az' => 'İtalyan',
        'de' => 'Italiener', 'fr' => 'Italien', 'ka' => 'იტალიელი', 'pl' => 'Włoch',
        'ro' => 'Italian', 'ru' => 'Итальянец', 'uk' => 'Італієць',
    ],
    'lv' => [
        'tr' => 'Leton', 'en' => 'Latvian', 'ar' => 'لاتفي', 'az' => 'Latış',
        'de' => 'Lette', 'fr' => 'Letton', 'ka' => 'ლატვიელი', 'pl' => 'Łotysz',
        'ro' => 'Leton', 'ru' => 'Латыш', 'uk' => 'Латиш',
    ],
    'lt' => [
        'tr' => 'Litvan', 'en' => 'Lithuanian', 'ar' => 'ليتواني', 'az' => 'Litvalı',
        'de' => 'Litauer', 'fr' => 'Lituanien', 'ka' => 'ლიეტუველი', 'pl' => 'Litwin',
        'ro' => 'Lituanian', 'ru' => 'Литовец', 'uk' => 'Литовець',
    ],
    'lu' => [
        'tr' => 'Lüksemburglu', 'en' => 'Luxembourger', 'ar' => 'لوكسمبورغي', 'az' => 'Lüksemburqlu',
        'de' => 'Luxemburger', 'fr' => 'Luxembourgeois', 'ka' => 'ლუქსემბურგელი', 'pl' => 'Luksemburczyk',
        'ro' => 'Luxemburghez', 'ru' => 'Люксембуржец', 'uk' => 'Люксембуржець',
    ],
    'mt' => [
        'tr' => 'Maltalı', 'en' => 'Maltese', 'ar' => 'مالطي', 'az' => 'Maltalı',
        'de' => 'Malteser', 'fr' => 'Maltais', 'ka' => 'მალტელი', 'pl' => 'Maltańczyk',
        'ro' => 'Maltez', 'ru' => 'Мальтиец', 'uk' => 'Мальтієць',
    ],
    'nl' => [
        'tr' => 'Hollandalı', 'en' => 'Dutch', 'ar' => 'هولندي', 'az' => 'Holland',
        'de' => 'Niederländer', 'fr' => 'Néerlandais', 'ka' => 'ჰოლანდიელი', 'pl' => 'Holender',
        'ro' => 'Olandez', 'ru' => 'Нидерландец', 'uk' => 'Нідерландець',
    ],
    'pl' => [
        'tr' => 'Polonyalı', 'en' => 'Polish', 'ar' => 'بولندي', 'az' => 'Polyak',
        'de' => 'Pole', 'fr' => 'Polonais', 'ka' => 'პოლონელი', 'pl' => 'Polak',
        'ro' => 'Polonez', 'ru' => 'Поляк', 'uk' => 'Поляк',
    ],
    'pt' => [
        'tr' => 'Portekizli', 'en' => 'Portuguese', 'ar' => 'برتغالي', 'az' => 'Portuqal',
        'de' => 'Portugiese', 'fr' => 'Portugais', 'ka' => 'პორტუგალიელი', 'pl' => 'Portugalczyk',
        'ro' => 'Portughez', 'ru' => 'Португалец', 'uk' => 'Португалець',
    ],
    'ro' => [
        'tr' => 'Romen', 'en' => 'Romanian', 'ar' => 'روماني', 'az' => 'Rumın',
        'de' => 'Rumäne', 'fr' => 'Roumain', 'ka' => 'რუმინელი', 'pl' => 'Rumun',
        'ro' => 'Român', 'ru' => 'Румын', 'uk' => 'Румун',
    ],
    'sk' => [
        'tr' => 'Slovak', 'en' => 'Slovak', 'ar' => 'سلوفاكي', 'az' => 'Slovak',
        'de' => 'Slowake', 'fr' => 'Slovaque', 'ka' => 'სლოვაკი', 'pl' => 'Słowak',
        'ro' => 'Slovac', 'ru' => 'Словак', 'uk' => 'Словак',
    ],
    'si' => [
        'tr' => 'Sloven', 'en' => 'Slovenian', 'ar' => 'سلوفيني', 'az' => 'Sloven',
        'de' => 'Slowene', 'fr' => 'Slovène', 'ka' => 'სლოვენიელი', 'pl' => 'Słoweniec',
        'ro' => 'Sloven', 'ru' => 'Словенец', 'uk' => 'Словенець',
    ],
    'es' => [
        'tr' => 'İspanyol', 'en' => 'Spanish', 'ar' => 'إسباني', 'az' => 'İspan',
        'de' => 'Spanier', 'fr' => 'Espagnol', 'ka' => 'ესპანელი', 'pl' => 'Hiszpan',
        'ro' => 'Spaniol', 'ru' => 'Испанец', 'uk' => 'Іспанець',
    ],
    'se' => [
        'tr' => 'İsveçli', 'en' => 'Swedish', 'ar' => 'سويدي', 'az' => 'İsveçli',
        'de' => 'Schwede', 'fr' => 'Suédois', 'ka' => 'შვედი', 'pl' => 'Szwed',
        'ro' => 'Suedez', 'ru' => 'Швед', 'uk' => 'Швед',
    ],
    'ge' => [
        'tr' => 'Gürcü', 'en' => 'Georgian', 'ar' => 'جورجي', 'az' => 'Gürcü',
        'de' => 'Georgier', 'fr' => 'Géorgien', 'ka' => 'ქართველი', 'pl' => 'Gruzin',
        'ro' => 'Georgian', 'ru' => 'Грузин', 'uk' => 'Грузин',
    ],
    'ua' => [
        'tr' => 'Ukraynalı', 'en' => 'Ukrainian', 'ar' => 'أوكراني', 'az' => 'Ukraynalı',
        'de' => 'Ukrainer', 'fr' => 'Ukrainien', 'ka' => 'უკრაინელი', 'pl' => 'Ukraińiec',
        'ro' => 'Ucrainean', 'ru' => 'Украинец', 'uk' => 'Українець',
    ],
    'sy' => [
        'tr' => 'Suriyeli', 'en' => 'Syrian', 'ar' => 'سوري', 'az' => 'Suriyalı',
        'de' => 'Syrer', 'fr' => 'Syrien', 'ka' => 'სირიელი', 'pl' => 'Syryjczyk',
        'ro' => 'Sirian', 'ru' => 'Сириец', 'uk' => 'Сирієць',
    ],
    'ae' => [
        'tr' => 'BAEli', 'en' => 'Emirati', 'ar' => 'إماراتي', 'az' => 'BƏƏli',
        'de' => 'Emirati', 'fr' => 'Émirati', 'ka' => 'ემირატელი', 'pl' => 'Emirateńczyk',
        'ro' => 'Emiratez', 'ru' => 'Эмиратец', 'uk' => 'Еміратець',
    ],
];

// Fix typo in cy az
$nationality['cy']['az'] = 'Kiprli';
$nationality['ae']['tr'] = 'BAE vatandaşı';
$nationality['ae']['az'] = 'BƏƏ vətəndaşı';

$out = [];
foreach ($iso2List as $iso2) {
    $name = [];
    foreach ($locales as $loc) {
        $v = $namesByLocale[$loc][$iso2] ?? null;
        if ($v === null || trim((string) $v) === '') {
            fwrite(STDERR, "Missing name $loc/$iso2\n");
            exit(1);
        }
        $name[$loc] = html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (! isset($nationality[$iso2])) {
        fwrite(STDERR, "Missing nationality $iso2\n");
        exit(1);
    }
    $out[strtoupper($iso2)] = [
        'iso2' => strtoupper($iso2),
        'iso3' => $iso3[$iso2],
        'id' => $iso2,
        'default_locale' => $defaultLocale[$iso2],
        'name' => $name,
        'nationality' => $nationality[$iso2],
    ];
}

$dir = 'D:/Ecommerce-proje/worksoft-location-packs/data';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$export = var_export($out, true);
$php = "<?php\n/**\n * AB-27 + GE/UA/SY/AE ülke adı ve uyruk (11 dil).\n * Üretim: generate-country-i18n.php — elle düzenlenebilir.\n */\n\nreturn ".$export.";\n";
file_put_contents($dir.'/country-i18n.php', $php);

echo 'OK countries='.count($out).PHP_EOL;
