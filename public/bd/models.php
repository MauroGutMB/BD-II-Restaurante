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
        migrate_schema($pdo);
        create_triggers($pdo);
        // Seed if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM mesas");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO mesas (numero, capacidade) VALUES (1, 4), (2, 4), (3, 6)");
            $pdo->exec("INSERT INTO clientes (nome, telefone) VALUES ('Consumidor Final', '000000000')");
            $pdo->exec("INSERT INTO funcionarios (nome, cargo) VALUES ('Atendente 1', 'Caixa')");
        }
        // Garante que sempre exista um admin para o primeiro acesso
        seed_admin($pdo);
    }
    return $pdo;
}

// PEDIDOS
// Retorna apenas pedidos finalizados (extrato/historico)
function get_pedidos() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT p.*, m.numero as mesa, c.nome as cliente
                         FROM pedidos p
                         LEFT JOIN mesas m ON p.id_mesa = m.id_mesa
                         LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                         WHERE p.status = 'fechado'
                         ORDER BY p.id_pedido DESC");
    return $stmt->fetchAll();
}

// Itens de todos os pedidos (nota fiscal), agrupados por id_pedido
function get_itens_por_pedido() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT i.id_pedido, i.quantidade, i.preco_unitario,
                                (i.quantidade * i.preco_unitario) as subtotal,
                                pr.nome as produto
                         FROM itens_pedido i
                         JOIN produtos pr ON pr.id_produto = i.id_produto
                         ORDER BY i.id_pedido, i.id_item");
    $por_pedido = [];
    foreach ($stmt->fetchAll() as $item) {
        $por_pedido[$item['id_pedido']][] = $item;
    }
    return $por_pedido;
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
    $stmt = $pdo->query("SELECT d.*, f.nome as fornecedor_nome
                         FROM despesas d
                         LEFT JOIN fornecedores f ON d.id_fornecedor = f.id_fornecedor
                         ORDER BY d.id_despesa DESC");
    return $stmt->fetchAll();
}

function create_compra($descricao, $categoria, $valor, $data, $id_fornecedor = null) {
    if (empty($data)) $data = date('Y-m-d H:i:s');
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO despesas (descricao, categoria, valor, data_despesa, id_fornecedor) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$descricao, $categoria, $valor, $data, $id_fornecedor]);
}

function delete_compra($id_despesa) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM despesas WHERE id_despesa = ?");
    $stmt->execute([$id_despesa]);
}

function update_compra($id, $descricao, $categoria, $valor, $data, $id_fornecedor = null) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE despesas SET descricao=?, categoria=?, valor=?, data_despesa=?, id_fornecedor=? WHERE id_despesa=?");
    $stmt->execute([$descricao, $categoria, $valor, $data, $id_fornecedor, $id]);
}

// CLIENTES
function get_clientes() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT c.*, m.numero as mesa_numero
                         FROM clientes c
                         LEFT JOIN mesas m ON c.id_mesa = m.id_mesa
                         ORDER BY c.nome ASC");
    return $stmt->fetchAll();
}

function create_cliente($nome, $telefone, $email, $id_mesa = null) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, telefone, email, id_mesa) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $telefone, $email, $id_mesa]);
}

function delete_cliente($id_cliente) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
}

// MESAS
function get_mesas() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT m.*, u.nome as servidor_nome
                         FROM mesas m
                         LEFT JOIN usuarios u ON u.id_usuario = m.id_servidor
                         ORDER BY m.numero ASC");
    return $stmt->fetchAll();
}

function get_mesas_detalhes(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT m.*, u.nome as servidor_nome,
                                (SELECT COUNT(*) FROM clientes c WHERE c.id_mesa = m.id_mesa) as num_clientes,
                                (SELECT COUNT(*) FROM pedidos p WHERE p.id_mesa = m.id_mesa AND p.status = 'aberto') as pedidos_abertos
                         FROM mesas m
                         LEFT JOIN usuarios u ON u.id_usuario = m.id_servidor
                         ORDER BY m.numero ASC");
    return $stmt->fetchAll();
}

function get_mesa_by_id(int $id_mesa): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT m.*, u.nome as servidor_nome
                           FROM mesas m
                           LEFT JOIN usuarios u ON u.id_usuario = m.id_servidor
                           WHERE m.id_mesa = ?");
    $stmt->execute([$id_mesa]);
    return $stmt->fetch() ?: null;
}

function get_mesa_do_servidor(int $id_usuario): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM mesas WHERE id_servidor = ? LIMIT 1");
    $stmt->execute([$id_usuario]);
    return $stmt->fetch() ?: null;
}

function mesa_pode_gerenciar(int $id_mesa): bool {
    if (is_admin()) return true;
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT id_servidor FROM mesas WHERE id_mesa = ?");
    $stmt->execute([$id_mesa]);
    $m = $stmt->fetch();
    $u = current_user();
    return $m !== false && (int)$m['id_servidor'] === (int)($u['id_usuario'] ?? 0);
}

