<?php

declare(strict_types=1);

$pdo = null;

function db_connect() {
    global $pdo;
    if (!$pdo) {
        $db_path = __DIR__ . '/../../database.sqlite';
        require_once __DIR__ . '/../../sqlite.php';
        $pdo = get_sqlite_connection($db_path);
        create_schema($pdo);
        // Seed if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM mesas");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO mesas (numero, capacidade) VALUES (1, 4), (2, 4), (3, 6)");
            $pdo->exec("INSERT INTO clientes (nome, telefone) VALUES ('Consumidor Final', '000000000')");
            $pdo->exec("INSERT INTO funcionarios (nome, cargo) VALUES ('Atendente 1', 'Caixa')");
        }
    }
    return $pdo;
}

// PEDIDOS
function get_pedidos() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT p.*, m.numero as mesa, c.nome as cliente 
                         FROM pedidos p 
                         LEFT JOIN mesas m ON p.id_mesa = m.id_mesa 
                         LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                         ORDER BY p.id_pedido DESC");
    return $stmt->fetchAll();
}

function create_pedido($id_mesa = 1, $id_cliente = 1, $id_funcionario = 1, $status = 'aberto', $forma = 'DINHEIRO') {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_mesa, id_cliente, id_funcionario, status, forma_de_pagamento) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_mesa, $id_cliente, $id_funcionario, $status, $forma]);
}

function update_pedido_status($id_pedido, $status) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id_pedido = ?");
    $stmt->execute([$status, $id_pedido]);
}

function delete_pedido($id_pedido) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
    $stmt->execute([$id_pedido]);
}

// COMPRAS (Despesas)
function get_compras() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM despesas ORDER BY id_despesa DESC");
    return $stmt->fetchAll();
}

function create_compra($descricao, $categoria, $valor, $data) {
    if (empty($data)) $data = date('Y-m-d H:i:s');
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO despesas (descricao, categoria, valor, data_despesa) VALUES (?, ?, ?, ?)");
    $stmt->execute([$descricao, $categoria, $valor, $data]);
}

function delete_compra($id_despesa) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM despesas WHERE id_despesa = ?");
    $stmt->execute([$id_despesa]);
}

function update_compra($id, $descricao, $categoria, $valor, $data) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE despesas SET descricao=?, categoria=?, valor=?, data_despesa=? WHERE id_despesa=?");
    $stmt->execute([$descricao, $categoria, $valor, $data, $id]);
}

// CLIENTES
function get_clientes() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC");
    return $stmt->fetchAll();
}

function create_cliente($nome, $telefone, $email) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, email) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $telefone, $email]);
}

function delete_cliente($id_cliente) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
}

// MESAS
function get_mesas() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM mesas ORDER BY numero ASC");
    return $stmt->fetchAll();
}

function create_mesa($numero, $capacidade) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO mesas (numero, capacidade) VALUES (?, ?)");
    $stmt->execute([$numero, $capacidade]);
}

function delete_mesa($id_mesa) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM mesas WHERE id_mesa = ?");
    $stmt->execute([$id_mesa]);
}

// PRODUTOS (Cardápio)
function get_produtos() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome FROM produtos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria ORDER BY p.nome ASC");
    return $stmt->fetchAll();
}

function get_categorias() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    return $stmt->fetchAll();
}

function create_produto($nome, $descricao, $preco, $id_categoria, $estoque) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, id_categoria, estoque) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $descricao, $preco, $id_categoria, $estoque]);
}

function delete_produto($id_produto) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
}

function create_categoria($nome) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
    $stmt->execute([$nome]);
}

// PEDIDO COM VERIFICAÇÕES E ITENS
function create_pedido_com_itens($id_mesa, $id_cliente, $id_funcionario, $produtos_quantidades, $forma_pagamento) {
    $pdo = db_connect();
    
    // Verificações
    $stmtMesa = $pdo->prepare("SELECT id_mesa, status FROM mesas WHERE id_mesa = ?");
    $stmtMesa->execute([$id_mesa]);
    $mesa = $stmtMesa->fetch();
    if (!$mesa) {
        throw new Exception("Mesa não encontrada.");
    }
    
    $stmtCliente = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ?");
    $stmtCliente->execute([$id_cliente]);
    if (!$stmtCliente->fetch()) {
        throw new Exception("Cliente não encontrado.");
    }

    try {
        $pdo->beginTransaction();

        // Criar Pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (id_mesa, id_cliente, id_funcionario, status, forma_de_pagamento) VALUES (?, ?, ?, 'aberto', ?)");
        $stmt->execute([$id_mesa, $id_cliente, $id_funcionario, $forma_pagamento]);
        $id_pedido = (int)$pdo->lastInsertId();

        // Adicionar Itens
        if (!empty($produtos_quantidades)) {
            $stmtItem = $pdo->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmtProd = $pdo->prepare("SELECT preco FROM produtos WHERE id_produto = ?");
            
            foreach ($produtos_quantidades as $id_produto => $quantidade) {
                if ($quantidade > 0) {
                    $stmtProd->execute([$id_produto]);
                    $produto = $stmtProd->fetch();
                    if ($produto) {
                        $stmtItem->execute([$id_pedido, $id_produto, $quantidade, $produto['preco']]);
                    }
                }
            }
        }

        // Atualizar status da mesa para ocupada
        $stmtUpdateMesa = $pdo->prepare("UPDATE mesas SET status = 'ocupada' WHERE id_mesa = ?");
        $stmtUpdateMesa->execute([$id_mesa]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
