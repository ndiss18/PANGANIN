<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/database.php';
require_once APP_ROOT . '/core/helpers.php';
require_once APP_ROOT . '/core/Session.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Router.php';
Session::start();

spl_autoload_register(static function (string $class): void {
    $paths = [
        APP_ROOT . '/app/Models/' . $class . '.php',
        APP_ROOT . '/app/Controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});
