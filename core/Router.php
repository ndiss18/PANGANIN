<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][self::normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $path = self::normalize($path);
        $handler = $this->routes[$method][$path] ?? null;
        if ($handler !== null) {
            $handler();
            return;
        }

        foreach ($this->routes as $allowedMethod => $routes) {
            if (isset($routes[$path])) {
                header('Allow: ' . $allowedMethod);
                http_response_code(405);
                echo 'Metode permintaan tidak diizinkan.';
                return;
            }
        }

        http_response_code(404);
        echo 'Halaman tidak ditemukan.';
    }

    private static function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
