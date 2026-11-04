CREATE DATABASE IF NOT EXISTS panganin_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE panganin_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(120) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer', 'admin', 'umkm') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_nama (nama_kategori)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi TEXT NOT NULL,
  harga INT UNSIGNED NOT NULL,
  gambar VARCHAR(2048) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category (category_id),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  total_harga DECIMAL(14,2) UNSIGNED NOT NULL,
  kode_pembayaran VARCHAR(24) NOT NULL,
  status_pembayaran ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
  lokasi_pengambilan VARCHAR(160) NOT NULL,
  catatan TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_kode_pembayaran (kode_pembayaran),
  KEY idx_orders_user_created (user_id, created_at),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_details (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  harga INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_details_order (order_id),
  KEY idx_order_details_product (product_id),
  CONSTRAINT fk_order_details_order
    FOREIGN KEY (order_id) REFERENCES orders (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_order_details_product
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (id, nama_kategori) VALUES
  (1, 'Makanan berat'),
  (2, 'Mie dan bakso'),
  (3, 'Lauk dan gorengan'),
  (4, 'Minuman'),
  (5, 'Camilan');

INSERT IGNORE INTO products (id, nama_produk, deskripsi, harga, gambar, category_id, stok) VALUES
  (101, 'Nasi Goreng', 'Nasi goreng bumbu gurih khas kantin kampus dengan telur, suwiran ayam, dan kerupuk.', 15000, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80', 1, 25),
  (102, 'Nasi Ayam Geprek', 'Nasi hangat dengan ayam goreng dan sambal geprek.', 18000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80', 1, 20),
  (103, 'Nasi Telur', 'Nasi dengan telur dan lauk sederhana untuk makan siang.', 12000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80', 1, 25),
  (104, 'Nasi Ayam Penyet', 'Ayam goreng penyet dengan nasi dan sambal.', 18000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80', 1, 20),
  (105, 'Nasi Pecel', 'Nasi dengan sayuran rebus dan siraman bumbu kacang.', 13000, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80', 1, 20),
  (106, 'Nasi Rames', 'Nasi dengan pilihan lauk dan sayur rumahan.', 15000, 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80', 1, 20),
  (107, 'Mie Goreng', 'Mie goreng berbumbu dengan sayuran dan telur.', 13000, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 2, 20),
  (108, 'Mie Ayam', 'Mie kenyal dengan ayam kecap, sawi hijau, dan kuah kaldu.', 13000, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80', 2, 20),
  (109, 'Bakso', 'Bakso dengan kuah hangat, mie, dan sayuran.', 14000, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80', 2, 25),
  (110, 'Soto Ayam', 'Soto ayam berkuah gurih dengan nasi dan pelengkap.', 15000, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80', 1, 20),
  (111, 'Nasi Kuning', 'Nasi berbumbu kunyit dengan lauk dan pelengkap.', 14000, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80', 1, 20),
  (112, 'Tempe Goreng', 'Tempe berbumbu yang digoreng hingga renyah.', 3000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80', 3, 40),
  (113, 'Tahu Isi', 'Tahu goreng berisi sayuran dengan kulit renyah.', 3000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80', 3, 40),
  (114, 'Es Teh', 'Seduhan teh melati dengan es batu yang menyegarkan.', 5000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', 4, 50),
  (115, 'Es Jeruk', 'Minuman jeruk segar yang disajikan dengan es.', 7000, 'https://images.unsplash.com/photo-1547514701-42782101795e?auto=format&fit=crop&w=600&q=80', 4, 40),
  (116, 'Kopi', 'Kopi racikan kantin untuk menemani kuliah.', 8000, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80', 4, 40),
  (117, 'Roti Bakar', 'Roti panggang dengan isian cokelat dan keju.', 10000, 'https://images.unsplash.com/photo-1584776296944-ab6fb57b0bdd?auto=format&fit=crop&w=600&q=80', 5, 20);
