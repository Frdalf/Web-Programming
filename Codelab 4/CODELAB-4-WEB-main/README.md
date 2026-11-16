# CODELAB-4-WEB — REST API Peminjaman Buku Perpustakaan

Repository ini berisi starter project REST API sistem perpustakaan menggunakan Laravel. Tugas codelab ini adalah melengkapi kemampuan API agar anggota perpustakaan (Member) dapat meminjam dan mengembalikan buku (Book) melalui endpoint yang disediakan.

## Requirement

- PHP 8.2 atau lebih tinggi
- Composer
- MySQL (aktif via Laragon/XAMPP)

## Setup Awal

Jalankan langkah berikut secara berurutan setelah clone repository:

```bash
git clone https://github.com/nisrinan/CODELAB-4-WEB.git
cd CODELAB-4-WEB
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di file `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD), lalu jalankan:

```bash
php artisan migrate --seed
```

Perintah ini akan membuat seluruh struktur table sekaligus mengisi data dummy member melalui seeder yang sudah disiapkan. Data dummy ini diperlukan karena endpoint borrow-book dan return-book membutuhkan `{id}` member yang valid — tanpa seeder, tabel members masih kosong dan tidak ada ID yang bisa dites.

Jalankan development server:

```bash
php artisan serve
```

## Tugas yang Harus Dilengkapi

Beberapa bagian kode telah dibuat dalam bentuk rumpang, ditandai komentar `TODO`. Lengkapi bagian yang masih rumpang tersebut menggunakan konsep Eloquent ORM, Relationship, dan API Resource.

**Perhatian:** `routes/api.php` yang tersedia saat ini hanya mendaftarkan `Route::apiResource()` untuk `books` dan `members`. Method `borrowBook()` dan `returnBook()` di `MemberController` tidak akan bisa diakses tanpa route tambahan. Tambahkan route berikut secara manual:

```php
Route::post('/members/{member}/borrow-book', [MemberController::class, 'borrowBook']);
Route::patch('/members/{member}/return-book/{book}', [MemberController::class, 'returnBook']);
```

## Referensi Kode yang Sudah Lengkap

Gunakan file-file berikut sebagai contoh format penulisan (tidak perlu diubah):

| File | Kegunaan sebagai referensi |
|---|---|
| `app/Models/Book.php` | Penulisan relasi belongsTo |
| `app/Http/Controllers/BookController.php` | CRUD Controller standar |
| `app/Http/Requests/StoreBookRequest.php` | Form Request dengan aturan berbeda untuk POST/PATCH |
| `app/Http/Resources/BookResource.php` | Format API Resource dengan whenLoaded |

## Pengujian (Postman)

Jika implementasi dilakukan dengan benar, alur pengujian berikut harus berjalan sesuai ekspektasi:

| Method | Endpoint | Body | Hasil yang Diharapkan |
|---|---|---|---|
| POST | `/api/members/{id}/borrow-book` | `{ "book_id": 1 }` | Buku berhasil dipinjam, `is_available` menjadi `false` |
| GET | `/api/members/{id}` | - | Menampilkan data member beserta daftar buku yang sedang dipinjam |
| PATCH | `/api/members/{id}/return-book/{book_id}` | - | Buku berhasil dikembalikan, `is_available` menjadi `true`, `member_id` menjadi `null` |

Gunakan `php artisan route:list` untuk memverifikasi seluruh route sudah terdaftar dengan benar sebelum pengujian.

## Troubleshooting

**Data member duplikat setelah migrate --seed dijalankan berkali-kali**

Jika perlu mengulang setup dari awal, gunakan perintah berikut, bukan `migrate --seed` saja:

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan menghapus seluruh table dan data terlebih dahulu sebelum diisi ulang dari nol, sehingga tidak ada data dummy yang dobel.

**Route borrowBook/returnBook mengembalikan 404**

Pastikan route tambahan (lihat bagian Tugas yang Harus Dilengkapi) sudah didaftarkan di `routes/api.php`, lalu cek dengan `php artisan route:list` untuk memastikan route benar-benar terbaca.

**Perubahan pada file .env tidak terlihat efeknya**

Bersihkan cache konfigurasi dengan:

```bash
php artisan config:clear
```
