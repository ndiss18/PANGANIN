<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $providedToken): bool
{
    return is_string($providedToken)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $providedToken);
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $status): void
{
    jsonResponse(['error' => $message], $status);
}

function readJsonInput(): array
{
    $rawInput = file_get_contents('php://input');
    try {
        $data = json_decode($rawInput === false ? '' : $rawInput, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        jsonError('Format data JSON tidak valid.', 400);
    }

    if (!is_array($data)) {
        jsonError('Data permintaan harus berupa objek JSON.', 400);
    }
    return $data;
}

function requireApiMethod(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        jsonError('Metode permintaan tidak diizinkan.', 405);
    }
}

function currentUser(): ?array
{
    $user = $_SESSION['user'] ?? null;
    return is_array($user) ? $user : null;
}

function requireAuthenticatedUser(): array
{
    $user = currentUser();
    if ($user === null) {
        jsonError('Silakan masuk terlebih dahulu.', 401);
    }
    return $user;
}

function requireRoles(array $roles): array
{
    $user = currentUser();
    if ($user === null) {
        header('Location: ../auth/login.php');
        exit;
    }
    if (!in_array($user['role'] ?? '', $roles, true)) {
        http_response_code(403);
        echo 'Akses ditolak. Halaman ini hanya untuk admin atau UMKM.';
        exit;
    }
    return $user;
}
