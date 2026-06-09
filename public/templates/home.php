<?php

declare(strict_types=1);

?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurante DB</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
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
                <a class="nav-link" href="/" aria-current="page">Inicio</a>
                <a class="nav-link" href="/pedido">Pedidos</a>
                <a class="nav-link" href="/compra">Compras</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="hero">
            <div class="hero-content">
                <p class="eyebrow">Painel principal</p>
                <h1>Controle total do fluxo do restaurante</h1>
                <p class="lead">Acompanhe compras, pedidos e status em um unico lugar. Use os atalhos abaixo para acessar os paineis.</p>
                <div class="button-row">
                    <a class="button" href="/pedido">Gerenciar pedidos</a>
                    <a class="button button-ghost" href="/compra">Gerenciar compras</a>
                </div>
            </div>
            <div class="hero-card">
                <h2>Resumo rapido</h2>
                <p class="lead">Indicadores simulados para demonstrar a visao geral do CRUD.</p>
                <ul class="stat-list">
                    <li class="stat-item"><span>Pedidos em aberto</span><strong>12</strong></li>
                    <li class="stat-item"><span>Compras pendentes</span><strong>4</strong></li>
                    <li class="stat-item"><span>Itens em estoque</span><strong>138</strong></li>
                </ul>
            </div>
        </section>

        <section class="card-grid">
            <article class="card">
                <h3>Gestao de pedidos</h3>
                <p class="lead">Organize o fluxo de mesas, entregas e status com rapidez.</p>
                <ul class="card-list">
                    <li>Editar status e prioridade</li>
                    <li>Revisar historico por data</li>
                    <li>Adicionar observacoes do cliente</li>
                </ul>
                <div class="card-actions">
                    <a class="text-link" href="/pedido">Abrir painel</a>
                </div>
            </article>
            <article class="card">
                <h3>Gestao de compras</h3>
                <p class="lead">Controle fornecedores, categorias e valores do abastecimento.</p>
                <ul class="card-list">
                    <li>Registrar compras recorrentes</li>
                    <li>Filtrar por categoria</li>
                    <li>Gerar relatorios simples</li>
                </ul>
                <div class="card-actions">
                    <a class="text-link" href="/compra">Abrir painel</a>
                </div>
            </article>
            <article class="card">
                <h3>Rotina diaria</h3>
                <p class="lead">Use o painel para manter o time alinhado e agil.</p>
                <ul class="card-list">
                    <li>Atualizacoes em tempo real</li>
                    <li>Campos prontos para integracao</li>
                    <li>Layout responsivo</li>
                </ul>
                <div class="card-actions">
                    <a class="text-link" href="/pedido">Conferir pedidos</a>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
