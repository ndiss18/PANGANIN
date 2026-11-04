<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Menu | Panganin</title>
  <link rel="stylesheet" href="<?= e(assetUrl('style.css')) ?>">
</head>
<body>
  <?php require APP_ROOT . '/app/Views/layouts/management-header.php'; ?>
  <main class="cart-section">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Admin / UMKM</span>
        <h1 class="section-title">Kelola Menu Kantin</h1>
        <p class="section-subtitle">Tambah, perbarui, atau hapus produk yang tampil di katalog Panganin.</p>
      </div>

      <?php if ($message !== ''): ?>
        <div class="alert-box" role="status"><?= e($message) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="alert-box alert-danger" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <section class="checkout-box" style="margin-bottom: 32px;">
        <h2 class="box-title"><?= $editingProduct !== null ? 'Edit Menu' : 'Tambah Menu' ?></h2>
        <form class="checkout-form" method="post" action="<?= e(appUrl('products/manage')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e((string) ($editingProduct['id'] ?? '')) ?>">
          <div class="form-group">
            <label class="form-label" for="nama_produk">Nama produk</label>
            <input class="form-input" id="nama_produk" name="nama_produk" maxlength="150" value="<?= e((string) ($editingProduct['nama_produk'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea class="form-input form-textarea" id="deskripsi" name="deskripsi" rows="3" maxlength="2000" required><?= e((string) ($editingProduct['deskripsi'] ?? '')) ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="harga">Harga (rupiah)</label>
            <input class="form-input" type="number" id="harga" name="harga" min="1" step="1" value="<?= e((string) ($editingProduct['harga'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="gambar">URL gambar (HTTPS)</label>
            <input class="form-input" type="url" id="gambar" name="gambar" maxlength="2048" value="<?= e((string) ($editingProduct['gambar'] ?? '')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="category_id">Kategori</label>
            <select class="form-input" id="category_id" name="category_id" required>
              <option value="">Pilih kategori</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (string) ($editingProduct['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['nama_kategori']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="stok">Stok</label>
            <input class="form-input" type="number" id="stok" name="stok" min="0" max="999999" step="1" value="<?= e((string) ($editingProduct['stok'] ?? '0')) ?>" required>
          </div>
          <button type="submit" class="btn btn-primary"><?= $editingProduct !== null ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
          <?php if ($editingProduct !== null): ?>
            <a class="btn btn-secondary btn-sm" href="<?= e(appUrl('products/manage')) ?>">Batal</a>
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
                  <h3 class="order-number"><?= e($product['nama_produk']) ?></h3>
                  <p class="order-timestamp"><?= e($product['nama_kategori']) ?> · Stok <?= (int) $product['stok'] ?></p>
                </div>
                <span class="total-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></span>
              </div>
              <p><?= e($product['deskripsi']) ?></p>
              <div class="order-history-actions">
                <a class="btn btn-secondary btn-sm" href="<?= e(appUrl('products/manage')) ?>&amp;edit=<?= (int) $product['id'] ?>">Edit</a>
                <form method="post" action="<?= e(appUrl('products/manage')) ?>" onsubmit="return confirm('Hapus menu ini?')">
                  <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
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
