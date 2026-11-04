<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

$error = '';
$name = '';
$email = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $nameInput = $_POST['nama'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $name = is_string($nameInput) ? trim($nameInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
    } elseif (strlen($name) > 480 || preg_match('/\A[\p{L}\p{M}\p{N} .\'-]{1,120}\z/u', $name) !== 1) {
        $error = 'Nama wajib diisi dan hanya boleh berisi huruf, angka, spasi, titik, apostrof, atau tanda hubung (maksimal 120 karakter).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $error = 'Masukkan alamat email yang valid.';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'Kata sandi harus terdiri dari minimal 8 karakter dan maksimal 72 byte.';
    } else {
        try {
            $statement = database()->prepare(
                'INSERT INTO users (nama, email, password, role)
                 VALUES (:nama, :email, :password, :role)'
            );
            $statement->execute([
                ':nama' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'customer',
            ]);
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) database()->lastInsertId(),
                'nama' => $name,
                'email' => $email,
                'role' => 'customer',
            ];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: ../index.html');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $error = 'Email tersebut sudah terdaftar. Silakan masuk atau gunakan email lain.';
            } else {
                error_log('Gagal memproses registrasi Panganin: ' . $exception->getMessage());
                $error = 'Registrasi tidak dapat diproses. Pastikan database sudah diimpor.';
            }
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
  <title>Daftar | Panganin</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <main class="cart-section">
    <div class="container">
      <section class="checkout-box" style="max-width: 560px; margin: 48px auto;">
        <h1 class="box-title">Daftar Pelanggan</h1>
        <p class="form-hint">Buat akun untuk memesan menu kantin. Akun baru memiliki role pelanggan.</p>
        <?php if ($error !== ''): ?>
          <div class="alert-box alert-danger" role="alert"><?= escapeHtml($error) ?></div>
        <?php endif; ?>
        <form class="checkout-form" method="post" action="register.php">
          <input type="hidden" name="csrf_token" value="<?= escapeHtml($token) ?>">
          <div class="form-group">
            <label class="form-label" for="nama">Nama</label>
            <input class="form-input" type="text" id="nama" name="nama" value="<?= escapeHtml($name) ?>" maxlength="120" autocomplete="name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" value="<?= escapeHtml($email) ?>" maxlength="254" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Kata sandi (minimal 8 karakter, maksimal 72 byte)</label>
            <input class="form-input" type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Daftar</button>
        </form>
        <p class="form-hint">Sudah punya akun? <a href="login.php">Masuk</a>.</p>
        <a class="btn btn-secondary btn-sm" href="../index.html">Kembali ke katalog</a>
      </section>
    </div>
  </main>
</body>
</html>
