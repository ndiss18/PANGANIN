# Panganin — Laravel MVC (Praktikum Week 7)

Panganin adalah website pemesanan makanan kantin kampus. Versi Laravel mengubah backend PHP Native OOP/MVC manual menjadi Laravel MVC, sementara HTML, tema, font, tata letak, CSS, gambar, dan interaksi katalog yang sudah ada tetap digunakan. Aplikasi PHP Native lama tetap tersedia di folder proyek ini; implementasi Laravel berada di `laravel/`.

## Teknologi

- PHP 8.3 atau lebih baru (proyek dibuat dengan Laravel 13; PHP lokal yang digunakan adalah 8.5).
- Composer 2.
- Laravel 13, Routing, Blade, middleware, session, validasi, dan autentikasi Laravel.
- MySQL melalui PDO MySQL.
- Eloquent ORM, migration, dan seeder.
- HTML, CSS, dan JavaScript vanilla; Fetch API GET katalog tetap digunakan.

## Laravel MVC

MVC memisahkan tanggung jawab aplikasi:

- **Model** mewakili data dan relasi Eloquent. Contoh: `Menu`, `Order`, `OrderDetail`, dan `User`.
- **View** menyajikan HTML dengan Blade. Katalog Blade memakai kembali struktur HTML lama dan file CSS yang sama.
- **Controller** memproses request, memvalidasi input, menjalankan aturan aplikasi melalui model, lalu mengirim view atau JSON.

Contoh request katalog mengalir dari route ke controller dan model:

```php
// laravel/routes/web.php
Route::get('/api/products', [MenuController::class, 'apiIndex'])
    ->name('api.products');

// laravel/app/Http/Controllers/MenuController.php
public function apiIndex(Request $request): JsonResponse
{
    $menus = $this->filteredMenus($request)->get();
    return response()->json($menus->map(fn (Menu $menu) => [
        'id' => $menu->id,
        'name' => $menu->nama_menu,
        'category' => $menu->kategori,
        'price' => $menu->harga,
        'image' => $menu->gambar,
        'description' => $menu->deskripsi,
    ]));
}
```

`resources/views/menu/index.blade.php` menyertakan bagian keranjang, checkout, dan riwayat sebagai Blade partial. `public/api.js` masih melakukan Fetch API GET ke route katalog Laravel; JavaScript menampilkan data yang sama dan tidak membuat katalog kedua.

## Struktur Laravel

```text
laravel/
  app/Http/Controllers/  AuthController, MenuController, CartController,
                        OrderController, AdminController
  app/Http/Middleware/  AdminMiddleware
  app/Models/           Menu, Order, OrderDetail, User
  config/               konfigurasi admin aplikasi
  database/migrations/  users, menus, orders, order_details
  database/seeders/     menu contoh dan admin opsional
  public/               front controller, CSS, JavaScript
  resources/views/
    layouts/            layout halaman akun dan admin
    menu/               katalog Blade dan detail menu
    cart/               partial keranjang
    checkout/           partial checkout
    orders/              riwayat dan detail pesanan
    admin/               dashboard, CRUD menu, dan pesanan
    auth/                login dan registrasi
  routes/web.php         seluruh route web dan JSON berbasis session
```

## Routing, Eloquent, dan keamanan

Seluruh URL didefinisikan di `laravel/routes/web.php`. Route bernama katalog seperti `api.products` menghubungkan GET Fetch API ke `MenuController`; route admin memakai middleware `admin`. Middleware memastikan pengguna sudah login dan memiliki role `admin`. Form memakai CSRF Laravel, validasi memakai `$request->validate()`, dan password disimpan menggunakan hashing Laravel.

Relasi Eloquent yang digunakan:

```php
// Order.php
public function details(): HasMany
{
    return $this->hasMany(OrderDetail::class);
}

// OrderDetail.php
public function order(): BelongsTo
{
    return $this->belongsTo(Order::class);
}
```

Keranjang disimpan sebagai ID menu dan kuantitas di Laravel Session. Harga serta subtotal ditampilkan kembali dari tabel `menus`; checkout mengunci dan membaca ulang menu dari database, menyimpan `orders` dan `order_details` dalam `DB::transaction()`, lalu mengosongkan keranjang hanya setelah transaksi berhasil.

## Migration dan Seeder

Migration membuat tabel `menus`, `orders`, dan `order_details` dengan foreign key dan timestamps. Tabel `users` ditambahkan untuk login pelanggan/admin. Seeder menyediakan sepuluh menu Indonesia (nasi goreng, ayam geprek, nasi telur, nasi pecel, mie ayam, bakso, nasi rames, soto ayam, es teh, dan es jeruk).

Contoh data awal memakai `Menu::updateOrCreate()` sehingga `db:seed` dapat dijalankan kembali tanpa menggandakan menu. Untuk membuat akun admin melalui seeder, isi `PANGANIN_ADMIN_NAME`, `PANGANIN_ADMIN_EMAIL`, dan `PANGANIN_ADMIN_PASSWORD` pada `.env` sebelum `php artisan db:seed`. Jika tidak diisi, seeder hanya membuat menu dan tidak menambahkan akun dengan kata sandi bawaan.

## Konfigurasi MySQL

1. Buat database MySQL:

   ```sql
   CREATE DATABASE panganin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Dari folder `laravel/`, salin `.env.example` menjadi `.env`, lalu sesuaikan:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=panganin_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. Pastikan PHP mengaktifkan ekstensi `pdo_mysql`. Atur kredensial admin sebelum menjalankan seeder bila admin ingin dibuat otomatis.

Jika Composer melaporkan ekstensi ZIP atau `unzip`/`7z` tidak tersedia, aktifkan `extension=zip` pada `php.ini` yang dipakai PHP CLI, lalu buka ulang terminal.

## Menjalankan secara lokal

Perintah berikut dijalankan dari folder Laravel, bukan folder PHP Native lama:

```powershell
cd laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Jika `.env` telah dibuat oleh Composer, jangan menyalinnya lagi; cukup isi konfigurasi database di `.env`, lalu jalankan `php artisan key:generate`. `php artisan key` bukan perintah Laravel; gunakan `php artisan key:generate`. Buka URL yang ditampilkan Artisan, biasanya `http://127.0.0.1:8000`.

## Alur penggunaan

1. Pengunjung membuka katalog; JavaScript memuat menu dari `GET /api/products` dan kategori dari database.
2. Pengunjung mencari/filter menu, membuka detail, dan menambah item. `GET /api/cart`, `POST/PUT/DELETE /api/cart/{menu}`, dan `DELETE /api/cart` mengelola keranjang pada session Laravel.
3. Pelanggan daftar atau masuk. Checkout memvalidasi nama, lokasi, dan catatan di server, lalu menyimpan pesanan beserta detail dengan harga database dalam satu transaksi.
4. `GET /api/orders` menampilkan riwayat akun pelanggan; `GET /api/orders/{order}` menampilkan rincian pesanan.
5. Admin masuk melalui `/admin/login`, membuka dashboard, mengelola menu di `/admin/menus`, serta melihat/memperbarui status pesanan di `/admin/orders`.

## Pengujian

Tes fitur memakai SQLite in-memory agar tidak mengubah database MySQL lokal:

```powershell
cd laravel
php artisan test
```

Tes mencakup katalog dan kategori API, keranjang session, checkout dengan harga dari database, penyimpanan detail pesanan, otorisasi admin, dan CRUD menu.
