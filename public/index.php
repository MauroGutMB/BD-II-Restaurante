<?php

declare(strict_types=1);

require_once __DIR__ . '/../sqlite.php';
require_once __DIR__ . '/bd/models.php';

// Initialize DB schema & seed
db_connect();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Pedido Actions
    if ($action === 'create_pedido') {
        $produtos_quantidades = $_POST['produtos'] ?? []; // Array associativo id_produto => quantidade
        try {
            create_pedido_com_itens(
                (int)($_POST['id_mesa'] ?? 1),
                (int)($_POST['id_cliente'] ?? 1),
                (int)($_POST['id_funcionario'] ?? 1),
                $produtos_quantidades,
                $_POST['forma_de_pagamento'] ?? 'DINHEIRO'
            );
        } catch (Exception $e) {
            // Em caso de erro (ex: mesa não encontrada), redireciona ou exibe erro
        }
        header('Location: /pedido');
        exit;
    }
    if ($action === 'update_pedido_status') {
        update_pedido_status($_POST['id_pedido'], $_POST['status']);
        header('Location: /pedido');
        exit;
    }
    if ($action === 'delete_pedido') {
        delete_pedido($_POST['id_pedido']);
        header('Location: /pedido');
        exit;
    }
    
    // Cliente Actions
    if ($action === 'create_cliente') {
        create_cliente($_POST['nome'], $_POST['telefone'] ?? '', $_POST['email'] ?? '');
        header('Location: /cliente');
        exit;
    }
    if ($action === 'delete_cliente') {
        delete_cliente($_POST['id_cliente']);
        header('Location: /cliente');
        exit;
    }

    // Mesa Actions
    if ($action === 'create_mesa') {
        create_mesa((int)$_POST['numero'], (int)$_POST['capacidade']);
        header('Location: /mesa');
        exit;
    }
    if ($action === 'delete_mesa') {
        delete_mesa($_POST['id_mesa']);
        header('Location: /mesa');
        exit;
    }

    // Produto Actions
    if ($action === 'create_produto') {
        create_produto(
            $_POST['nome'], 
            $_POST['descricao'] ?? '', 
            (float)($_POST['preco'] ?? 0), 
            empty($_POST['id_categoria']) ? null : (int)$_POST['id_categoria'], 
            (int)($_POST['estoque'] ?? 0)
        );
        header('Location: /cardapio');
        exit;
    }
    if ($action === 'delete_produto') {
        delete_produto($_POST['id_produto']);
        header('Location: /cardapio');
        exit;
    }
    if ($action === 'create_categoria') {
        create_categoria($_POST['nome']);
        header('Location: /cardapio');
        exit;
    }

    
    // Compra Actions
    if ($action === 'create_compra') {
        create_compra(
            $_POST['descricao'],
            $_POST['categoria'],
            $_POST['valor'],
            $_POST['data_despesa']
        );
        header('Location: /compra');
        exit;
    }
    if ($action === 'update_compra') {
        update_compra(
            $_POST['id_despesa'],
            $_POST['descricao'],
            $_POST['categoria'],
            $_POST['valor'],
            $_POST['data_despesa']
        );
        header('Location: /compra');
        exit;
    }
    if ($action === 'delete_compra') {
        delete_compra($_POST['id_despesa']);
        header('Location: /compra');
        exit;
    }
}

$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$routes = [
    '/' => __DIR__ . '/templates/home.php',
    '/pedido' => __DIR__ . '/templates/pedido.php',
    '/compra' => __DIR__ . '/templates/compra.php',
    '/cliente' => __DIR__ . '/templates/cliente.php',
    '/mesa' => __DIR__ . '/templates/mesa.php',
    '/cardapio' => __DIR__ . '/templates/cardapio.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    return;
}

http_response_code(404);
require __DIR__ . '/templates/404.php';
