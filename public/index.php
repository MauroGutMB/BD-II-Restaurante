<?php

declare(strict_types=1);

require_once __DIR__ . '/../sqlite.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$routes = [
    '/' => __DIR__ . '/templates/home.php',
    '/pedido' => __DIR__ . '/templates/pedido.php',
    '/compra' => __DIR__ . '/templates/compra.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    return;
}

http_response_code(404);
require __DIR__ . '/templates/404.php';
