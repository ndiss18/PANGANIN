<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $name, array $data = []): void
    {
        $file = APP_ROOT . '/app/Views/' . $name . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View tidak ditemukan: ' . $name);
        }

        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    protected function redirect(string $route): void
    {
        header('Location: ' . appUrl($route));
        exit;
    }

    protected function currentUser(): ?array
    {
        return Session::user();
    }

    protected function requireRole(array $roles, bool $jsonResponse = false): ?array
    {
        $user = $this->currentUser();
        if ($user === null) {
            if ($jsonResponse) {
                $this->json(['error' => 'Silakan masuk terlebih dahulu.'], 401);
            } else {
                $this->redirect('login');
            }
            return null;
        }

        if (!in_array($user['role'] ?? '', $roles, true)) {
            if ($jsonResponse) {
                $this->json(['error' => 'Akses ditolak.'], 403);
            } else {
                http_response_code(403);
                echo 'Akses ditolak. Halaman ini tidak tersedia untuk role akun Anda.';
            }
            return null;
        }

        return $user;
    }
}
