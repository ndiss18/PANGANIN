<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($product) && $product ? e($product['nama_produk']) : 'Menu tidak ditemukan' ?> | Panganin</title>
  <link rel="stylesheet" href="<?= e(assetUrl('style.css')) ?>">
</head>
<body>
  <main class="cart-section">
    <div class="container">
      <section class="checkout-box" style="max-width: 760px; margin: 48px auto;">
        <?php if (!empty($product)): ?>
          <img src="<?= e($product['gambar']) ?>" alt="<?= e($product['nama_produk']) ?>" style="width: 100%; max-height: 360px; object-fit: cover; border-radius: 16px;">
          <p class="section-tag"><?= e($product['nama_kategori']) ?></p>
          <h1 class="box-title"><?= e($product['nama_produk']) ?></h1>
          <p><?= e($product['deskripsi']) ?></p>
          <p class="total-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></p>
          <p class="form-hint">Stok tersedia: <?= (int) $product['stok'] ?></p>
        <?php else: ?>
          <h1 class="box-title">Menu tidak ditemukan</h1>
          <p><?= e($error ?? 'Menu yang diminta tidak tersedia.') ?></p>
        <?php endif; ?>
        <a class="btn btn-secondary btn-sm" href="<?= e(appUrl()) ?>">Kembali ke katalog</a>
      </section>
    </div>
  </main>
</body>
</html>
