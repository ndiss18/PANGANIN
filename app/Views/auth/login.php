<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk | Panganin</title>
  <link rel="stylesheet" href="<?= e(assetUrl('style.css')) ?>">
</head>
<body>
  <main class="cart-section">
    <div class="container">
      <section class="checkout-box" style="max-width: 560px; margin: 48px auto;">
        <h1 class="box-title">Masuk ke Panganin</h1>
        <p class="form-hint">Gunakan akun pelanggan, admin, atau UMKM.</p>
        <?php if ($error !== ''): ?>
          <div class="alert-box alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form class="checkout-form" method="post" action="<?= e(appUrl('login')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="254" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Kata sandi</label>
            <input class="form-input" type="password" id="password" name="password" autocomplete="current-password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
        <p class="form-hint">Belum punya akun? <a href="<?= e(appUrl('register')) ?>">Daftar sebagai pelanggan</a>.</p>
        <a class="btn btn-secondary btn-sm" href="<?= e(appUrl()) ?>">Kembali ke katalog</a>
      </section>
    </div>
  </main>
</body>
</html>
