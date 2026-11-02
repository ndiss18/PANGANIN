<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo 'Metode permintaan tidak diizinkan.';
    exit;
}

$providedToken = $_POST['csrf_token'] ?? null;
if (!verifyCsrfToken(is_string($providedToken) ? $providedToken : null)) {
    http_response_code(419);
    echo 'Sesi keamanan tidak valid. Muat ulang halaman lalu coba kembali.';
    exit;
}

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 42000, '/');
header('Location: ../index.html');
exit;
