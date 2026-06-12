<?php

declare(strict_types=1);

require_once __DIR__ . '/../sqlite.php';
require_once __DIR__ . '/bd/models.php';
require_once __DIR__ . '/bd/auth.php';

// Initialize DB schema & seed
db_connect();
auth_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

// Login / Logout (antes da checagem de sessao)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (attempt_login(trim($_POST['usuario'] ?? ''), $_POST['senha'] ?? '')) {
        header('Location: /');
    } else {
        header('Location: /login?erro=1');
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    logout();
    header('Location: /login');
    exit;
}

// Exige login para qualquer rota exceto /login
if (!current_user()) {
    if ($path !== '/login') {
        header('Location: /login');
        exit;
    }
    require __DIR__ . '/templates/login.php';
    return;
}
if ($path === '/login') {
    header('Location: /');
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Acoes restritas ao admin (gerente): administracao, mesas e cardapio.
    // Servidores apenas criam/gerenciam pedidos, clientes e compras.
    $admin_actions = [
        'create_servidor', 'set_servidor_ativo', 'delete_servidor',
        'create_fornecedor', 'delete_fornecedor',
        'create_mesa', 'delete_mesa',
        'create_produto', 'delete_produto', 'create_categoria',
        'create_compra', 'update_compra', 'delete_compra',
    ];
    if (in_array($action, $admin_actions, true) && !is_admin()) {
        http_response_code(403);
        require __DIR__ . '/templates/403.php';
        exit;
    }

    // Servidor (usuarios) Actions - somente admin
    if ($action === 'create_servidor') {
        try {
            create_servidor($_POST['nome'], trim($_POST['usuario']), $_POST['senha']);
        } catch (Exception $e) {
            // Usuario duplicado: volta para a listagem com aviso
            header('Location: /servidor?erro=duplicado');
            exit;
        }
        header('Location: /servidor');
        exit;
    }
    if ($action === 'set_servidor_ativo') {
        set_servidor_ativo($_POST['id_usuario'], (int)$_POST['ativo'] === 1);
        header('Location: /servidor');
        exit;
    }
    if ($action === 'delete_servidor') {
        delete_servidor($_POST['id_usuario']);
        header('Location: /servidor');
        exit;
    }

    // Fornecedor Actions - somente admin
    if ($action === 'create_fornecedor') {
        create_fornecedor($_POST['nome'], $_POST['cnpj'] ?? '', $_POST['telefone'] ?? '', $_POST['email'] ?? '');
        header('Location: /fornecedor');
        exit;
    }
    if ($action === 'delete_fornecedor') {
        delete_fornecedor($_POST['id_fornecedor']);
        header('Location: /fornecedor');
        exit;
    }

    // Pedido Actions
    if ($action === 'create_pedido') {
        $produtos_quantidades = $_POST['produtos'] ?? []; // Array associativo id_produto => quantidade
        try {
            // A mesa e definida automaticamente pelo cadastro do cliente
            create_pedido_com_itens(
                (int)($_POST['id_cliente'] ?? 1),
                (int)($_POST['id_funcionario'] ?? 1),
                $produtos_quantidades,
                $_POST['forma_de_pagamento'] ?? 'DINHEIRO'
            );
        } catch (Exception $e) {
            header('Location: /pedido?erro=' . urlencode($e->getMessage()));
            exit;
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
        create_cliente(
            $_POST['nome'],
            $_POST['telefone'] ?? '',
            $_POST['email'] ?? '',
            empty($_POST['id_mesa']) ? null : (int)$_POST['id_mesa']
        );
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
            $_POST['data_despesa'],
            empty($_POST['id_fornecedor']) ? null : (int)$_POST['id_fornecedor']
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
            $_POST['data_despesa'],
            empty($_POST['id_fornecedor']) ? null : (int)$_POST['id_fornecedor']
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

$routes = [
    '/' => __DIR__ . '/templates/home.php',
    '/pedido' => __DIR__ . '/templates/pedido.php',
    '/compra' => __DIR__ . '/templates/compra.php',
    '/cliente' => __DIR__ . '/templates/cliente.php',
    '/mesa' => __DIR__ . '/templates/mesa.php',
    '/cardapio' => __DIR__ . '/templates/cardapio.php',
    '/servidor' => __DIR__ . '/templates/servidor.php',
    '/fornecedor' => __DIR__ . '/templates/fornecedor.php',
    '/relatorio' => __DIR__ . '/templates/relatorio.php',
];

// Rotas restritas ao admin (gerente). O cardapio fica visivel aos
// servidores em modo leitura (o template esconde as acoes de gestao).
$admin_routes = ['/servidor', '/fornecedor', '/mesa', '/relatorio'];
if (in_array($path, $admin_routes, true) && !is_admin()) {
    http_response_code(403);
    require __DIR__ . '/templates/403.php';
    return;
}

if (isset($routes[$path])) {
    require $routes[$path];
    return;
}

http_response_code(404);
require __DIR__ . '/templates/404.php';
