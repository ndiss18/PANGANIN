<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Panganin</title>
  <link rel="stylesheet" href="<?= e(assetUrl('style.css')) ?>">
</head>
<body>
  <?php require APP_ROOT . '/app/Views/layouts/management-header.php'; ?>
  <main class="cart-section">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Admin / UMKM</span>
        <h1 class="section-title">Dashboard Panganin</h1>
        <p class="section-subtitle">Ringkasan data yang tersimpan pada database kantin.</p>
      </div>
      <?php if (!empty($error)): ?>
        <div class="alert-box alert-danger" role="alert"><?= e($error) ?></div>
      <?php elseif (is_array($counts)): ?>
        <div class="order-history-list">
          <article class="order-history-card"><h2 class="box-title">Produk</h2><p class="total-price"><?= (int) $counts['products'] ?></p></article>
          <article class="order-history-card"><h2 class="box-title">Kategori</h2><p class="total-price"><?= (int) $counts['categories'] ?></p></article>
          <article class="order-history-card"><h2 class="box-title">Pesanan tersimpan</h2><p class="total-price"><?= (int) $counts['orders'] ?></p></article>
        </div>
      <?php endif; ?>
      <a class="btn btn-primary" href="<?= e(appUrl('products/manage')) ?>">Kelola Menu</a>
      <a class="btn btn-secondary" href="<?= e(appUrl()) ?>">Lihat Katalog</a>
    </div>
  </main>
</body>
</html>
