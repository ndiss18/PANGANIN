<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function login(): void
    {
        $error = '';
        $email = '';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $emailInput = $_POST['email'] ?? '';
            $passwordInput = $_POST['password'] ?? '';
            $email = is_string($emailInput) ? trim($emailInput) : '';
            $password = is_string($passwordInput) ? $passwordInput : '';

            if (!Session::verifyCsrf(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
                $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                $error = 'Masukkan email dan kata sandi yang valid.';
            } else {
                try {
                    $user = (new User())->findByEmail($email);
                    if ($user === null || !password_verify($password, $user['password'])) {
                        $error = 'Email atau kata sandi tidak sesuai.';
                    } else {
                        Session::signIn($user);
                        $this->redirect('');
                    }
                } catch (PDOException $exception) {
                    error_log('Gagal memproses login Panganin: ' . $exception->getMessage());
                    $error = 'Login tidak dapat diproses. Pastikan database sudah diimpor.';
                }
            }
        }

        $this->view('auth/login', [
            'error' => $error,
            'email' => $email,
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function register(): void
    {
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

            if (!Session::verifyCsrf(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
                $error = 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
            } elseif (strlen($name) > 480 || preg_match('/\A[\p{L}\p{M}\p{N} .\'-]{1,120}\z/u', $name) !== 1) {
                $error = 'Nama wajib diisi dan maksimal 120 karakter.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
                $error = 'Masukkan alamat email yang valid.';
            } elseif (strlen($password) < 8 || strlen($password) > 72) {
                $error = 'Kata sandi harus terdiri dari minimal 8 karakter dan maksimal 72 byte.';
            } else {
                try {
                    $userId = (new User())->createCustomer($name, $email, password_hash($password, PASSWORD_DEFAULT));
                    Session::signIn([
                        'id' => $userId,
                        'nama' => $name,
                        'email' => $email,
                        'role' => 'customer',
                    ]);
                    $this->redirect('');
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

        $this->view('auth/register', [
            'error' => $error,
            'name' => $name,
            'email' => $email,
            'csrfToken' => Session::csrfToken(),
        ]);
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            echo 'Metode permintaan tidak diizinkan.';
            return;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
            http_response_code(419);
            echo 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
            return;
        }

        Session::signOut();
        $this->redirect('');
    }

    public function sessionInfo(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            header('Allow: GET');
            $this->json(['error' => 'Metode permintaan tidak diizinkan.'], 405);
            return;
        }

        $this->json([
            'user' => $this->currentUser(),
            'csrf_token' => Session::csrfToken(),
        ]);
    }
}
