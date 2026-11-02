<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
$user = requireRoles(['admin', 'umkm']);
$connection = null;
$error = '';
$message = isset($_GET['saved']) ? 'Menu berhasil disimpan.' : '';
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$editingProduct = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $providedToken = $_POST['csrf_token'] ?? null;
    if (!verifyCsrfToken(is_string($providedToken) ? $providedToken : null)) {
        $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
    } else {
        $action = $_POST['action'] ?? '';
        $rawId = $_POST['id'] ?? '';
        $id = $rawId === '' ? null : filter_var($rawId, FILTER_VALIDATE_INT);

        if ($action === 'delete') {
            if ($id === false || $id < 1) {
                $error = 'ID menu tidak valid.';
            } else {
                try {
                    $statement = database()->prepare('DELETE FROM products WHERE id = :id');
                    $statement->execute([':id' => $id]);
                    if ($statement->rowCount() === 0) {
                        $error = 'Menu tidak ditemukan.';
                    } else {
                        header('Location: index.php?saved=1');
                        exit;
                    }
                } catch (PDOException $exception) {
                    if ($exception->getCode() === '23000') {
                        $error = 'Menu sudah digunakan dalam pesanan dan tidak dapat dihapus. Atur stok menjadi 0 jika menu tidak dijual lagi.';
                    } else {
                        error_log('Gagal menghapus produk Panganin: ' . $exception->getMessage());
                        $error = 'Menu tidak dapat dihapus. Periksa koneksi database.';
                    }
                }
            }
        } elseif ($action === 'save') {
            $nameInput = $_POST['nama_produk'] ?? '';
            $descriptionInput = $_POST['deskripsi'] ?? '';
            $priceInput = $_POST['harga'] ?? '';
            $imageInput = $_POST['gambar'] ?? '';
            $categoryInput = $_POST['category_id'] ?? '';
            $stockInput = $_POST['stok'] ?? '';

            $name = is_string($nameInput) ? trim($nameInput) : '';
            $description = is_string($descriptionInput) ? trim($descriptionInput) : '';
            $image = is_string($imageInput) ? trim($imageInput) : '';
            $price = filter_var($priceInput, FILTER_VALIDATE_INT);
            $categoryId = filter_var($categoryInput, FILTER_VALIDATE_INT);
            $stock = filter_var($stockInput, FILTER_VALIDATE_INT);

            if ($id === false || ($id !== null && $id < 1)) {
                $error = 'ID menu tidak valid.';
            } elseif (strlen($name) > 600 || preg_match('/\A.{1,150}\z/us', $name) !== 1 || preg_match('/[\x00-\x1F]/', $name)) {
                $error = 'Nama menu wajib diisi dan maksimal 150 karakter.';
            } elseif ($description === '' || strlen($description) > 2000) {
                $error = 'Deskripsi wajib diisi dan maksimal 2000 karakter.';
            } elseif ($price === false || $price < 1 || $price > 4294967295) {
                $error = 'Harga harus berupa bilangan bulat lebih besar dari 0.';
            } elseif ($categoryId === false || $categoryId < 1) {
                $error = 'Pilih kategori yang valid.';
            } elseif ($stock === false || $stock < 0 || $stock > 999999) {
                $error = 'Stok harus berupa bilangan bulat antara 0 sampai 999999.';
            } elseif (strlen($image) > 2048 || filter_var($image, FILTER_VALIDATE_URL) === false || parse_url($image, PHP_URL_SCHEME) !== 'https') {
                $error = 'Gambar harus menggunakan URL HTTPS yang valid.';
            } else {
                try {
                    $connection = database();
                    $categoryStatement = $connection->prepare('SELECT id FROM categories WHERE id = :id');
                    $categoryStatement->execute([':id' => $categoryId]);
                    if (!$categoryStatement->fetch()) {
                        $error = 'Kategori yang dipilih tidak ditemukan.';
                    } elseif ($id === null) {
                        $statement = $connection->prepare(
                            'INSERT INTO products (nama_produk, deskripsi, harga, gambar, category_id, stok)
                             VALUES (:nama_produk, :deskripsi, :harga, :gambar, :category_id, :stok)'
                        );
                        $statement->execute([
                            ':nama_produk' => $name,
                            ':deskripsi' => $description,
                            ':harga' => $price,
                            ':gambar' => $image,
                            ':category_id' => $categoryId,
                            ':stok' => $stock,
                        ]);
                        header('Location: index.php?saved=1');
                        exit;
                    } else {
                        $statement = $connection->prepare(
                            'UPDATE products
                             SET nama_produk = :nama_produk, deskripsi = :deskripsi, harga = :harga,
                                 gambar = :gambar, category_id = :category_id, stok = :stok
                             WHERE id = :id'
                        );
                        $statement->execute([
                            ':nama_produk' => $name,
                            ':deskripsi' => $description,
                            ':harga' => $price,
                            ':gambar' => $image,
                            ':category_id' => $categoryId,
                            ':stok' => $stock,
                            ':id' => $id,
                        ]);
                        header('Location: index.php?saved=1');
                        exit;
                    }
                } catch (PDOException $exception) {
                    error_log('Gagal menyimpan produk Panganin: ' . $exception->getMessage());
                    $error = 'Menu gagal disimpan. Periksa koneksi dan data database.';
                }
            }
            if ($error !== '') {
                $editingProduct = [
                    'id' => $id === false ? null : $id,
                    'nama_produk' => $name,
                    'deskripsi' => $description,
                    'harga' => $priceInput,
                    'gambar' => $image,
                    'category_id' => $categoryInput,
                    'stok' => $stockInput,
                ];
            }
        } else {
            $error = 'Aksi menu tidak dikenal.';
        }
    }
}

