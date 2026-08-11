# Worksoft Location Packs

Ülke → il → ilçe JSON paketleri (çok dilli). Worksoft `location` eklentisi bu katalogdan listeler ve kurar.

Bu depo **Laravel projesinin parçası değildir**; paketler isteğe bağlı indirilir.

## Yapı

```text
manifest.json              # Admin katalog listesi
schema/location-pack.schema.json
packs/<id>/
  pack.json
  locations.json
```

## Yeni ülke ekleme

1. `packs/<id>/locations.json` oluştur (şemaya uy).
2. `manifest.json` içine satır ekle.
3. Push et. Worksoft admin → Katalogu yenile → Kur.

Kod değişikliği gerekmez.

## Yerel geliştirme

Worksoft `.env`:

```env
LOCATION_PACKS_LOCAL_ROOT=D:/Ecommerce-proje/worksoft-location-packs
```

veya uzak:

```env
LOCATION_PACKS_MANIFEST_URL=https://raw.githubusercontent.com/<org>/worksoft-location-packs/main/manifest.json
```

## Diller

`tr`, `en`, `ar`, `az`, `de`, `fr`, `ka`, `pl`, `ro`, `ru`, `uk`
