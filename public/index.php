<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/config/bootstrap.php';

try {
    $router = require APP_ROOT . '/routes/web.php';
    $route = $_GET['route'] ?? '';
    if (!is_string($route)) {
        $route = '';
    }
    if ($route === '') {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        if (is_string($requestPath) && $scriptPath !== '/' && str_starts_with($requestPath, $scriptPath)) {
            $requestPath = substr($requestPath, strlen($scriptPath));
        }
        $requestPath = preg_replace('/\/index\.(?:php|html)$/', '', (string) $requestPath);
        $route = trim($requestPath, '/');
    }
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $route);
} catch (PDOException $exception) {
    error_log('Kesalahan database Panganin: ' . $exception->getMessage());
    http_response_code(500);
    if (str_starts_with((string) ($_GET['route'] ?? ''), 'api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Permintaan tidak dapat diproses karena masalah database.']);
    } else {
        echo 'Permintaan tidak dapat diproses. Pastikan database panganin_db sudah diimpor.';
    }
}