function assign_servidor_mesa(int $id_mesa, ?int $id_servidor): void {
    $pdo = db_connect();
    $pdo->prepare("UPDATE mesas SET id_servidor = ? WHERE id_mesa = ?")
        ->execute([$id_servidor ?: null, $id_mesa]);
}

// GESTAO DA CONTA DA MESA

function get_pedido_aberto_por_mesa(int $id_mesa): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_mesa = ? AND status = 'aberto' ORDER BY id_pedido DESC LIMIT 1");
    $stmt->execute([$id_mesa]);
    return $stmt->fetch() ?: null;
}

function get_itens_do_pedido(int $id_pedido): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT i.*, pr.nome as produto_nome
                           FROM itens_pedido i
                           JOIN produtos pr ON pr.id_produto = i.id_produto
                           WHERE i.id_pedido = ?
                           ORDER BY i.id_item ASC");
    $stmt->execute([$id_pedido]);
    return $stmt->fetchAll();
}

function get_clientes_da_mesa(int $id_mesa): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_mesa = ? ORDER BY nome ASC");
    $stmt->execute([$id_mesa]);
    return $stmt->fetchAll();
}

function get_clientes_sem_mesa(): array {
    $pdo = db_connect();
    return $pdo->query("SELECT * FROM clientes WHERE id_mesa IS NULL ORDER BY nome ASC")->fetchAll();
}

function criar_conta_mesa(int $id_mesa): int {
    $pdo = db_connect();
    $c = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_mesa = ? LIMIT 1");
    $c->execute([$id_mesa]);
    $cliente = $c->fetch();
    $pdo->prepare("INSERT INTO pedidos (id_mesa, id_cliente, id_funcionario, status, forma_de_pagamento) VALUES (?, ?, NULL, 'aberto', 'DINHEIRO')")
        ->execute([$id_mesa, $cliente ? (int)$cliente['id_cliente'] : null]);
    $pdo->prepare("UPDATE mesas SET status = 'ocupada' WHERE id_mesa = ? AND status != 'ocupada'")
        ->execute([$id_mesa]);
    return (int)$pdo->lastInsertId();
}

function add_item_conta(int $id_pedido, int $id_produto, int $quantidade): void {
    if ($quantidade <= 0) return;
    $pdo = db_connect();
    $stmtE = $pdo->prepare("SELECT id_item FROM itens_pedido WHERE id_pedido = ? AND id_produto = ?");
    $stmtE->execute([$id_pedido, $id_produto]);
    $existente = $stmtE->fetch();
    if ($existente) {
        // Trigger before_update cuida do estoque
        $pdo->prepare("UPDATE itens_pedido SET quantidade = quantidade + ? WHERE id_item = ?")
            ->execute([$quantidade, $existente['id_item']]);
    } else {
        $stmtP = $pdo->prepare("SELECT preco FROM produtos WHERE id_produto = ?");
        $stmtP->execute([$id_produto]);
        $prod = $stmtP->fetch();
        if (!$prod) throw new Exception("Produto nao encontrado.");
        // Trigger before_insert cuida do estoque
        $pdo->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)")
            ->execute([$id_pedido, $id_produto, $quantidade, $prod['preco']]);
    }
}

function remove_item_conta(int $id_item, int $id_pedido): void {
    // Trigger after_delete restaura estoque automaticamente
    $pdo = db_connect();
    $pdo->prepare("DELETE FROM itens_pedido WHERE id_item = ? AND id_pedido = ?")
        ->execute([$id_item, $id_pedido]);
}

function fechar_conta(int $id_pedido, string $forma_pagamento): void {
    $pdo = db_connect();
    $pdo->prepare("UPDATE pedidos SET status = 'fechado', forma_de_pagamento = ? WHERE id_pedido = ?")
        ->execute([$forma_pagamento, $id_pedido]);
}

function liberar_mesa(int $id_mesa): void {
    // Nao fecha a conta automaticamente: isso e responsabilidade do servidor via 'fechar_conta'.
    if (get_pedido_aberto_por_mesa($id_mesa)) {
        throw new Exception("Ha uma conta em aberto nesta mesa. Feche-a antes de liberar.");
    }
    $pdo = db_connect();
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE clientes SET id_mesa = NULL WHERE id_mesa = ?")
            ->execute([$id_mesa]);
        $pdo->prepare("UPDATE mesas SET status = 'livre', id_servidor = NULL WHERE id_mesa = ?")
            ->execute([$id_mesa]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function vincular_cliente_mesa(int $id_cliente, int $id_mesa): void {
    $pdo = db_connect();
    $pdo->prepare("UPDATE clientes SET id_mesa = ? WHERE id_cliente = ?")
        ->execute([$id_mesa, $id_cliente]);
    $pdo->prepare("UPDATE mesas SET status = 'ocupada' WHERE id_mesa = ? AND status = 'livre'")
        ->execute([$id_mesa]);
}

function desvincular_cliente_mesa(int $id_cliente): void {
    $pdo = db_connect();
    $pdo->prepare("UPDATE clientes SET id_mesa = NULL WHERE id_cliente = ?")
        ->execute([$id_cliente]);
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

// USUARIOS (login: admin e servidores)
function get_usuario_by_login($usuario) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND ativo = 1");
    $stmt->execute([$usuario]);
    return $stmt->fetch() ?: null;
}

function get_servidores() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT id_usuario, nome, usuario, perfil, ativo, data_cadastro
                         FROM usuarios WHERE perfil = 'servidor' ORDER BY nome ASC");
    return $stmt->fetchAll();
}

function create_servidor($nome, $usuario, $senha) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, usuario, senha, perfil) VALUES (?, ?, ?, 'servidor')");
    $stmt->execute([$nome, $usuario, password_hash($senha, PASSWORD_DEFAULT)]);
}

