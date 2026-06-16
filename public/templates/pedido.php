<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';

$pedidos        = get_pedidos();        // retorna apenas fechados
$itens_por_pedido = get_itens_por_pedido();
$pode_apagar    = is_admin();
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos</title>
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
                <h1>Extrato de pedidos</h1>
                <p class="lead">Historico de pedidos finalizados com nota fiscal de cada um.</p>
            </div>
        </section>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Mesa / Cliente</th>
                        <th>Nota fiscal</th>
                        <th>Pagamento</th>
                        <?php if ($pode_apagar): ?><th>Acoes</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td>
                            <div class="cell-title">#<?= htmlspecialchars((string)$p['id_pedido']) ?></div>
                            <div class="cell-sub"><?= htmlspecialchars((string)$p['data_pedido']) ?></div>
                        </td>
                        <td>
                            <div class="cell-title"><?= $p['mesa'] ? 'Mesa ' . htmlspecialchars((string)$p['mesa']) : 'S/mesa' ?></div>
                            <?php if ($p['cliente']): ?><div class="cell-sub"><?= htmlspecialchars((string)$p['cliente']) ?></div><?php endif; ?>
                        </td>
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
                        <td>
                            <span class="badge badge--success"><?= htmlspecialchars((string)$p['forma_de_pagamento']) ?></span>
                        </td>
                        <?php if ($pode_apagar): ?>
                        <td>
                            <form method="POST" action="/" class="inline-form">
                                <input type="hidden" name="action" value="delete_pedido">
                                <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
                                <button class="text-link" type="submit" onclick="return confirm('Apagar este pedido do historico?')" style="color:red">Apagar</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="<?= $pode_apagar ? 5 : 4 ?>" style="text-align:center;padding:3rem 0;" class="cell-sub">
                            Nenhum pedido finalizado ainda.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
