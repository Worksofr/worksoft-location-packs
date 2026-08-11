<?php
/**
 * Azerbaycan konum paketi v1.3.0
 * Ülke adı: 11 dil. İl / ilçe / qəsəbə: yalnızca Azərbaycanca (az).
 * Büyük şehirler: ilçe + qəsəbə/kənd karışık (detaylı)
 * Diğer rayonlar: Merkez + qəsəbə/kasaba (kənd yok)
 */

$locales = ['tr', 'en', 'ar', 'az', 'de', 'fr', 'ka', 'pl', 'ro', 'ru', 'uk'];

/** Ülke adı / uyruk — tüm diller */
function nm(string $az, string $en, array $locales, array $extra = []): array
{
    $map = array_merge([
        'az' => $az, 'tr' => $az, 'en' => $en,
        'de' => $en, 'fr' => $en, 'pl' => $en, 'ro' => $en, 'ka' => $en, 'ar' => $en,
        'ru' => $extra['ru'] ?? $az, 'uk' => $extra['uk'] ?? ($extra['ru'] ?? $en),
    ], $extra);
    $out = [];
    foreach ($locales as $loc) {
        $out[$loc] = $map[$loc] ?? $en;
    }

    return $out;
}

/** İl / ilçe — yalnızca az */
function placeAz(string $az): array
{
    return ['az' => $az];
}

function city(string $key, string $az, string $en = '', array $locales = [], array $extra = []): array
{
    return ['key' => $key, 'name' => placeAz($az)];
}

function citiesFromPairs(array $pairs, array $locales = []): array
{
    $out = [];
    foreach ($pairs as $key => $pair) {
        $az = is_array($pair) ? (string) ($pair[0] ?? '') : (string) $pair;
        if ($az === '') {
            continue;
        }
        $out[] = city($key, $az);
    }

    return $out;
}

$countryNames = [
    'az' => 'Azərbaycan', 'tr' => 'Azerbaycan', 'en' => 'Azerbaijan', 'ar' => 'أذربيجان',
    'de' => 'Aserbaidschan', 'fr' => 'Azerbaïdjan', 'ka' => 'აზერბაიჯანი', 'pl' => 'Azerbejdżan',
    'ro' => 'Azerbaidjan', 'ru' => 'Азербайджан', 'uk' => 'Азербайджан',
];
$nationality = [
    'az' => 'Azərbaycanlı', 'tr' => 'Azerbaycanlı', 'en' => 'Azerbaijani', 'ar' => 'أذربيجاني',
    'de' => 'Aserbaidschaner', 'fr' => 'Azerbaïdjanais', 'ka' => 'აზერბაიჯანელი', 'pl' => 'Azerbejdżanin',
    'ro' => 'Azerbaidjan', 'ru' => 'Азербайджанец', 'uk' => 'Азербайджанець',
];

// --- Büyük şehirler (detaylı: rayon/ilçe + qəsəbə) ---
$bakuDistricts = [
    'binagadi' => ['Binəqədi', 'Binagadi'],
    'garadagh' => ['Qaradağ', 'Garadagh'],
    'khazar' => ['Xəzər', 'Khazar'],
    'sabail' => ['Səbail', 'Sabail'],
    'sabunchu' => ['Sabunçu', 'Sabunchu'],
    'surakhani' => ['Suraxanı', 'Surakhani'],
    'pirallahi' => ['Pirallahı', 'Pirallahi'],
    'nizami' => ['Nizami', 'Nizami'],
    'khatai' => ['Xətai', 'Khatai'],
    'narimanov' => ['Nərimanov', 'Narimanov'],
    'nasimi' => ['Nəsimi', 'Nasimi'],
    'yasamal' => ['Yasamal', 'Yasamal'],
];

