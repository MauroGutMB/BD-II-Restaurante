<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';

$id_mesa  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$erro     = htmlspecialchars((string)($_GET['erro'] ?? ''));

// Vista de detalhe de uma mesa especifica
if ($id_mesa > 0) {
    $mesa = get_mesa_by_id($id_mesa);

    if (!$mesa) {
        http_response_code(404);
        echo '<p style="text-align:center;padding:4rem;">Mesa nao encontrada.</p>';
        exit;
    }

    $mesa_livre = empty($mesa['id_servidor']); // sem servidor atribuido
    $pode       = mesa_pode_gerenciar($id_mesa);

    // Mesa de outro servidor: acesso negado
    if (!$pode && !$mesa_livre) {
        http_response_code(403);
        require __DIR__ . '/403.php';
        exit;
    }

    // Verifica se servidor ja tem outra mesa (limita a 1 por servidor)
    $ja_tem_mesa = !is_admin() && !empty(get_mesa_do_servidor((int)(current_user()['id_usuario'] ?? 0)));

    $clientes    = $pode ? get_clientes_da_mesa($id_mesa) : [];
    $disponiveis = $pode ? get_clientes_sem_mesa() : [];
    $pedido      = $pode ? get_pedido_aberto_por_mesa($id_mesa) : null;
    $itens       = $pedido ? get_itens_do_pedido((int)$pedido['id_pedido']) : [];
    $total       = (float)array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco_unitario'], $itens));
    $produtos    = $pode ? get_produtos() : [];
    $servidores  = is_admin() ? get_servidores() : [];
    // Categorias únicas dos produtos para o picker
    $cat_unicas  = array_values(array_unique(array_filter(array_column($produtos, 'categoria_nome'))));

    $badge_s = match($mesa['status']) { 'ocupada' => 'badge--warning', 'livre' => 'badge--success', default => '' };
}
// Vista de visao geral (lista de mesas)
else {
    $mesas_todas = get_mesas_detalhes();
    $user_id     = (int)(current_user()['id_usuario'] ?? 0);
    // Verifica se servidor ja tem mesa atribuida
    $minha_mesa_id = !is_admin() ? (int)(get_mesa_do_servidor($user_id)['id_mesa'] ?? 0) : 0;
}
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $id_mesa > 0 ? 'Mesa ' . htmlspecialchars((string)($mesa['numero'] ?? '')) : 'Mesas' ?></title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .inline-form { display: inline-block; margin: 0; }
        .inline-form button { background: none; border: none; padding: 0; cursor: pointer; color: inherit; font: inherit; }
    </style>
</head>
<body>
<?php $active = '/gerenciar-mesa'; require __DIR__ . '/partials/topbar.php'; ?>
<main class="container">

<?php if ($id_mesa === 0): ?>
<!-- ============================
     VISAO GERAL DAS MESAS
     ============================ -->
<section class="page-header">
    <div>
        <p class="eyebrow">Mesas</p>
        <h1>Ocupacao das mesas</h1>
        <p class="lead">Veja o status de cada mesa e acesse o gerenciamento.</p>
    </div>
    <?php if (is_admin()): ?>
    <div class="page-actions">
        <a class="button" href="/mesa">Administrar mesas</a>
    </div>
    <?php endif; ?>
</section>

<?php if (empty($mesas_todas)): ?>
<p class="report-empty">Nenhuma mesa cadastrada. <?= is_admin() ? '<a href="/mesa">Criar mesas</a>' : 'Aguarde o gerente cadastrar as mesas.' ?></p>
<?php else: ?>

<?php if (!is_admin() && $minha_mesa_id === 0): ?>
<div class="alert-info">Voce ainda nao tem uma mesa. Pegue uma mesa livre abaixo para comecar a gerenciar.</div>
<?php endif; ?>

<div class="mesa-cards-grid">
<?php foreach ($mesas_todas as $m):
    $badge_card  = match($m['status']) { 'ocupada' => 'badge--warning', 'livre' => 'badge--success', default => '' };
    $pode_este   = is_admin() || (int)$m['id_servidor'] === $user_id;
    $minha       = !is_admin() && (int)$m['id_servidor'] === $user_id;
    $esta_livre  = empty($m['id_servidor']); // sem servidor
    $pode_pegar  = !is_admin() && $esta_livre && $minha_mesa_id === 0;
