# Panganin — PHP Native OOP dan MVC Manual (Week 6)

Backend ini memakai PHP Native, PDO, dan MySQL `panganin_db` dari Week 5. Tidak memakai Laravel, CodeIgniter, atau framework PHP lain. Halaman katalog tetap memakai HTML, CSS, dan JavaScript Panganin yang sudah ada.

## Menjalankan dengan XAMPP

1. Salin folder proyek ke `C:\xampp\htdocs\panganin`.
2. Jalankan **Apache** dan **MySQL** melalui XAMPP Control Panel.
3. Buka phpMyAdmin, impor `database/panganin.sql`. Skrip memakai database `panganin_db`, membuat tabel, dan menambahkan data awal dengan `INSERT IGNORE`.
4. Buka `http://localhost/panganin/` atau `http://localhost/panganin/index.php`. Apache diarahkan ke front controller `index.php`.
5. Daftar akun pelanggan, masuk, pilih menu, lalu checkout. Pastikan ekstensi PDO MySQL (`pdo_mysql`) aktif.

Konfigurasi lokal bawaan memakai host `127.0.0.1`, user `root`, dan password kosong. Untuk konfigurasi lain, ubah environment variable `PANGANIN_DB_HOST`, `PANGANIN_DB_NAME`, `PANGANIN_DB_USER`, dan `PANGANIN_DB_PASS` atau konfigurasi koneksi di `config/database.php`.

### Jika katalog gagal dimuat

1. Pastikan **Apache** dan **MySQL** berstatus Running di XAMPP Control Panel; PHP API tidak dapat membaca katalog saat MySQL berhenti.
2. Pastikan database `panganin_db` dan tabel `products` serta `categories` tersedia di phpMyAdmin. Jika belum, impor `database/panganin.sql`.
3. Buka `http://localhost/panganin/index.php?route=api%2Fproducts`. Jika berhasil, halaman ini menampilkan JSON produk. Jika gagal, pesan pada katalog sekarang menunjukkan petunjuk koneksi yang dikirim server.
4. Pastikan `pdo_mysql` aktif dan host, database, user, serta password pada `config/database.php` sesuai konfigurasi MySQL XAMPP.

### Akun admin dan UMKM untuk praktikum

Registrasi publik selalu menghasilkan role `customer`. Setelah mendaftarkan akun, ubah role akun untuk pengujian melalui phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'email-yang-sudah-terdaftar';
-- atau
UPDATE users SET role = 'umkm' WHERE email = 'email-yang-sudah-terdaftar';
```

Keluar dan masuk kembali agar role baru dimuat ke session. Dashboard tersedia pada `index.php?route=dashboard`; pengelolaan produk tersedia pada `index.php?route=products%2Fmanage`.

## Struktur MVC

```text
app/
  Controllers/  AuthController, ProductController, CategoryController,
                OrderController, DashboardController, HomeController
  Models/       User, Product, Category, Order, OrderDetail
  Views/
    layouts/    Navigasi bersama halaman pengelolaan
    auth/       Login dan registrasi
    home/       Halaman utama; memakai kembali index.html tanpa desain ulang
    products/   Detail produk dan CRUD produk
    dashboard/  Ringkasan admin/UMKM
config/         Koneksi PDO dan bootstrap
core/           Controller, Model, Router, Session, helper
public/         Front controller publik
routes/         Pemetaan URL dan HTTP method
database/       Skema Week 5 dan data awal
```

Semua request utama melewati root `index.php`, yang meneruskan ke `public/index.php`; deklarasi route berada di `routes/web.php`. URL seperti katalog API, login, dashboard, dan CRUD memakai parameter `route`. Endpoint PHP lama tetap ada sebagai adapter tipis ke Router.

## Alur MVC request → response

1. Browser meminta URL, misalnya `index.php?route=api%2Fproducts`.
2. `public/index.php` memulai session, memuat bootstrap, dan menyerahkan method serta route ke `core/Router.php`.
3. Router memilih method controller dari `routes/web.php`.
4. Controller memeriksa autentikasi/role, CSRF, dan input; lalu memanggil Model.
5. Model mengakses database `panganin_db` dengan PDO prepared statements. Checkout mengunci stok dan menyimpan order, detail, serta pengurangan stok dalam satu transaksi.
6. Controller mengirim JSON atau merender View. Halaman utama merender kembali `index.html`; `style.css`, `api.js`, dan `script.js` tetap dipakai.

## Penerapan OOP (bukti Week 6)

- **Class/object, property/method, constructor:** `Database::connection()`, objek `Product`, `User`, `Order`, dan `OrderDetail`.
- **Encapsulation/access modifier:** koneksi database statis `private` di `Database`; koneksi model `protected` di `Model`; operasi database disediakan sebagai method model publik.
- **Inheritance:** semua model mewarisi `Model`; semua controller mewarisi `Controller`.
- **Polymorphism:** model mengimplementasikan `tableName()` masing-masing. `DashboardController` menerima objek-objek `Model` dan memanggil `countRecords()`; method tersebut menggunakan nama tabel yang ditentukan oleh subclass.
- **Session/security:** class `Session` mengelola session, autentikasi, dan token CSRF.

## Fitur dan akses route

| Fitur | Route |
|---|---|
| Beranda/katalog, keranjang, checkout, riwayat | `/` |
| Login / registrasi / logout | `?route=login` / `?route=register` / `?route=logout` |
| Session frontend | `?route=api%2Fsession` |
| Katalog produk / kategori | `?route=api%2Fproducts` / `?route=api%2Fcategories` |
| Detail produk | `?route=products%2Fshow&id=101` |
| CRUD produk admin/UMKM | `?route=products%2Fmanage` |
| Dashboard admin/UMKM | `?route=dashboard` |
| Pesanan dan riwayat pelanggan | `?route=api%2Forders` (POST membuat pesanan, GET membaca riwayat) |

Pencarian dan filter katalog tetap berjalan pada katalog yang sama. Riwayat yang telah tersedia pada desain sekarang dibaca dari pesanan akun di database. Tidak ada fitur pantau pesanan baru. Harga dan stok checkout selalu diambil/diperiksa ulang dari database, bukan dipercaya dari browser.