$bakuSettlements = [
    // Binəqədi
    'm-a-rasulzade' => ['M.Ə. Rəsulzadə', 'M.A. Rasulzade'],
    'bileceri' => ['Biləcəri', 'Bilajari'],
    'binagadi-q' => ['Binəqədi qəsəbə', 'Binagadi settlement'],
    'khojasan' => ['Xocəsən', 'Khojasan'],
    'sulutepe' => ['Sulutəpə', 'Sulutepe'],
    '28-may' => ['28 May', '28 May'],
    // Xətai
    'ahmadli' => ['Əhmədli', 'Ahmadli'],
    // Xəzər
    'shuvelan' => ['Şüvəlan', 'Shuvelan'],
    'bina' => ['Binə', 'Bina'],
    'buzovna' => ['Buzovna', 'Buzovna'],
    'qala' => ['Qala', 'Gala'],
    'mardakan' => ['Mərdəkan', 'Mardakan'],
    'shagan' => ['Şağan', 'Shagan'],
    'turkan' => ['Türkan', 'Turkan'],
    'zira' => ['Zirə', 'Zira'],
    // Qaradağ
    'lokbatan' => ['Lökbatan', 'Lokbatan'],
    'heibat' => ['Heybət', 'Heybat'],
    'shubani' => ['Şubanı', 'Shubani'],
    'cheyildag' => ['Çeyildağ', 'Cheyildagh'],
    'alat' => ['Ələt', 'Alat'],
    'bash-alat' => ['Baş Ələt', 'Bash Alat'],
    'kotal' => ['Kotal', 'Kotal'],
    'garakosa' => ['Qarakosa', 'Garakosa'],
    'pirsaat' => ['Pirsaat', 'Pirsaat'],
    'shikhlar' => ['Şıxlar', 'Shikhlar'],
    'yeni-alat' => ['Yeni Ələt', 'Yeni Alat'],
    'korgoz' => ['Korgöz', 'Korgoz'],
    'gyzildash' => ['Qızıldaş', 'Gizildash'],
    'shongar' => ['Şonqar', 'Shongar'],
    'gobustan-q' => ['Qobustan (Qaradağ)', 'Gobustan (Garadagh)'],
    'mushvigabad' => ['Müşfiqabad', 'Mushfigabad'],
    'puta' => ['Puta', 'Puta'],
    'sahil' => ['Sahil', 'Sahil'],
    'garadagh-q' => ['Qaradağ qəsəbə', 'Garadagh settlement'],
    'sangachal' => ['Sanqaçal', 'Sangachal'],
    'umid' => ['Ümid', 'Umid'],
    // Nizami
    'keshla' => ['Keşlə', 'Keshla'],
    // Pirallahı
    'pirallahi-q' => ['Pirallahı qəsəbə', 'Pirallahi settlement'],
    'chilov' => ['Çilov', 'Chilov'],
    'gurgan' => ['Gürgən', 'Gurgan'],
    'neft-dashlari' => ['Neft Daşları', 'Oil Rocks'],
    // Sabunçu
    'bakikhanov' => ['Bakıxanov', 'Bakikhanov'],
    'balakhani' => ['Balaxanı', 'Balakhani'],
    'bilgah' => ['Bilgəh', 'Bilgah'],
    'kurdakhani' => ['Kürdəxanı', 'Kurdakhani'],
    'mashtaga' => ['Maştağa', 'Mashtaga'],
    'nardaran' => ['Nardaran', 'Nardaran'],
    'pirshagi' => ['Pirşağı', 'Pirshagi'],
    'ramana' => ['Ramana', 'Ramana'],
    'sabunchu-q' => ['Sabunçu qəsəbə', 'Sabunchu settlement'],
    'zabrat' => ['Zabrat', 'Zabrat'],
    // Səbail
    'badamdar' => ['Badamdar', 'Badamdar'],
    'bibiheybat' => ['Bibiheybət', 'Bibiheybat'],
    // Suraxanı
    'amircan' => ['Əmircan', 'Amircan'],
    'bulbule' => ['Bülbülə', 'Bulbule'],
    'hovsan' => ['Hövsan', 'Hovsan'],
    'garachukhur' => ['Qaraçuxur', 'Garachukhur'],
    'yeni-surakhani' => ['Yeni Suraxanı', 'Yeni Surakhani'],
    'zig' => ['Zığ', 'Zig'],
];

$major = [];

