<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

$error = '';
$email = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Masukkan email dan kata sandi yang valid.';
    } else {
        try {
            $statement = database()->prepare(
                'SELECT id, nama, email, password, role FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute([':email' => $email]);
            $user = $statement->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Email atau kata sandi tidak sesuai.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'nama' => $user['nama'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: ../index.html');
                exit;
            }
        } catch (PDOException $exception) {
            error_log('Gagal memproses login Panganin: ' . $exception->getMessage());
            $error = 'Login tidak dapat diproses. Pastikan database sudah diimpor.';
        }
    }
}
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk | Panganin</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <main class="cart-section">
    <div class="container">
      <section class="checkout-box" style="max-width: 560px; margin: 48px auto;">
        <h1 class="box-title">Masuk ke Panganin</h1>
        <p class="form-hint">Gunakan akun pelanggan, admin, atau UMKM.</p>
        <?php if ($error !== ''): ?>
          <div class="alert-box alert-danger" role="alert"><?= escapeHtml($error) ?></div>
        <?php endif; ?>
        <form class="checkout-form" method="post" action="login.php">
          <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" value="<?= escapeHtml($email) ?>" maxlength="254" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Kata sandi</label>
            <input class="form-input" type="password" id="password" name="password" autocomplete="current-password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
        <p class="form-hint">Belum punya akun? <a href="register.php">Daftar sebagai pelanggan</a>.</p>
        <a class="btn btn-secondary btn-sm" href="../index.html">Kembali ke katalog</a>
      </section>
    </div>
  </main>
</body>
</html>