?>
<div class="mesa-card <?= $minha ? 'mesa-card--minha' : '' ?>">
    <div class="mesa-card-top">
        <span class="mesa-num">Mesa <?= htmlspecialchars((string)$m['numero']) ?></span>
        <span class="badge <?= $badge_card ?>"><?= ucfirst(htmlspecialchars((string)$m['status'])) ?></span>
    </div>
    <div class="mesa-card-info">
        <span><?= (int)$m['capacidade'] ?> lugares</span>
        <span><?= (int)$m['num_clientes'] ?> cliente<?= $m['num_clientes'] != 1 ? 's' : '' ?></span>
        <?php if ((int)$m['pedidos_abertos'] > 0): ?>
        <span class="badge badge--warning" style="font-size:0.75rem;">Conta aberta</span>
        <?php endif; ?>
    </div>
    <div class="mesa-card-servidor">
        <?php if ($m['servidor_nome']): ?>
        <span class="user-avatar" style="width:28px;height:28px;font-size:0.8rem;"><?= strtoupper(substr((string)$m['servidor_nome'], 0, 1)) ?></span>
        <span><?= htmlspecialchars((string)$m['servidor_nome']) ?><?= $minha ? ' (voce)' : '' ?></span>
        <?php else: ?>
        <span class="cell-sub">Livre</span>
        <?php endif; ?>
    </div>
    <div style="margin-top:0.75rem;">
    <?php if ($pode_este): ?>
        <a class="button<?= $minha ? '' : ' button-outline' ?>" href="/gerenciar-mesa?id=<?= $m['id_mesa'] ?>" style="width:100%;text-align:center;display:block;">Gerenciar</a>
    <?php elseif ($pode_pegar): ?>
        <form method="POST" action="/">
            <input type="hidden" name="action" value="tomar_mesa">
            <input type="hidden" name="id_mesa" value="<?= $m['id_mesa'] ?>">
            <button class="button button-outline" type="submit" style="width:100%;">Pegar mesa</button>
        </form>
    <?php elseif (!is_admin() && $esta_livre && $minha_mesa_id > 0): ?>
        <div style="text-align:center;font-size:0.82rem;color:var(--muted);padding:0.4rem 0;">Livre &mdash; voce ja tem uma mesa</div>
    <?php else: ?>
        <div style="text-align:center;font-size:0.82rem;color:var(--muted);padding:0.4rem 0;">Gerenciada por outro servidor</div>
    <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ============================
     DETALHE / GERENCIAMENTO DE MESA
     ============================ -->
<section class="page-header">
    <div>
        <p class="eyebrow">
            <?php if (is_admin()): ?><a href="/mesa" class="text-link">Mesas</a> /<?php endif; ?>
            <a href="/gerenciar-mesa" class="text-link">Visao geral</a>
        </p>
        <h1>
            Mesa <?= htmlspecialchars((string)$mesa['numero']) ?>
            <span class="badge <?= $badge_s ?>" style="font-size:0.9rem;vertical-align:middle;"><?= ucfirst(htmlspecialchars((string)$mesa['status'])) ?></span>
        </h1>
        <p class="lead">
            <?= (int)$mesa['capacidade'] ?> lugares
            &middot; Servidor: <?= $mesa['servidor_nome'] ? htmlspecialchars((string)$mesa['servidor_nome']) : '<em>nao atribuido</em>' ?>
        </p>
    </div>
    <div class="page-actions">
        <?php if ($pode): ?>
        <?php if ($pedido): ?>
        <span class="cell-sub" style="font-size:0.82rem;">Feche a conta antes de liberar a mesa.</span>
        <button class="button button-outline" disabled style="opacity:0.45;cursor:not-allowed;">Liberar mesa</button>
        <?php else: ?>
        <form method="POST" action="/" class="inline-form">
            <input type="hidden" name="action" value="liberar_mesa_action">
            <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
            <button class="button button-outline" type="submit" onclick="return confirm('Desvincular todos os clientes e liberar a mesa?')">Liberar mesa</button>
        </form>
        <?php endif; ?>
        <?php elseif ($mesa_livre && !$ja_tem_mesa): ?>
        <form method="POST" action="/" class="inline-form">
            <input type="hidden" name="action" value="tomar_mesa">
            <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
            <button class="button" type="submit">Pegar esta mesa</button>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php if ($erro !== ''): ?>