// Bakı
$bakuCities = array_merge(
    [city('merkez', 'Merkez', 'Center', $locales, ['ru' => 'Центр'])],
    citiesFromPairs($bakuDistricts, $locales),
    citiesFromPairs($bakuSettlements, $locales)
);
$major['baku'] = [
    'name' => placeAz('Bakı'),
    'cities' => $bakuCities,
];

// Gəncə
$major['ganja'] = [
    'name' => placeAz('Gəncə'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'kapaz' => ['Kəpəz', 'Kapaz'],
        'nizami-g' => ['Nizami (Gəncə)', 'Nizami (Ganja)'],
        'javadkhan' => ['Cavadxan', 'Javadkhan'],
        'hacikand' => ['Hacıkənd', 'Hajikand'],
        'goygol-q' => ['Göygöl qəsəbə', 'Goygol settlement'],
        'mahseti' => ['Məhsəti', 'Mahsati'],
        'natavan' => ['Natəvan', 'Natavan'],
        'sadilli' => ['Sadıllı', 'Sadilli'],
        'shikhzamanli' => ['Şıxzamanlı', 'Shikhzamanli'],
    ], $locales),
];

// Sumqayıt
$major['sumqayit'] = [
    'name' => placeAz('Sumqayıt'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'jorat' => ['Corat', 'Jorat'],
        'haci-zeynalabdin' => ['Hacı Zeynalabdin', 'Haji Zeynalabdin'],
    ], $locales),
];

// Mingəçevir
$major['mingachevir'] = [
    'name' => placeAz('Mingəçevir'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
    ], $locales),
];

// Şirvan
$major['shirvan'] = [
    'name' => placeAz('Şirvan'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'bayramli' => ['Bayramlı', 'Bayramli'],
        'haciqahramanli' => ['Hacıqəhrəmanlı', 'Hajigahramanli'],
    ], $locales),
];

// Naxçıvan şəhəri
$major['nakhchivan'] = [
    'name' => placeAz('Naxçıvan'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'aliabad' => ['Əliabad', 'Aliabad'],
    ], $locales),
];

// Lənkəran şəhəri
$major['lankaran'] = [
    'name' => placeAz('Lənkəran'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'liman' => ['Liman', 'Liman'],
        'garmatuk' => ['Gərmətük', 'Garmatuk'],
        'shirinsu' => ['Şirinsu', 'Shirinsu'],
        'vel' => ['Vel', 'Vel'],
        'sutamurdov' => ['Sutəmurdov', 'Sutamurdov'],
    ], $locales),
];

// Şəki şəhəri
$major['shaki'] = [
    'name' => placeAz('Şəki'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'kish' => ['Kiş', 'Kish'],
        'okhud' => ['Oxud', 'Okhud'],
        'turan' => ['Turan', 'Turan'],
        'inqiloy' => ['İnçə', 'Incha'],
    ], $locales),
];

// Yevlax şəhəri
$major['yevlakh'] = [
    'name' => placeAz('Yevlax'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'aran' => ['Aran', 'Aran'],
        'qoyunbinasi' => ['Qoyunbinəsi', 'Goyunbinesi'],
    ], $locales),
];

// Naftalan
$major['naftalan'] = [
    'name' => placeAz('Naftalan'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
        'qashalti' => ['Qaşıaltı', 'Gashalti'],
        'eskiqala' => ['Eskiqala', 'Eskigala'],
    ], $locales),
];

// Şuşa şəhəri
$major['shusha'] = [
    'name' => placeAz('Şuşa'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
    ], $locales),
];

// Xankəndi
$major['khankendi'] = [
    'name' => placeAz('Xankəndi'),
    'cities' => citiesFromPairs([
        'merkez' => ['Merkez', 'Center'],
    ], $locales),
];