function set_servidor_ativo($id_usuario, $ativo) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id_usuario = ? AND perfil = 'servidor'");
    $stmt->execute([$ativo ? 1 : 0, $id_usuario]);
}

function delete_servidor($id_usuario) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ? AND perfil = 'servidor'");
    $stmt->execute([$id_usuario]);
}

// FORNECEDORES
function get_fornecedores() {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM fornecedores ORDER BY nome ASC");
    return $stmt->fetchAll();
}

function create_fornecedor($nome, $cnpj, $telefone, $email) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $cnpj, $telefone, $email]);
}

function delete_fornecedor($id_fornecedor) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM fornecedores WHERE id_fornecedor = ?");
    $stmt->execute([$id_fornecedor]);
}

// RELATORIOS (somente gerente)
// Periodos aceitos: '1m', '3m', '1y'. Qualquer outro valor vira 'todos os tempos'.
function relatorio_condicao_periodo($periodo, $coluna) {
    $mapa = ['1m' => '-1 month', '3m' => '-3 months', '1y' => '-1 year'];
    if (!isset($mapa[$periodo])) {
        return '1 = 1';
    }
    // date() em vez de datetime() para incluir colunas que guardam apenas a data
    return "$coluna >= date('now', '{$mapa[$periodo]}')";
}

function get_despesas_por_categoria($periodo) {
    $pdo = db_connect();
    $cond = relatorio_condicao_periodo($periodo, 'data_despesa');
    $stmt = $pdo->query("SELECT IFNULL(NULLIF(categoria, ''), 'Sem categoria') as rotulo,
                                SUM(valor) as total
                         FROM despesas
                         WHERE $cond
                         GROUP BY rotulo
                         ORDER BY total DESC");
    return $stmt->fetchAll();
}

// Ganhos = itens de pedidos fechados, agrupados pela categoria do produto
function get_ganhos_por_categoria($periodo) {
    $pdo = db_connect();
    $cond = relatorio_condicao_periodo($periodo, 'p.data_pedido');
    $stmt = $pdo->query("SELECT IFNULL(c.nome, 'Sem categoria') as rotulo,
                                SUM(i.quantidade * i.preco_unitario) as total
                         FROM itens_pedido i
                         JOIN pedidos p ON p.id_pedido = i.id_pedido AND p.status = 'fechado'
                         JOIN produtos pr ON pr.id_produto = i.id_produto
                         LEFT JOIN categorias c ON c.id_categoria = pr.id_categoria
                         WHERE $cond
                         GROUP BY rotulo
                         ORDER BY total DESC");
    return $stmt->fetchAll();
}

function get_total_despesas($periodo) {
    $pdo = db_connect();
    $cond = relatorio_condicao_periodo($periodo, 'data_despesa');
    $stmt = $pdo->query("SELECT IFNULL(SUM(valor), 0) FROM despesas WHERE $cond");
    return (float)$stmt->fetchColumn();
}

function get_total_ganhos($periodo) {
    $pdo = db_connect();
    $cond = relatorio_condicao_periodo($periodo, 'p.data_pedido');
    $stmt = $pdo->query("SELECT IFNULL(SUM(i.quantidade * i.preco_unitario), 0)
                         FROM itens_pedido i
                         JOIN pedidos p ON p.id_pedido = i.id_pedido AND p.status = 'fechado'
                         WHERE $cond");
    return (float)$stmt->fetchColumn();
}

// PEDIDO COM VERIFICAÇÕES E ITENS
// A mesa do pedido vem do cadastro do cliente (clientes.id_mesa).
function create_pedido_com_itens($id_cliente, $id_funcionario, $produtos_quantidades, $forma_pagamento) {
    $pdo = db_connect();

    // Verificações
    $stmtCliente = $pdo->prepare("SELECT id_cliente, nome, id_mesa FROM clientes WHERE id_cliente = ?");
    $stmtCliente->execute([$id_cliente]);
    $cliente = $stmtCliente->fetch();
    if (!$cliente) {
        throw new Exception("Cliente não encontrado.");
    }
    if (empty($cliente['id_mesa'])) {
        throw new Exception("O cliente '{$cliente['nome']}' não possui mesa vinculada.");
    }
    $id_mesa = (int)$cliente['id_mesa'];

    $stmtMesa = $pdo->prepare("SELECT id_mesa, status FROM mesas WHERE id_mesa = ?");
    $stmtMesa->execute([$id_mesa]);
    $mesa = $stmtMesa->fetch();
    if (!$mesa) {
        throw new Exception("Mesa não encontrada.");
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