<div class="alert-error"><?= $erro ?></div>
<?php endif; ?>

<?php if (!$pode && $mesa_livre): ?>
<!-- Mesa livre: servidor pode pegar -->
<div style="text-align:center;padding:4rem 1rem;">
    <p style="font-size:1.1rem;color:var(--brand-dark);margin-bottom:1rem;">Esta mesa esta disponivel.</p>
    <?php if ($ja_tem_mesa): ?>
    <p class="cell-sub">Voce ja e responsavel por outra mesa. Libere-a primeiro para pegar esta.</p>
    <?php else: ?>
    <form method="POST" action="/">
        <input type="hidden" name="action" value="tomar_mesa">
        <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
        <button class="button" type="submit">Pegar esta mesa</button>
    </form>
    <?php endif; ?>
</div>
<?php else: ?>
<section class="mesa-detail-grid">

    <!-- COLUNA PRINCIPAL: CONTA -->
    <div class="stack-gap">

        <div class="table-card conta-card">
            <div class="conta-header">
                <h2>Conta da mesa <?= $pedido ? '#' . $pedido['id_pedido'] : '' ?></h2>
                <?php if (!$pedido): ?>
                <form method="POST" action="/">
                    <input type="hidden" name="action" value="criar_conta_mesa">
                    <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                    <button class="button" type="submit">Abrir conta</button>
                </form>
                <?php else: ?>
                <span class="badge badge--warning">Em aberto</span>
                <?php endif; ?>
            </div>

            <?php if ($pedido): ?>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="text-align:right;">Qtd</th>
                        <th style="text-align:right;">Unitario</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($itens)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem 0;" class="cell-sub">Nenhum item na conta. Adicione abaixo.</td></tr>
                    <?php else: ?>
                    <?php foreach ($itens as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$it['produto_nome']) ?></td>
                        <td style="text-align:right;"><?= (int)$it['quantidade'] ?></td>
                        <td style="text-align:right;">R$ <?= number_format((float)$it['preco_unitario'], 2, ',', '.') ?></td>
                        <td style="text-align:right;font-weight:600;">R$ <?= number_format((float)($it['quantidade'] * $it['preco_unitario']), 2, ',', '.') ?></td>
                        <td style="text-align:right;">
                            <form method="POST" action="/" class="inline-form">
                                <input type="hidden" name="action" value="remove_item_conta">
                                <input type="hidden" name="id_item" value="<?= $it['id_item'] ?>">
                                <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                                <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                                <button class="text-link" style="color:red;" type="submit" title="Remover item">×</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($itens)): ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;font-weight:600;border-top:1px solid var(--border);">Total</td>
                        <td style="text-align:right;font-weight:700;font-size:1.05rem;color:var(--brand-dark);border-top:1px solid var(--border);">R$ <?= number_format($total, 2, ',', '.') ?></td>
                        <td style="border-top:1px solid var(--border);"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>

            <!-- Adicionar item -->
            <form method="POST" action="/" class="add-item-form" id="form-add-item-<?= $id_mesa ?>" onsubmit="return validarAddItem_<?= $id_mesa ?>()">
                <input type="hidden" name="action" value="add_item_conta">
                <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                <input type="hidden" name="id_produto" id="hidden-id-produto-<?= $id_mesa ?>">

                <!-- Picker de produto: busca + chips de categoria + lista -->
                <div class="prod-picker">
                    <p style="margin:0 0 0.4rem;font-weight:600;font-size:0.88rem;">Adicionar item</p>
                    <div class="prod-picker-header">
                        <input
                            type="text"
                            id="picker-search-<?= $id_mesa ?>"
                            class="search-input"
                            placeholder="Buscar por nome ou categoria..."
                            autocomplete="off"
                        >
                        <div class="cat-chips" id="picker-cats-<?= $id_mesa ?>">
                            <button type="button" class="chip chip--active" data-cat="">Todos</button>
                            <?php foreach ($cat_unicas as $cu): ?>
                            <button type="button" class="chip" data-cat="<?= htmlspecialchars($cu) ?>"><?= htmlspecialchars($cu) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="prod-picker-list" id="picker-list-<?= $id_mesa ?>">
                        <?php if (empty($produtos)): ?>
                        <div class="prod-picker-vazio">Nenhum produto cadastrado.</div>
                        <?php else: ?>
                        <?php foreach ($produtos as $prod):
                            $sem = (int)$prod['estoque'] <= 0;
                            $cat = htmlspecialchars((string)($prod['categoria_nome'] ?? ''));
                        ?>
                        <div
                            class="prod-item<?= $sem ? ' prod-item--esgotado' : '' ?>"
                            data-id="<?= $prod['id_produto'] ?>"
                            data-nome="<?= htmlspecialchars(strtolower($prod['nome'])) ?>"
                            data-nome-orig="<?= htmlspecialchars((string)$prod['nome']) ?>"
                            data-cat="<?= $cat ?>"
                            data-preco="<?= number_format((float)$prod['preco'], 2, ',', '.') ?>"
                        >
                            <div class="prod-item-info">
                                <span class="prod-item-nome"><?= htmlspecialchars((string)$prod['nome']) ?></span>
                                <?php if ($cat !== ''): ?>
                                <span class="cat-badge"><?= $cat ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="prod-item-meta">
                                <span class="prod-item-preco">R$ <?= number_format((float)$prod['preco'], 2, ',', '.') ?></span>
                                <span class="prod-item-estoque<?= $sem ? ' text-danger' : '' ?>">
                                    <?= $sem ? 'Esgotado' : 'Est. ' . (int)$prod['estoque'] ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="prod-selecionado" id="prod-selecionado-<?= $id_mesa ?>" style="display:none;">
                        <span>Selecionado:</span>
                        <strong id="selecionado-nome-<?= $id_mesa ?>"></strong>
                        <button type="button" class="text-link" style="color:var(--muted);font-size:1.1rem;" title="Cancelar" onclick="limparSelecao_<?= $id_mesa ?>()">×</button>
                    </div>
                </div>

                <div class="add-item-form-footer">
                    <label class="field" style="width:80px;">
                        <span>Qtd</span>
                        <input type="number" name="quantidade" value="1" min="1" required>
                    </label>
                    <button class="button" type="submit">Adicionar</button>
                </div>
            </form>

            <script>
            (function () {
                const id      = <?= $id_mesa ?>;
                const search  = document.getElementById('picker-search-' + id);
                const chips   = document.querySelectorAll('#picker-cats-' + id + ' .chip');
                const items   = document.querySelectorAll('#picker-list-' + id + ' .prod-item');
                const hidden  = document.getElementById('hidden-id-produto-' + id);
                const selDiv  = document.getElementById('prod-selecionado-' + id);
                const selNome = document.getElementById('selecionado-nome-' + id);
                let catAtiva  = '';

                function filtrar() {
                    const q = (search.value || '').toLowerCase().trim();
                    items.forEach(item => {
                        const nome = item.dataset.nome || '';
                        const cat  = (item.dataset.cat || '').toLowerCase();
                        const matchCat = catAtiva === '' || cat === catAtiva.toLowerCase();
                        const matchQ   = q === '' || nome.includes(q) || cat.includes(q);
                        item.style.display = (matchCat && matchQ) ? '' : 'none';
                    });
                }

                chips.forEach(btn => {
                    btn.addEventListener('click', () => {
                        chips.forEach(b => b.classList.remove('chip--active'));
                        btn.classList.add('chip--active');
                        catAtiva = btn.dataset.cat;
                        filtrar();
                    });
                });

                search.addEventListener('input', filtrar);

                items.forEach(item => {
                    if (item.classList.contains('prod-item--esgotado')) return;
                    item.addEventListener('click', () => {
                        items.forEach(i => i.classList.remove('prod-item--selecionado'));
                        item.classList.add('prod-item--selecionado');
                        hidden.value = item.dataset.id;
                        selNome.textContent = item.dataset.nomeOrig + ' — R$ ' + item.dataset.preco;
                        selDiv.style.display = '';
                    });
                });

                window['limparSelecao_' + id] = function () {
                    hidden.value = '';
                    selNome.textContent = '';
                    selDiv.style.display = 'none';
                    items.forEach(i => i.classList.remove('prod-item--selecionado'));
                };

                window['validarAddItem_' + id] = function () {
                    if (!hidden.value) {
                        alert('Selecione um produto na lista.');
                        return false;
                    }
                    return true;
                };
            })();
            </script>

            <!-- Fechar conta -->
            <form method="POST" action="/" class="fechar-conta-form">
                <input type="hidden" name="action" value="fechar_conta_mesa">
                <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                <label class="field" style="flex:1;">
                    <span>Forma de pagamento</span>
                    <select name="forma_pagamento">
                        <option value="DINHEIRO">Dinheiro</option>
                        <option value="PIX">Pix</option>
                        <option value="CARTAO">Cartao</option>
                    </select>
                </label>
                <button class="button" type="submit" onclick="return confirm('Fechar a conta? Isso nao libera a mesa automaticamente.')">Fechar conta</button>
            </form>

            <?php else: ?>
            <p class="cell-sub" style="text-align:center;padding:3rem 0;">
                Nenhuma conta aberta. Clique em "Abrir conta" para iniciar um pedido.
            </p>
            <?php endif; ?>
        </div>

    </div><!-- /coluna principal -->

    <!-- SIDEBAR: clientes + atribuicao -->
    <aside class="stack-gap">

        <!-- Clientes na mesa -->
        <div class="form-card">
            <h2>Clientes na mesa</h2>
            <?php if (empty($clientes)): ?>
            <p class="cell-sub">Nenhum cliente vinculado.</p>
            <?php else: ?>
            <ul class="client-list">
                <?php foreach ($clientes as $c): ?>
                <li>
                    <div>
                        <div class="cell-title"><?= htmlspecialchars((string)$c['nome']) ?></div>
                        <?php if ($c['telefone']): ?><div class="cell-sub"><?= htmlspecialchars((string)$c['telefone']) ?></div><?php endif; ?>
                    </div>
                    <form method="POST" action="/" class="inline-form">
                        <input type="hidden" name="action" value="desvincular_cliente_mesa">
                        <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?>">
                        <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                        <button class="text-link" style="color:red;font-size:1.1rem;" title="Remover da mesa" type="submit">×</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (!empty($disponiveis)): ?>
            <form method="POST" action="/" class="stack" style="margin-top:1rem;">
                <input type="hidden" name="action" value="vincular_cliente_mesa">
                <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                <label class="field">
                    <span>Vincular cliente</span>
                    <select name="id_cliente" required>
                        <option value="">Selecione o cliente</option>
                        <?php foreach ($disponiveis as $d): ?>
                        <option value="<?= $d['id_cliente'] ?>"><?= htmlspecialchars((string)$d['nome']) ?><?= $d['telefone'] ? ' - ' . htmlspecialchars((string)$d['telefone']) : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="button" type="submit">Vincular</button>
            </form>
            <?php else: ?>
            <p class="cell-sub" style="margin-top:0.75rem;">Nenhum cliente disponivel para vincular.</p>
            <?php endif; ?>
        </div>

        <!-- Admin: atribuir servidor -->
        <?php if (is_admin()): ?>
        <div class="form-card">
            <h2>Servidor responsavel</h2>
            <form method="POST" action="/" class="stack">
                <input type="hidden" name="action" value="assign_servidor_mesa">
                <input type="hidden" name="id_mesa" value="<?= $id_mesa ?>">
                <label class="field">
                    <span>Servidor</span>
                    <select name="id_servidor">
                        <option value="">Nenhum</option>
                        <?php foreach ($servidores as $srv): ?>
                        <option value="<?= $srv['id_usuario'] ?>"<?= (int)($mesa['id_servidor'] ?? 0) === (int)$srv['id_usuario'] ? ' selected' : '' ?>><?= htmlspecialchars((string)$srv['nome']) ?> (@<?= htmlspecialchars((string)$srv['usuario']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="button" type="submit">Atribuir</button>
            </form>
        </div>
        <?php endif; ?>

    </aside><!-- /sidebar -->

</section><!-- /mesa-detail-grid -->
<?php endif; // !$pode && $mesa_livre ?>

<?php endif; // $id_mesa > 0 ?>
</main>
</body>
</html>