// --- Diğer rayonlar: Merkez + qəsəbə/kasaba (kənd yok) ---
$lightRayons = [
    'absheron' => ['Abşeron', 'Absheron', [
        'merkez' => ['Merkez (Xırdalan)', 'Center (Khirdalan)'],
        'khirdalan' => ['Xırdalan', 'Khirdalan'],
        'mehdiabad' => ['Mehdiabad', 'Mehdiabad'],
        'saray' => ['Saray', 'Saray'],
        'ceyranbatan' => ['Ceyranbatan', 'Jeyranbatan'],
        'digah' => ['Digah', 'Digah'],
        'fatmai' => ['Fatmayı', 'Fatmai'],
        'goradil' => ['Görədil', 'Goradil'],
        'hokmali' => ['Hökümdarlı', 'Hokmali'],
        'mashaga' => ['Məmmədli', 'Mammadli'],
        'new-surakhani-a' => ['Yeni Suraxanı', 'Yeni Surakhani'],
        'pirakashkul' => ['Pirəkəşkül', 'Pirakashkul'],
    ]],
    'agdam' => ['Ağdam', 'Agdam', ['merkez' => ['Merkez', 'Center'], 'quzanli' => ['Quzanlı', 'Guzanli']]],
    'agdash' => ['Ağdaş', 'Agdash', ['merkez' => ['Merkez', 'Center'], 'kosha' => ['Köşə', 'Kosha'], 'yukhari-zeyid' => ['Yuxarı Zeynəddin', 'Yukhari Zeynaddin']]],
    'aghjabadi' => ['Ağcabədi', 'Aghjabadi', ['merkez' => ['Merkez', 'Center'], 'hindarx' => ['Hindərx', 'Hindarkh']]],
    'agsu' => ['Ağsu', 'Agsu', ['merkez' => ['Merkez', 'Center']]],
    'agstafa' => ['Ağstafa', 'Agstafa', ['merkez' => ['Merkez', 'Center'], 'vurgun' => ['Vurğun', 'Vurgun'], 'poylu' => ['Poylu', 'Poylu']]],
    'astara' => ['Astara', 'Astara', ['merkez' => ['Merkez', 'Center'], 'erzinke' => ['Kijəbə', 'Kijaba'], 'archivan' => ['Ərçivan', 'Archivan']]],
    'babek' => ['Babək', 'Babek', ['merkez' => ['Merkez', 'Center'], 'nehram' => ['Nehrəm', 'Nehram'], 'jahri' => ['Cahri', 'Jahri']]],
    'balakan' => ['Balakən', 'Balakan', ['merkez' => ['Merkez', 'Center'], 'katex' => ['Katex', 'Katekh'], 'mahallar' => ['Mahamalar', 'Mahamalar']]],
    'barda' => ['Bərdə', 'Barda', ['merkez' => ['Merkez', 'Center'], 'soylu' => ['Soğulcan', 'Sogulcan']]],
    'beylagan' => ['Beyləqan', 'Beylagan', ['merkez' => ['Merkez', 'Center'], 'duzqaraqoyunlu' => ['Düzqaraqoyunlu', 'Duzgaragoyunlu']]],
    'bilasuvar' => ['Biləsuvar', 'Bilasuvar', ['merkez' => ['Merkez', 'Center']]],
    'dashkasan' => ['Daşkəsən', 'Dashkasan', ['merkez' => ['Merkez', 'Center'], 'quşçu' => ['Quşçu', 'Gushchu'], 'alunitdag' => ['Alunitdağ', 'Alunitdag']]],
    'fuzuli' => ['Füzuli', 'Fuzuli', ['merkez' => ['Merkez', 'Center'], 'horadiz' => ['Horadiz', 'Horadiz']]],
    'gadabay' => ['Gədəbəy', 'Gadabay', ['merkez' => ['Merkez', 'Center'], 'soyudlu' => ['Soyudlu', 'Soyudlu'], 'slavyanka' => ['Slavyanka', 'Slavyanka']]],
    'gobustan' => ['Qobustan', 'Gobustan', ['merkez' => ['Merkez', 'Center']]],
    'goranboy' => ['Goranboy', 'Goranboy', ['merkez' => ['Merkez', 'Center'], 'dalimammadli' => ['Dəliməmmədli', 'Dalimammadli'], 'gizilhajili' => ['Qızılhacılı', 'Gizilhajili']]],
    'goychay' => ['Göyçay', 'Goychay', ['merkez' => ['Merkez', 'Center']]],
    'goygol' => ['Göygöl', 'Goygol', ['merkez' => ['Merkez', 'Center'], 'hajikend' => ['Hacıkənd', 'Hajikend'], 'kurekchay' => ['Kürəkçay', 'Kurekchay']]],
    'hajigabul' => ['Hacıqabul', 'Hajigabul', ['merkez' => ['Merkez', 'Center'], 'mugan' => ['Muğan', 'Mugan'], 'navahi' => ['Navahi', 'Navahi']]],
    'imishli' => ['İmişli', 'Imishli', ['merkez' => ['Merkez', 'Center'], 'bahar' => ['Bahar', 'Bahar']]],
    'ismailli' => ['İsmayıllı', 'Ismailli', ['merkez' => ['Merkez', 'Center'], 'basgal' => ['Basqal', 'Basgal'], 'lahic' => ['Lahıc', 'Lahij']]],
    'jabrayil' => ['Cəbrayıl', 'Jabrayil', ['merkez' => ['Merkez', 'Center']]],
    'jalilabad' => ['Cəlilabad', 'Jalilabad', ['merkez' => ['Merkez', 'Center'], 'goytepe' => ['Göytəpə', 'Goytepe'], 'privolnoye' => ['Privolnoye', 'Privolnoye']]],
    'julfa' => ['Culfa', 'Julfa', ['merkez' => ['Merkez', 'Center'], 'haydarabad' => ['Heydərabad', 'Heydarabad']]],
    'kalbajar' => ['Kəlbəcər', 'Kalbajar', ['merkez' => ['Merkez', 'Center'], 'istiklal' => ['İstisu', 'Istisu']]],
    'kangarli' => ['Kəngərli', 'Kangarli', ['merkez' => ['Merkez', 'Center'], 'givrag' => ['Qıvraq', 'Givrag']]],
    'khachmaz' => ['Xaçmaz', 'Khachmaz', ['merkez' => ['Merkez', 'Center'], 'xudat' => ['Xudat', 'Khudat'], 'samur' => ['Samurçay', 'Samurchay'], 'yalama' => ['Yalama', 'Yalama']]],
    'khizi' => ['Xızı', 'Khizi', ['merkez' => ['Merkez', 'Center'], 'altiqash' => ['Altiağac', 'Altiagach'], 'shurabad' => ['Şuraabad', 'Shurabad']]],
    'khojaly' => ['Xocalı', 'Khojaly', ['merkez' => ['Merkez', 'Center']]],
    'khojavend' => ['Xocavənd', 'Khojavend', ['merkez' => ['Merkez', 'Center'], 'hadrut' => ['Hadrut', 'Hadrut']]],
    'kurdamir' => ['Kürdəmir', 'Kurdamir', ['merkez' => ['Merkez', 'Center']]],
    'lachin' => ['Laçın', 'Lachin', ['merkez' => ['Merkez', 'Center'], 'gulebird' => ['Qarıqışlaq', 'Garigishlag']]],
    'lankaran-rayon' => ['Lənkəran rayonu', 'Lankaran District', [
        'merkez' => ['Merkez', 'Center'],
        'hirkan' => ['Hirkan', 'Hirkan'],
        'narimanabad' => ['Nərimanabad', 'Narimanabad'],
        'ashagi-nuvedi' => ['Aşağı Nuvədi', 'Ashagi Nuvedi'],
    ]],
    'lerik' => ['Lerik', 'Lerik', ['merkez' => ['Merkez', 'Center']]],
    'masally' => ['Masallı', 'Masally', ['merkez' => ['Merkez', 'Center'], 'boradigah' => ['Boradigah', 'Boradigah'], 'arkivan' => ['Ərkivan', 'Arkivan']]],
    'neftchala' => ['Neftçala', 'Neftchala', ['merkez' => ['Merkez', 'Center'], 'bank' => ['Bankə', 'Banka'], 'hasilli' => ['Həsənabad', 'Hasanabad']]],
    'oghuz' => ['Oğuz', 'Oghuz', ['merkez' => ['Merkez', 'Center']]],
    'ordubad' => ['Ordubad', 'Ordubad', ['merkez' => ['Merkez', 'Center'], 'paraqa' => ['Parağa', 'Paraga'], 'sabirkand' => ['Sabirkənd', 'Sabirkand']]],
    'qabala' => ['Qəbələ', 'Gabala', ['merkez' => ['Merkez', 'Center'], 'vandam' => ['Vəndam', 'Vandam'], 'nij' => ['Nic', 'Nij'], 'bick' => ['Bıççaqçı', 'Bichagchi']]],
    'qakh' => ['Qax', 'Gakh', ['merkez' => ['Merkez', 'Center'], 'ilisu' => ['İlisu', 'Ilisu'], 'qaxingiloy' => ['Qaxingiloy', 'Gakhingiloy']]],
    'qazakh' => ['Qazax', 'Gazakh', ['merkez' => ['Merkez', 'Center'], 'ashagi-salahli' => ['Aşağı Salahlı', 'Ashagi Salahli']]],
    'quba' => ['Quba', 'Guba', ['merkez' => ['Merkez', 'Center'], 'qonalqala' => ['Qonalqala', 'Gonalgala'], 'red-settlement' => ['Qırmızı Qəsəbə', 'Red Town'], 'davachi' => ['Zizik', 'Zizik']]],
    'qubadli' => ['Qubadlı', 'Gubadli', ['merkez' => ['Merkez', 'Center']]],
    'qusar' => ['Qusar', 'Gusar', ['merkez' => ['Merkez', 'Center'], 'samur' => ['Samur', 'Samur'], 'haci-zeynalabdin-q' => ['Hacı Zeynalabdin', 'Haji Zeynalabdin']]],
    'saatly' => ['Saatlı', 'Saatly', ['merkez' => ['Merkez', 'Center']]],
    'sabirabad' => ['Sabirabad', 'Sabirabad', ['merkez' => ['Merkez', 'Center'], 'galagayin' => ['Qalağayın', 'Galagayin'], 'muradxan' => ['Muradxanlı', 'Muradkhanli']]],
    'sadarak' => ['Sədərək', 'Sadarak', ['merkez' => ['Merkez', 'Center'], 'heydarabad' => ['Heydərabad', 'Heydarabad']]],
    'salyan' => ['Salyan', 'Salyan', ['merkez' => ['Merkez', 'Center'], 'gizilagac' => ['Qızılağac', 'Gizilagaj'], 'severo-vostok' => ['Şorsulu', 'Shorsulu']]],
    'samukh' => ['Samux', 'Samukh', ['merkez' => ['Merkez', 'Center'], 'karyagin' => ['Qarayeri', 'Garayeri']]],
    'shabran' => ['Şabran', 'Shabran', ['merkez' => ['Merkez', 'Center'], 'adam' => ['Adam', 'Adam']]],
    'shahbuz' => ['Şahbuz', 'Shahbuz', ['merkez' => ['Merkez', 'Center'], 'badamli' => ['Badamlı', 'Badamli']]],
    'shaki-rayon' => ['Şəki rayonu', 'Shaki District', [
        'merkez' => ['Merkez', 'Center'],
        'bash-keldek' => ['Baş Kəldək', 'Bash Keldek'],
        'oxud' => ['Oxud', 'Okhud'],
    ]],
    'shamakhi' => ['Şamaxı', 'Shamakhi', ['merkez' => ['Merkez', 'Center'], 'sabir' => ['Sabir', 'Sabir'], 'melham' => ['Mədrəsə', 'Madrasa']]],
    'shamkir' => ['Şəmkir', 'Shamkir', ['merkez' => ['Merkez', 'Center'], 'dolyar' => ['Dəllər', 'Dallar'], 'kurekchay' => ['Kura', 'Kura'], 'chumakli' => ['Çinarlı', 'Chinarli']]],
    'sharur' => ['Şərur', 'Sharur', ['merkez' => ['Merkez', 'Center'], 'duzgeh' => ['Düzkənd', 'Duzkend'], 'maharramkend' => ['Mağaraçuğ', 'Magarachug']]],
    'shusha-rayon' => ['Şuşa rayonu', 'Shusha District', ['merkez' => ['Merkez', 'Center']]],
    'siazan' => ['Siyəzən', 'Siazan', ['merkez' => ['Merkez', 'Center'], 'gilazi' => ['Giləzi', 'Gilazi']]],
    'tartar' => ['Tərtər', 'Tartar', ['merkez' => ['Merkez', 'Center'], 'shikharkh' => ['Şıxarx', 'Shikharkh']]],
    'tovuz' => ['Tovuz', 'Tovuz', ['merkez' => ['Merkez', 'Center'], 'qovlar' => ['Qovlar', 'Govlar'], 'ashagi-quşçu' => ['Aşağı Quşçu', 'Ashagi Gushchu']]],
    'ujar' => ['Ucar', 'Ujar', ['merkez' => ['Merkez', 'Center']]],
    'yardymli' => ['Yardımlı', 'Yardymli', ['merkez' => ['Merkez', 'Center']]],
    'yevlakh-rayon' => ['Yevlax rayonu', 'Yevlakh District', ['merkez' => ['Merkez', 'Center']]],
    'zangilan' => ['Zəngilan', 'Zangilan', ['merkez' => ['Merkez', 'Center'], 'minjivan' => ['Mincivan', 'Minjivan']]],
    'zaqatala' => ['Zaqatala', 'Zaqatala', ['merkez' => ['Merkez', 'Center'], 'aliabad-z' => ['Əliabad', 'Aliabad'], 'muxax' => ['Muxax', 'Mukhakh']]],
    'zardab' => ['Zərdab', 'Zardab', ['merkez' => ['Merkez', 'Center']]],
];