try {
    $connection = database();
    $categories = $connection->query('SELECT id, nama_kategori FROM categories ORDER BY nama_kategori')->fetchAll();

    if ($editingProduct === null && $editId !== false && $editId > 0) {
        $editStatement = $connection->prepare('SELECT * FROM products WHERE id = :id');
        $editStatement->execute([':id' => $editId]);
        $editingProduct = $editStatement->fetch() ?: null;
    }
    $products = $connection->query(
        'SELECT p.id, p.nama_produk, p.deskripsi, p.harga, p.gambar, p.stok,
                c.nama_kategori
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         ORDER BY p.nama_produk'
    )->fetchAll();
} catch (PDOException $exception) {
    error_log('Gagal membuka manajemen produk Panganin: ' . $exception->getMessage());
    $categories = [];
    $products = [];
    $error = 'Data menu tidak dapat dimuat. Pastikan database sudah diimpor.';
}
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Menu | Panganin</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-container">
      <a class="brand" href="../index.html"><span class="brand-name">Panganin</span></a>
      <nav class="site-nav" aria-label="Navigasi akun">
        <ul class="nav-list">
          <li class="nav-item"><a class="nav-link" href="../index.html#menu">Katalog</a></li>
          <li class="nav-item"><span class="nav-link"><?= escapeHtml($user['nama']) ?> (<?= escapeHtml($user['role']) ?>)</span></li>
          <li class="nav-item">
            <form action="../auth/logout.php" method="post">
              <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
              <button type="submit" class="btn-text-danger">Keluar</button>
            </form>
          </li>
        </ul>
      </nav>
    </div>
  </header>
  <main class="cart-section">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Admin / UMKM</span>
        <h1 class="section-title">Kelola Menu Kantin</h1>
        <p class="section-subtitle">Tambah, perbarui, atau hapus produk yang tampil di katalog Panganin.</p>
      </div>

      <?php if ($message !== ''): ?>
        <div class="alert-box" role="status"><?= escapeHtml($message) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="alert-box alert-danger" role="alert"><?= escapeHtml($error) ?></div>
      <?php endif; ?>

      <section class="checkout-box" style="margin-bottom: 32px;">
        <h2 class="box-title"><?= $editingProduct !== null ? 'Edit Menu' : 'Tambah Menu' ?></h2>
        <form class="checkout-form" method="post" action="index.php<?= $editingProduct !== null ? '?edit=' . (int) $editingProduct['id'] : '' ?>">
          <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= escapeHtml((string) ($editingProduct['id'] ?? '')) ?>">
          <div class="form-group">
            <label class="form-label" for="nama_produk">Nama produk</label>
            <input class="form-input" id="nama_produk" name="nama_produk" maxlength="150" value="<?= escapeHtml((string) ($editingProduct['nama_produk'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea class="form-input form-textarea" id="deskripsi" name="deskripsi" rows="3" maxlength="2000" required><?= escapeHtml((string) ($editingProduct['deskripsi'] ?? '')) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="harga">Harga (rupiah)</label>
            <input class="form-input" type="number" id="harga" name="harga" min="1" step="1" value="<?= escapeHtml((string) ($editingProduct['harga'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="gambar">URL gambar (HTTPS)</label>
            <input class="form-input" type="url" id="gambar" name="gambar" maxlength="2048" value="<?= escapeHtml((string) ($editingProduct['gambar'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="category_id">Kategori</label>
            <select class="form-input" id="category_id" name="category_id" required>
              <option value="">Pilih kategori</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (string) ($editingProduct['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= escapeHtml($category['nama_kategori']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="stok">Stok</label>
            <input class="form-input" type="number" id="stok" name="stok" min="0" max="999999" step="1" value="<?= escapeHtml((string) ($editingProduct['stok'] ?? '0')) ?>" required>
          </div>
          <button type="submit" class="btn btn-primary"><?= $editingProduct !== null ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
          <?php if ($editingProduct !== null): ?>
            <a class="btn btn-secondary btn-sm" href="index.php">Batal</a>
          <?php endif; ?>
        </form>
      </section>

      <section>
        <h2 class="section-title">Produk Terdaftar</h2>
        <div class="order-history-list">
          <?php foreach ($products as $product): ?>
            <article class="order-history-card">
              <div class="order-card-header">
                <div>
                  <h3 class="order-number"><?= escapeHtml($product['nama_produk']) ?></h3>
                  <p class="order-timestamp"><?= escapeHtml($product['nama_kategori']) ?> · Stok <?= (int) $product['stok'] ?></p>
                </div>
                <span class="total-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></span>
              </div>
              <p><?= escapeHtml($product['deskripsi']) ?></p>
              <div class="order-history-actions">
                <a class="btn btn-secondary btn-sm" href="index.php?edit=<?= (int) $product['id'] ?>">Edit</a>
                <form method="post" action="index.php" onsubmit="return confirm('Hapus menu ini?')">
                  <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                  <button type="submit" class="btn-text-danger">Hapus</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
          <?php if (count($products) === 0): ?>
            <p>Belum ada produk di database.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</body>
</html>
