# 📚 Modul 3: Sistem Perpustakaan Sederhana (OOP PHP)

Latihan praktikum ini dirancang untuk mengasah pemahaman mengenai konsep dasar **Object-Oriented Programming (OOP)** pada PHP, khususnya:
1. **Class & Object**: Pembuatan dan instansiasi class.
2. **Encapsulation & Visibility**: Penggunaan access modifier (`private` dan `public`).
3. **Getter & State Methods**: Pengecekan status ketersediaan objek.
4. **Relasi Antar Objek (Association/Dependency)**: Interaksi antara entitas `Member`, `Book`, dan `Library`.

Pada latihan ini, kode pada file [`src/Member.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Member.php) sengaja dibuat **rumpang** (ditandai dengan `_____`). Tugas praktikan adalah melengkapi bagian yang kosong tersebut agar program dapat berjalan dengan baik dan menghasilkan output sesuai target.

---

## 📂 Struktur Proyek

```text
library-system-md3/
├── src/
│   ├── Book.php       # Class Book: merepresentasikan entitas buku dan statusnya
│   ├── Library.php    # Class Library: mengelola katalog buku perpustakaan
│   └── Member.php     # Class Member: [LATIHAN] entitas anggota yang meminjam buku
├── index.php          # Entry point untuk simulasi peminjaman & pengembalian buku
└── readme.md          # Petunjuk pengerjaan praktikum
```

---

## 🔍 Penjelasan Komponen

### 1. `Book` ([`src/Book.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Book.php))
- **Properti**:
  - `$title` (`string`): Judul buku.
  - `$author` (`string`): Penulis buku.
  - `$available` (`bool`): Status ketersediaan buku (default: `true`).
- **Method**:
  - `getTitle(): string`: Mengembalikan judul buku.
  - `isAvailable(): bool`: Mengembalikan status ketersediaan buku (`true` jika tersedia, `false` jika dipinjam).
  - `borrow(): void`: Mengubah status buku menjadi sedang dipinjam (`$available = false`).
  - `returnBook(): void`: Mengembalikan status buku menjadi tersedia (`$available = true`).

### 2. `Library` ([`src/Library.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Library.php))
- **Properti**:
  - `$books` (`array`): Menyimpan koleksi objek `Book`.
- **Method**:
  - `addBook(Book $book): void`: Menambahkan buku ke koleksi perpustakaan.
  - `listBooks(): void`: Menampilkan seluruh daftar buku beserta status ketersediaannya (`Available` atau `Borrowed`).

### 3. `Member` ([`src/Member.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Member.php)) &mdash; *Target Pengerjaan*
- **Properti**:
  - `$name` (`string`): Nama anggota perpustakaan.
  - `$borrowedBooks` (`array`): Daftar buku yang sedang dipinjam oleh anggota tersebut.
- **Method**:
  - `getName(): string`: Mengembalikan nama anggota.
  - `borrowBook(Book $book): void`: **(Lengkapi)** Logika anggota meminjam buku jika buku tersedia.
  - `returnBook(Book $book): void`: **(Lengkapi)** Logika anggota mengembalikan buku yang dipinjam.

---

## 📝 Petunjuk Pengerjaan

Buka file [`src/Member.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Member.php) dan lengkapi bagian rumpang berikut:

### 1. Method `borrowBook(Book $book)`
```php
public function borrowBook(Book $book): void
{
    // periksa apakah buku yang mau di pinjam masih tersedia
    if (_________________) {

        // ubah status buku menjadi dipinjam ("Borrowed")
        __________________;

        $this->borrowedBooks[] = $book;
    }
}
```
**Instruksi:**
- Pada kondisi `if (...)`: Panggil method dari objek `$book` untuk mengecek apakah buku masih tersedia (`isAvailable()`).
- Di dalam blok `if`: Panggil method dari objek `$book` untuk mengubah status ketersediaannya menjadi dipinjam (`borrow()`).

### 2. Method `returnBook(Book $book)`
```php
public function returnBook(Book $book): void
{
    // ubah status buku menjadi tersedia kembali ("Available")
    _______;

    foreach ($this->borrowedBooks as $index => $borrowedBook){
        if ($borrowedBook === $book) {
            unset($this->borrowedBooks[$index]);
        }
    }
}
```
**Instruksi:**
- Panggil method dari objek `$book` untuk mengubah status buku kembali tersedia (`returnBook()`).
- Logika berikutnya akan menghapus buku tersebut dari array daftar pinjaman `$borrowedBooks`.

---

## ▶️ Cara Menjalankan Program

Buka terminal pada direktori proyek, lalu jalankan perintah:

```bash
php index.php
```

---

## 🎯 Target Output

Jika kode pada [`src/Member.php`](file:///d:/Shofwan%20Alif/UMM/Sem6/asistensi/Dev%20modul%20web/Repo/library-system-md3/src/Member.php) sudah dilengkapi dengan benar, hasil eksekusi program di terminal harus sesuai dengan tampilan berikut:

```text
Member : Alice

Library Books
Clean Code - Borrowed
Design Patterns - Available

After Returning Book
Clean Code - Available
Design Patterns - Available
```

---

## 💡 Tips & Troubleshooting

1. **Error Undefined Constant:**
   Jika muncul pesan error:
   ```text
   Fatal error: Uncaught Error: Undefined constant "App\____"
   ```
   Hal ini menandakan bahwa tanda garis bawah (`____`) belum diganti dengan sintaks PHP yang valid.
2. **Operator Pemanggilan Method:**
   Ingat bahwa di PHP pemanggilan method atau properti objek menggunakan operator `->` (contoh: `$object->method()`), bukan titik (`.`).
3. **Versi PHP:**
   Gunakan PHP versi 7.4 ke atas (disarankan PHP 8.x) karena kode menggunakan typed properties seperti `private string $name;`.
