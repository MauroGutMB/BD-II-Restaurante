<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';

// Rotulos dos periodos disponiveis em todos os filtros
$periodos = [
    '1m' => '1 mes',
    '3m' => 'Ultimos 3 meses',
    '1y' => '1 ano',
    'all' => 'Todos os tempos',
];

// Cada relatorio tem seu proprio filtro: pd (despesas), pg (ganhos), pt (totais)
$pd = isset($periodos[$_GET['pd'] ?? '']) ? $_GET['pd'] : '1m';
$pg = isset($periodos[$_GET['pg'] ?? '']) ? $_GET['pg'] : '1m';
$pt = isset($periodos[$_GET['pt'] ?? '']) ? $_GET['pt'] : '1m';

$despesas = get_despesas_por_categoria($pd);
$ganhos = get_ganhos_por_categoria($pg);
$total_gasto = get_total_despesas($pt);
$total_ganho = get_total_ganhos($pt);
$saldo = $total_ganho - $total_gasto;

$cores = ['#1d6f69', '#c97b2d', '#7a4ea3', '#2d6fc9', '#b3261e', '#5a8f3d', '#c92d7b', '#6b6b6b'];

// Monta os stops do conic-gradient (grafico de pizza) a partir das fatias
function pizza_gradiente(array $dados, array $cores): string {
    $total = (float)array_sum(array_column($dados, 'total'));
    if ($total <= 0) {
        return '';
    }
    $stops = [];
    $acumulado = 0.0;
    foreach ($dados as $i => $d) {
        $cor = $cores[$i % count($cores)];
        $inicio = $acumulado / $total * 100;
        $acumulado += (float)$d['total'];
        $fim = $acumulado / $total * 100;
        $stops[] = sprintf('%s %.4f%% %.4f%%', $cor, $inicio, $fim);
    }
    return 'conic-gradient(' . implode(', ', $stops) . ')';
}

function moeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Links dos filtros preservando o periodo escolhido nos outros relatorios
function link_filtro(string $param, string $valor, string $pd, string $pg, string $pt): string {
    $params = ['pd' => $pd, 'pg' => $pg, 'pt' => $pt];
    $params[$param] = $valor;
    return htmlspecialchars('/relatorio?' . http_build_query($params));
}
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatorios</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php $active = '/relatorio'; require __DIR__ . '/partials/topbar.php'; ?>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Relatorios</p>
                <h1>Relatorios gerenciais</h1>
                <p class="lead">Despesas, ganhos com pedidos fechados e o balanco do periodo.</p>
            </div>
        </section>

        <section class="report-grid">
            <div class="report-card">
                <div class="report-card-header">
                    <h2>Despesas por categoria</h2>
                    <p class="cell-sub">Gastos registrados em compras/despesas.</p>
                </div>
                <div class="filter-pills">
                    <?php foreach ($periodos as $valor => $rotulo): ?>
                    <a class="pill<?= $valor === $pd ? ' pill--active' : '' ?>" href="<?= link_filtro('pd', $valor, $pd, $pg, $pt) ?>"><?= $rotulo ?></a>
                    <?php endforeach; ?>
                </div>
                <?php $grad = pizza_gradiente($despesas, $cores); ?>
                <?php if ($grad === ''): ?>
                <p class="report-empty">Nenhuma despesa no periodo.</p>
                <?php else: ?>
                <?php $total_pizza = (float)array_sum(array_column($despesas, 'total')); ?>
                <div class="pie-wrap">
                    <div class="pie" style="background: <?= $grad ?>;"></div>
                    <ul class="legend">
                        <?php foreach ($despesas as $i => $d): ?>
                        <li>
                            <span class="legend-dot" style="background: <?= $cores[$i % count($cores)] ?>;"></span>
                            <span class="legend-label"><?= htmlspecialchars((string)$d['rotulo']) ?></span>
                            <span class="legend-value"><?= moeda((float)$d['total']) ?> <small>(<?= number_format((float)$d['total'] / $total_pizza * 100, 1, ',', '.') ?>%)</small></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="report-total-line">
                    <span>Total de despesas</span>
                    <strong><?= moeda($total_pizza) ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <div class="report-card">
                <div class="report-card-header">
                    <h2>Ganhos por categoria</h2>
                    <p class="cell-sub">Vendas dos pedidos fechados, por categoria do cardapio.</p>
                </div>
                <div class="filter-pills">
                    <?php foreach ($periodos as $valor => $rotulo): ?>
                    <a class="pill<?= $valor === $pg ? ' pill--active' : '' ?>" href="<?= link_filtro('pg', $valor, $pd, $pg, $pt) ?>"><?= $rotulo ?></a>
                    <?php endforeach; ?>
                </div>
                <?php $grad = pizza_gradiente($ganhos, $cores); ?>
                <?php if ($grad === ''): ?>
                <p class="report-empty">Nenhum pedido fechado no periodo.</p>
                <?php else: ?>
                <?php $total_pizza = (float)array_sum(array_column($ganhos, 'total')); ?>
                <div class="pie-wrap">
                    <div class="pie" style="background: <?= $grad ?>;"></div>
                    <ul class="legend">
                        <?php foreach ($ganhos as $i => $g): ?>
                        <li>
                            <span class="legend-dot" style="background: <?= $cores[$i % count($cores)] ?>;"></span>
                            <span class="legend-label"><?= htmlspecialchars((string)$g['rotulo']) ?></span>
                            <span class="legend-value"><?= moeda((float)$g['total']) ?> <small>(<?= number_format((float)$g['total'] / $total_pizza * 100, 1, ',', '.') ?>%)</small></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="report-total-line">
                    <span>Total de ganhos</span>
                    <strong><?= moeda($total_pizza) ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <div class="report-card report-card--wide">
                <div class="report-card-header">
                    <h2>Balanco do periodo</h2>
                    <p class="cell-sub">Total gasto, total ganho e saldo.</p>
                </div>
                <div class="filter-pills">
                    <?php foreach ($periodos as $valor => $rotulo): ?>
                    <a class="pill<?= $valor === $pt ? ' pill--active' : '' ?>" href="<?= link_filtro('pt', $valor, $pd, $pg, $pt) ?>"><?= $rotulo ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="totais-grid">
                    <div class="total-box total-box--gasto">
                        <span>Total gasto</span>
                        <strong><?= moeda($total_gasto) ?></strong>
                    </div>
                    <div class="total-box total-box--ganho">
                        <span>Total ganho</span>
                        <strong><?= moeda($total_ganho) ?></strong>
                    </div>
                    <div class="total-box <?= $saldo >= 0 ? 'total-box--ganho' : 'total-box--gasto' ?>">
                        <span>Saldo</span>
                        <strong><?= moeda($saldo) ?></strong>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
