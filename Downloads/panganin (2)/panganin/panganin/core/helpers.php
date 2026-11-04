<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function appUrl(string $route = ''): string
{
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $segments = array_values(array_filter(explode('/', trim($scriptDirectory, '/')), 'strlen'));
    if ($segments !== [] && in_array(end($segments), ['api', 'auth', 'products', 'public'], true)) {
        array_pop($segments);
    }
    $basePath = $segments === [] ? '/' : '/' . implode('/', $segments) . '/';
    return $basePath . 'index.php?route=' . rawurlencode($route);
}

function assetUrl(string $path): string
{
    $home = appUrl();
    $basePath = substr($home, 0, (int) strpos($home, 'index.php'));
    return $basePath . ltrim($path, '/');
}
