<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
requireApiMethod('GET');

$user = currentUser();
if ($user !== null) {
    $user = [
        'id' => (int) $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
}

jsonResponse([
    'user' => $user,
    'csrf_token' => csrfToken(),
]);
