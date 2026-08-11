# Worksoft Location Packs

Ülke → bölge/eyalet → şehir JSON paketleri (çok dilli). Worksoft `location` eklentisi bu katalogdan listeler ve kurar.

Bu depo **Laravel projesinin parçası değildir**; paketler isteğe bağlı indirilir.

## Yapı

```text
manifest.json
schema/location-pack.schema.json
data/country-i18n.php
packs/<id>/
  pack.json
  locations.json
```

## Paketler

| id | Not |
|----|-----|
| `az` | Özel Azerbaycan hiyerarşisi (ilçe + qəsəbə) |
| `tr` | Türkiye il + ilçe |
| `ua` | Ukrayna — Kiril adlar (`enrich-ua-cyrillic.php` + Geonames) |
| `ge` | Gürcistan — Mkhedruli adlar (`enrich-ge-mkhedruli.php` + Geonames) |
| AB-27 + `sy` `ae` | Bölge + şehir (dr5hn) |

**Çeviri politikası**

- **Ülke adı / uyruk:** 11 dil (`tr,en,ar,az,de,fr,ka,pl,ro,ru,uk`)
- **İl / eyalet / ilçe / şehir:** yalnızca o ülkenin kendi dili (`default_locale`). Örn. Bakı yalnızca `az`, İstanbul yalnızca `tr`, Berlin yalnızca `de`. Site dilinde çeviri yoksa ana kayıt (yerel ad) gösterilir.

## Yeni / yenile üretim

```bash
php generate-country-i18n.php   # data/country-i18n.php
php -d memory_limit=1024M build-region-packs.php
# Ukrayna Kiril (Geonames UA.zip gerekli):
# curl -L -o sources/geonames/UA.zip https://download.geonames.org/export/dump/UA.zip
# expand → sources/geonames/UA/UA.txt
php -d memory_limit=1024M enrich-ua-cyrillic.php
php -d memory_limit=512M enrich-ge-mkhedruli.php
```

`build-region-packs.php` dr5hn birleşik JSON’u `sources/dr5hn/` altına indirir (gitignore). `az`, `tr`, `ua` ve `ge` dokunulmaz.

## Attribution (ODbL)

Eyalet/şehir verisi:

> Data by Countries States Cities Database  
> https://github.com/dr5hn/countries-states-cities-database  
> Licensed under ODbL v1.0 — attribution required.

## Worksoft bağlantısı

`.env`:

```env
LOCATION_PACKS_MANIFEST_URL=https://raw.githubusercontent.com/Worksofr/worksoft-location-packs/main/manifest.json
# LOCATION_PACKS_LOCAL_ROOT=D:/Ecommerce-proje/worksoft-location-packs
# LOCATION_PACKS_MAX_BYTES=20971520
```

Admin → Konum içe aktar → Katalogu yenile → Kur.
