<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
$pedidos = get_pedidos();
$clientes = get_clientes();
$produtos = get_produtos();
$itens_por_pedido = get_itens_por_pedido();
$erro = isset($_GET['erro']) ? (string)$_GET['erro'] : '';
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .inline-form { display: inline-block; margin: 0; }
        .inline-form button { background: none; border: none; padding: 0; cursor: pointer; font: inherit; }
    </style>
</head>
<body>
    <?php $active = '/pedido'; require __DIR__ . '/partials/topbar.php'; ?>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Pedidos</p>
                <h1>Gerenciar pedidos</h1>
                <p class="lead">Acompanhe mesas, entregas e andamento com foco na operacao diaria.</p>
            </div>
            <div class="page-actions">
                <a href="#form-card" class="button">Novo pedido</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Mesa/Cliente</th>
                            <th>Nota fiscal</th>
                            <th>Forma de Pag.</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td>
                                <div class="cell-title">#<?= htmlspecialchars((string)$p['id_pedido']) ?></div>
                                <div class="cell-sub"><?= htmlspecialchars((string)$p['data_pedido']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($p['mesa'] ? 'Mesa ' . $p['mesa'] : ($p['cliente'] ?: 'N/A')) ?></td>
                            <td>
                                <?php $itens = $itens_por_pedido[$p['id_pedido']] ?? []; ?>
                                <?php if (empty($itens)): ?>
                                <span class="cell-sub">Sem itens</span>
                                <?php else: ?>
                                <?php $total = array_sum(array_column($itens, 'subtotal')); ?>
                                <details class="nota-fiscal">
                                    <summary>R$ <?= number_format((float)$total, 2, ',', '.') ?> &middot; <?= count($itens) ?> <?= count($itens) === 1 ? 'item' : 'itens' ?></summary>
                                    <div class="nota-fiscal-corpo">
                                        <div class="nota-fiscal-titulo">Nota fiscal &middot; Pedido #<?= $p['id_pedido'] ?></div>
                                        <ul>
                                            <?php foreach ($itens as $item): ?>
                                            <li>
                                                <span><?= (int)$item['quantidade'] ?>x <?= htmlspecialchars((string)$item['produto']) ?> <small>(R$ <?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?>)</small></span>
                                                <span>R$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="nota-fiscal-total">
                                            <span>Total</span>
                                            <span>R$ <?= number_format((float)$total, 2, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </details>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string)$p['forma_de_pagamento']) ?></td>
                            <td>
                                <?php
                                    $s = $p['status'];
                                    $badge = 'badge--warning';
                                    if ($s === 'fechado' || $s === 'Concluido') $badge = 'badge--success';
                                    if ($s === 'cancelado') $badge = '';
                                ?>
                                <span class="badge <?= $badge ?>"><?= htmlspecialchars((string)$s) ?></span>
                            </td>
                            <td>
                                <div class="button-row">
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="update_pedido_status">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
                                        <input type="hidden" name="status" value="fechado">
                                        <button class="text-link" type="submit" style="color:var(--brand)">Finalizar</button>
                                    </form>
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="delete_pedido">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
                                        <button class="text-link" type="submit" onclick="return confirm('Tem certeza?')" style="color:red">Apagar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Nenhum pedido encontrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2>Criar pedido rapido</h2>
                <?php if ($erro !== ''): ?>
                <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" value="create_pedido">

                    <label class="field">
                        <span>Cliente</span>
                        <select name="id_cliente" required>
                            <option value="">Selecione o cliente</option>
                            <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"<?= $c['id_mesa'] === null ? ' disabled' : '' ?>><?= htmlspecialchars((string)$c['nome']) ?><?= $c['mesa_numero'] !== null ? ' - Mesa ' . htmlspecialchars((string)$c['mesa_numero']) : ' (sem mesa)' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="helper">A mesa e definida automaticamente pelo cadastro do cliente.</span>
                    </label>

                    <div class="field">
                        <span>Itens do Pedido</span>
                        <div style="max-height: 200px; overflow-y: auto; background: var(--card); border: 1px solid var(--border); padding: 0.5rem; border-radius: var(--radius-sm);">
                            <?php foreach ($produtos as $prod): ?>
                            <label style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                                <span><?= htmlspecialchars((string)$prod['nome']) ?> <small>(R$ <?= number_format((float)$prod['preco'], 2, ',', '.') ?>)</small></span>
                                <input type="number" name="produtos[<?= $prod['id_produto'] ?>]" value="0" min="0" style="width: 60px; padding: 0.2rem;">
                            </label>
                            <?php endforeach; ?>
                            <?php if (empty($produtos)): ?>
                                <small>Nenhum produto cadastrado.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <label class="field">
                        <span>Forma Pagamento</span>
                        <select name="forma_de_pagamento">
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="PIX">Pix</option>
                            <option value="CARTAO">Cartao</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Status inicial</span>
                        <select name="status">
                            <option value="aberto">Aberto</option>
                            <option value="fechado">Fechado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </label>
                    <div class="form-actions">
                        <button class="button" type="submit">Salvar pedido</button>
                        <button class="button button-outline" type="reset">Limpar</button>
                    </div>
                </form>
            </aside>
        </section>
    </main>
</body>
</html>