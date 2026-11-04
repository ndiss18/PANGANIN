# Backend PHP Native Panganin

Frontend HTML, CSS, dan JavaScript Panganin tetap dipakai. Katalog sekarang membaca data dari MySQL melalui PHP; checkout mengirim item ke server untuk validasi stok dan perhitungan total.

## Menjalankan dengan XAMPP

1. Salin seluruh isi folder proyek ini ke `C:\xampp\htdocs\panganin`.
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, pilih **Import**, lalu impor `database/panganin.sql`. File ini membuat `panganin_db`, tabel, kategori, dan 17 menu awal.
4. Buka `http://localhost/panganin/index.html`.
5. Daftar akun pelanggan, masuk, tambahkan menu ke keranjang, isi lokasi pengambilan, lalu pesan.

Konfigurasi standar di `config/database.php` memakai host `127.0.0.1`, database `panganin_db`, user `root`, dan kata sandi kosong seperti instalasi XAMPP umum. Jika konfigurasi lokal berbeda, ubah nilai tersebut atau atur environment variable `PANGANIN_DB_HOST`, `PANGANIN_DB_NAME`, `PANGANIN_DB_USER`, dan `PANGANIN_DB_PASS`. Pastikan ekstensi PDO MySQL (`pdo_mysql`) aktif di PHP XAMPP.

Jangan membuka halaman melalui `file://` atau server statis saja. PHP dan session memerlukan Apache/PHP.

## Pengujian fitur

- **Koneksi dan katalog:** buka `http://localhost/panganin/api/products.php`; respons berupa JSON menu dari database. Kategori dapat diperiksa melalui `http://localhost/panganin/api/categories.php`.
- **Registrasi, login, logout:** gunakan tautan akun di area checkout. Registrasi publik hanya membuat role `customer`.
- **Tambah/edit/hapus menu:** setelah akun dibuat, ubah rolenya melalui phpMyAdmin:

  ```sql
  UPDATE users SET role = 'admin' WHERE email = 'email-yang-sudah-terdaftar';
  ```

  Keluar lalu masuk kembali, kemudian pilih **Kelola Menu**. Untuk akun UMKM gunakan `role = 'umkm'`. Akun customer tidak boleh membuka halaman pengelolaan.
- **Checkout:** pesan satu atau beberapa produk. Pastikan total pada `orders.total_harga`, baris pada `order_details`, dan stok produk berubah sesuai pesanan. Checkout akan ditolak bila stok tidak mencukupi.

## Bagian untuk laporan praktikum Week 5

- `config/database.php`: koneksi PHP Native menggunakan PDO ke MySQL.
- `auth/register.php` dan `auth/login.php`: registrasi/login memakai `password_hash()` dan `password_verify()`, prepared statements, dan session.
- `api/products.php` dan `api/categories.php`: katalog dan kategori dibaca dari tabel MySQL lalu dikirim sebagai JSON ke frontend yang sudah ada.
- `products/index.php`: operasi tambah, baca, ubah, dan hapus produk dibatasi untuk role `admin` dan `umkm`.
- `api/orders.php`: validasi sesi/CSRF, transaksi database, penguncian stok, penyimpanan pesanan serta detail, dan perhitungan total dari harga database (bukan nilai dari browser).
- `database/panganin.sql`: pembuatan database/tabel, foreign key, serta data awal kategori dan menu kantin Indonesia.

Prepared statements dipakai pada query yang menerima input. Password tidak disimpan dalam teks biasa; form dan API yang mengubah data memakai token CSRF. Riwayat yang tetap tampil pada frontend mengikuti desain dan perilaku sebelumnya; tidak ada fitur pantau pesanan baru.
