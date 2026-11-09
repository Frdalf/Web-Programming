# FlipPlay Backend Engine — Modul 3 (lanjutan The Vibe Grid, Modul 2)

```
php index.php             # ambil data live dari Kitsu API (fallback lokal jika gagal)
php index.php --offline   # langsung pakai data/kitsu-fallback.json
php index.php --interactive [--offline]   # menu input/output (tambah, daftar, cari, detail, koleksi)
```
Butuh PHP 8.x (pakai `str_contains`, named argument, dll). Untuk mode online, ekstensi `openssl` harus aktif.

## Struktur
```
backend/
├── data/kitsu-fallback.json     data cadangan, format JSON:API persis seperti Kitsu
├── src/
│   ├── Models/   Media (abstract) ← Anime, Manga · User
│   ├── Auras/    Aura (abstract)  ← AllAura, GenreAura, BingeAura
│   └── Services/ KitsuClient, Library
└── index.php     entry point + autoloader
```

## Alur simulasi di `index.php`
1. **Menambahkan media ke dalam library**: data dari Kitsu atau fallback dimasukkan satu per satu (`+ [Anime] Judul ditambahkan`). Setelah itu 2 media dibuat manual lewat constructor (`new Anime('local-1', ...)`, `new Manga('local-2', ...)`) dan ditutup dengan total `$library->count()`.
2. **Menampilkan daftar media yang tersedia**: dikelompokkan per tipe dengan `getByType()`.
3. **Mencari media berdasarkan judul**: kata kunci diambil dinamis dari judul anime pertama, lalu judul media manual (pasti ketemu) dan kata acak (hasil kosong). **3b** berisi Aura-Based Discovery.
4. **Menampilkan informasi detail suatu media**: detail anime dan manga pertama (dinamis), media manual, serta contoh judul yang tidak ada (`null`).
5. **Menampilkan koleksi media milik pengguna**: tambah, tolak duplikat, hapus, tampilkan koleksi.

Tidak ada judul yang di-hardcode dari salah satu sumber data, jadi alur ini berjalan sama di mode online maupun `--offline`.

## Dari Modul 2 (JS) ke Modul 3 (PHP)
| vibe-grid.js | PHP OOP |
|---|---|
| `fetchAnime()` + `response.ok` + try/catch | `KitsuClient::fetch()` + cek HTTP status + `RuntimeException` |
| `categoryMap` dari `json.included` | `KitsuClient::mapJsonApi()` |
| objek anime `attributes` | objek `Anime` / `Manga` |
| `hasGenre()` | `Media::hasAnyGenre()` |
| array `AURAS` dengan fungsi `match` | class turunan `Aura` yang meng-override `matches()` |
| `applyFilters()` (keyword + aura) | `Library::search($keyword, $auraId)` |
| loading / error / empty state | pesan "Memuat...", "✖ Gagal...", "Tidak ada media yang cocok..." |

## Ketentuan demo
| Ketentuan | Di mana |
|---|---|
| ≥ 3 class | 10 class |
| Constructor | semua class; child memanggil `parent::__construct()` |
| Access modifier | `private` ($collection, $medias, $episodeCount), `protected` (property Media/Aura untuk child), `public` (method layanan) |
| Inheritance | Media → Anime/Manga; Aura → AllAura/GenreAura/BingeAura |
| Type hint & return type | `search(string $keyword, string $auraId): array`, `findByTitle(string $title): ?Media`, `matches(Media $media): bool` |
| Namespace | `FlipPlay\Models`, `FlipPlay\Auras`, `FlipPlay\Services` |
| Entry point | `index.php` |

## Persiapan tanya jawab
- **Kenapa Aura dijadikan class?** Di JS setiap Aura menyimpan fungsi `match`. Di OOP perilaku itu jadi method `matches()` yang di-override tiap child. `Library::search()` cukup memanggil `$aura->matches($m)` tanpa peduli Aura jenis apa. Inilah polymorphism.
- **Polymorphism di Binge Mode:** `BingeAura` memakai `getLength()` dan `getBingeThreshold()`. Untuk Anime hasilnya episode > 24 (sama dengan Modul 2), untuk Manga chapter > 100. Satu aturan, perilaku berbeda tergantung objeknya.
- **Kenapa `abstract`?** `Media` dan `Aura` tidak masuk akal di-`new` langsung. Abstract juga memaksa child mengimplementasikan method wajib.
- **`protected` vs `private`:** `$title` dan `$rating` di Media bersifat `protected` karena dibaca method turunan. `$episodeCount` bersifat `private` karena hanya urusan Anime. `$collection` bersifat `private` agar hanya bisa diubah lewat `addToCollection()`, yang menolak duplikat.
- **Encapsulation:** rating divalidasi 0–10 di `setRating()` (lihat bagian Bonus pada output).
- **`?Media`:** nullable return, sehingga `findByTitle()` mengembalikan `null` kalau judul tidak ada.
- **`static mapJsonApi()`:** method milik class, tidak butuh objek. Dipakai untuk data API maupun data cadangan.
- **Kunci `Tipe:id`:** ID Kitsu bisa sama antara anime dan manga, jadi kunci array digabung dengan tipenya.
- **Autoloader:** `spl_autoload_register` memetakan namespace ke folder, misalnya `FlipPlay\Auras\BingeAura` → `src/Auras/BingeAura.php`. Prinsipnya sama dengan PSR-4 Composer di Laravel.
- **`declare(strict_types=1)`:** type hint ditegakkan ketat. Mengirim `"12"` ke parameter `float` akan langsung error.