$states = [];
foreach ($major as $key => $row) {
    $states[] = [
        'key' => $key,
        'name' => $row['name'],
        'detail_level' => 'major_city',
        'cities' => $row['cities'],
    ];
}

foreach ($lightRayons as $key => [$az, $en, $cityPairs]) {
    $states[] = [
        'key' => $key,
        'name' => placeAz($az),
        'detail_level' => 'rayon_light',
        'cities' => citiesFromPairs($cityPairs, $locales),
    ];
}

$cityCount = 0;
foreach ($states as $s) {
    $cityCount += count($s['cities']);
}

$pack = [
    'id' => 'az',
    'iso2' => 'AZ',
    'code' => 'AZE',
    'version' => '1.3.0',
    'default_locale' => 'az',
    'notes' => [
        'tr' => 'Ülke adı 11 dil. İl/ilçe/qəsəbə yalnızca Azərbaycanca. Büyük şehirler detaylı; diğer rayonlar: Merkez + kasaba.',
        'en' => 'Country name in 11 locales. Places in Azerbaijani only. Major cities detailed; other rayons: center + towns.',
    ],
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

$locationsPath = $dir.'/locations.json';
file_put_contents($locationsPath, json_encode($pack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents($dir.'/pack.json', json_encode([
    'id' => 'az',
    'version' => '1.3.0',
    'iso2' => 'AZ',
    'code' => 'AZE',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

$fileBytes = (int) filesize($locationsPath);
$azRow = [
    'id' => 'az',
    'iso2' => 'AZ',
    'code' => 'AZE',
    'version' => '1.3.0',
    'default_locale' => 'az',
    'path' => 'packs/az/locations.json',
    'states_count' => count($states),
    'cities_count' => $cityCount,
    'file_bytes' => $fileBytes,
    'estimate_disk_bytes' => 4096 + (count($states) * 400) + ($cityCount * 450) + 2048,
    'name' => $countryNames,
];

$manifestPath = __DIR__.'/manifest.json';
$manifest = is_file($manifestPath)
    ? json_decode((string) file_get_contents($manifestPath), true)
    : ['version' => 1, 'packs' => []];
$found = false;
foreach ($manifest['packs'] as &$row) {
    if (($row['id'] ?? '') === 'az') {
        $row = $azRow;
        $found = true;
        break;
    }
}
unset($row);
if (! $found) {
    array_unshift($manifest['packs'], $azRow);
}
file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

echo 'OK states='.count($states).' cities='.$cityCount."\n";
