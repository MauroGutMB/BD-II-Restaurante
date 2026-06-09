<?php

declare(strict_types=1);

?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compra</title>
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
                <a class="nav-link" href="/pedido">Pedidos</a>
                <a class="nav-link" href="/compra" aria-current="page">Compras</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Compras</p>
                <h1>Gerenciar compras</h1>
                <p class="lead">Centralize fornecedores, estoque e pagamentos com um fluxo simples de CRUD.</p>
            </div>
            <div class="page-actions">
                <button class="button" type="button">Nova compra</button>
                <button class="button button-ghost" type="button">Gerar relatorio</button>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <div class="toolbar">
                    <div class="search">
                        <input type="search" name="search" placeholder="Buscar compra ou fornecedor">
                    </div>
                    <div class="pill-group">
                        <button class="pill is-active" type="button">Hoje</button>
                        <button class="pill" type="button">Semana</button>
                        <button class="pill" type="button">Mes</button>
                        <button class="pill" type="button">Pagas</button>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="cell-title">Mercado Sul</div>
                                <div class="cell-sub">Nota 1920</div>
                            </td>
                            <td>Hortifruti</td>
                            <td>21/05/2026</td>
                            <td>R$ 1.420,00</td>
                            <td><span class="badge badge--success">Pago</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Detalhes</button>
                                    <button class="text-link" type="button">Editar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="cell-title">Pescados Norte</div>
                                <div class="cell-sub">Entrega programada</div>
                            </td>
                            <td>Frios</td>
                            <td>20/05/2026</td>
                            <td>R$ 980,00</td>
                            <td><span class="badge badge--warning">Pendente</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Detalhes</button>
                                    <button class="text-link" type="button">Editar</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="cell-title">Distribuidora Central</div>
                                <div class="cell-sub">Contrato mensal</div>
                            </td>
                            <td>Bebidas</td>
                            <td>19/05/2026</td>
                            <td>R$ 2.350,00</td>
                            <td><span class="badge badge--info">Em entrega</span></td>
                            <td>
                                <div class="button-row">
                                    <button class="text-link" type="button">Detalhes</button>
                                    <button class="text-link" type="button">Editar</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <aside class="form-card">
                <h2>Registrar compra</h2>
                <form class="stack">
                    <label class="field">
                        <span>Fornecedor</span>
                        <input type="text" name="fornecedor" placeholder="Nome do fornecedor">
                    </label>
                    <label class="field">
                        <span>Categoria</span>
                        <select name="categoria">
                            <option>Hortifruti</option>
                            <option>Frios</option>
                            <option>Bebidas</option>
                            <option>Limpeza</option>
                        </select>
                    </label>
                    <label class="field">
                        <span>Data da compra</span>
                        <input type="date" name="data">
                    </label>
                    <label class="field">
                        <span>Valor total</span>
                        <input type="text" name="valor" placeholder="R$ 0,00">
                        <span class="helper">Exemplo: R$ 1.250,00</span>
                    </label>
                    <label class="field">
                        <span>Observacoes</span>
                        <textarea name="observacoes" placeholder="Detalhes da compra"></textarea>
                    </label>
                    <div class="form-actions">
                        <button class="button" type="submit">Salvar compra</button>
                        <button class="button button-outline" type="button">Limpar</button>
                    </div>
                </form>
            </aside>
        </section>
    </main>
</body>
</html>
