<?php

declare(strict_types=1);

?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido</title>
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
                <a class="nav-link" href="/">Inicio</a>
                <a class="nav-link" href="/pedido" aria-current="page">Pedidos</a>
                <a class="nav-link" href="/compra">Compras</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Pedidos</p>
                <h1>Gerenciar pedidos</h1>
                <p class="lead">Acompanhe mesas, entregas e andamento com foco na operacao diaria.</p>
            </div>
            <div class="page-actions">
                <button class="button" type="button">Novo pedido</button>
                <button class="button button-ghost" type="button">Atualizar fila</button>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <div class="toolbar">
                    <div class="search">
                        <input type="search" name="search" placeholder="Buscar pedido ou mesa">
                    </div>
                    <div class="pill-group">
                        <button class="pill is-active" type="button">Em preparo</button>
                        <button class="pill" type="button">A caminho</button>
                        <button class="pill" type="button">Concluidos</button>
                        <button class="pill" type="button">Cancelados</button>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Mesa/Cliente</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="cell-title">#1042</div>
                                <div class="cell-sub">21/05/2026 - 12:10</div>
                            </td>
                            <td>Mesa 07</td>
                            <td>2 pratos, 3 bebidas</td>
                            <td>R$ 156,00</td>
                            <td><span class="badge badge--warning">Em preparo</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Editar</button>
                                    <button class="text-link" type="button">Finalizar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="cell-title">#1041</div>
                                <div class="cell-sub">21/05/2026 - 11:50</div>
                            </td>
                            <td>Delivery - Ana</td>
                            <td>1 prato, 1 sobremesa</td>
                            <td>R$ 72,00</td>
                            <td><span class="badge badge--info">A caminho</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Editar</button>
                                    <button class="text-link" type="button">Finalizar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="cell-title">#1039</div>
                                <div class="cell-sub">21/05/2026 - 11:15</div>
                            </td>
                            <td>Mesa 02</td>
                            <td>4 pratos, 2 bebidas</td>
                            <td>R$ 248,00</td>
                            <td><span class="badge badge--success">Concluido</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Detalhes</button>
                                    <button class="text-link" type="button">Reabrir</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <aside class="form-card">
                <h2>Criar pedido rapido</h2>
                <form class="stack">
                    <label class="field">
                        <span>Mesa ou cliente</span>
                        <input type="text" name="mesa" placeholder="Mesa 07 ou nome">
                    </label>
                    <label class="field">
                        <span>Itens</span>
                        <textarea name="itens" placeholder="Descreva os itens do pedido"></textarea>
                    </label>
                    <label class="field">
                        <span>Prioridade</span>
                        <select name="prioridade">
                            <option>Normal</option>
                            <option>Alta</option>
                            <option>Entrega rapida</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Status inicial</span>
                        <select name="status">
                            <option>Em preparo</option>
                            <option>A caminho</option>
                            <option>Concluido</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Observacoes</span>
                        <textarea name="observacoes" placeholder="Observacoes para a cozinha"></textarea>
                        <span class="helper">Exemplo: sem sal, embalar separado.</span>
                    </label>
                    <div class="form-actions">
                        <button class="button" type="submit">Salvar pedido</button>
                        <button class="button button-outline" type="button">Limpar</button>
                    </div>
                </form>
            </aside>
        </section>
    </main>
</body>
</html>
