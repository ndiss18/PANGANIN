<?php
declare(strict_types=1);

$router = new Router();
$home = new HomeController();
$auth = new AuthController();
$products = new ProductController();
$categories = new CategoryController();
$orders = new OrderController();
$dashboard = new DashboardController();

$router->get('/', [$home, 'index']);
$router->get('/home', [$home, 'index']);
$router->get('/login', [$auth, 'login']);
$router->post('/login', [$auth, 'login']);
$router->get('/register', [$auth, 'register']);
$router->post('/register', [$auth, 'register']);
$router->post('/logout', [$auth, 'logout']);
$router->get('/api/session', [$auth, 'sessionInfo']);
$router->get('/api/products', [$products, 'index']);
$router->get('/api/categories', [$categories, 'index']);
$router->post('/api/categories', [$categories, 'store']);
$router->get('/products/show', [$products, 'show']);
$router->get('/products/manage', [$products, 'manage']);
$router->post('/products/manage', [$products, 'manage']);
$router->get('/dashboard', [$dashboard, 'index']);
$router->get('/api/orders', [$orders, 'history']);
$router->post('/api/orders', [$orders, 'store']);

return $router;
