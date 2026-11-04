<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar | Panganin</title>
  <link rel="stylesheet" href="<?= e(assetUrl('style.css')) ?>">
</head>
<body>
  <main class="cart-section">
    <div class="container">
      <section class="checkout-box" style="max-width: 560px; margin: 48px auto;">
        <h1 class="box-title">Daftar Pelanggan</h1>
        <p class="form-hint">Buat akun untuk memesan menu kantin. Akun baru memiliki role pelanggan.</p>
        <?php if ($error !== ''): ?>
          <div class="alert-box alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form class="checkout-form" method="post" action="<?= e(appUrl('register')) ?>">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <div class="form-group">
            <label class="form-label" for="nama">Nama</label>
            <input class="form-input" type="text" id="nama" name="nama" value="<?= e($name) ?>" maxlength="120" autocomplete="name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="254" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Kata sandi (minimal 8 karakter, maksimal 72 byte)</label>
            <input class="form-input" type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>
        <p class="form-hint">Sudah punya akun? <a href="<?= e(appUrl('login')) ?>">Masuk</a>.</p>
        <a class="btn btn-secondary btn-sm" href="<?= e(appUrl()) ?>">Kembali ke katalog</a>
      </section>
    </div>
  </main>
</body>
</html>
