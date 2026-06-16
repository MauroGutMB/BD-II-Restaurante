<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bd/auth.php';

$user = current_user();
$active = $active ?? '';
$links = [
    '/' => 'Inicio',
    '/pedido' => 'Pedidos',
    '/compra' => 'Compras',
    '/cliente' => 'Clientes',
];
if (is_admin()) {
    $links['/mesa'] = 'Mesas';
} else {
    $links['/gerenciar-mesa'] = 'Mesas';
}
$links['/cardapio'] = 'Cardapio';
if (is_admin()) {
    $links['/servidor'] = 'Servidores';
    $links['/fornecedor'] = 'Fornecedores';
    $links['/relatorio'] = 'Relatorios';
}
?>
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="brand">
                <span class="brand-mark">RD</span>
                <div>
                    <div class="brand-title">Restaurante DB</div>
                    <div class="brand-sub">Painel de gestao de pedidos e compras</div>
                </div>
            </div>
            <nav class="nav-links">
                <?php foreach ($links as $href => $label): ?>
                <a class="nav-link" href="<?= $href ?>"<?= $href === $active ? ' aria-current="page"' : '' ?>><?= $label ?></a>
                <?php endforeach; ?>
            </nav>
            <?php if ($user): ?>
            <div class="topbar-user">
                <div class="user-chip">
                    <span class="user-avatar"><?= strtoupper(substr((string)$user['nome'], 0, 1)) ?></span>
                    <div class="user-info">
                        <strong><?= htmlspecialchars((string)$user['nome']) ?></strong>
                        <span><?= $user['perfil'] === 'admin' ? 'Gerente' : 'Servidor' ?></span>
                    </div>
                </div>
                <form method="POST" action="/" class="logout-form">
                    <input type="hidden" name="action" value="logout">
                    <button class="logout-btn" type="submit" title="Sair do sistema">Sair</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </header>
