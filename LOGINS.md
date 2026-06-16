# Logins do Sistema

Credenciais dos usuários cadastrados no banco de dados (`database.sqlite`).

> Senhas armazenadas com hash bcrypt — os valores abaixo são apenas para acesso local/avaliação.

## Gerente (admin)

| Nome    | Usuário | Senha      | Perfil |
| ------- | ------- | ---------- | ------ |
| Gerente | `admin` | `admin123` | admin  |

Acesso total: mesas, cardápio, servidores, fornecedores, relatórios, extrato de pedidos.

## Servidores

| Nome         | Usuário | Senha      | Mesa responsável |
| ------------ | ------- | ---------- | ---------------- |
| Maria Santos | `maria` | `maria123` | Mesa 1           |
| Joao Pereira | `joao`  | `joao123`  | Mesa 2           |
| Ana Lima     | `ana`   | `ana123`   | Mesa 3           |

Servidores gerenciam sua mesa: participantes, conta em aberto (itens, fechamento) e liberação da mesa.

## Clientes cadastrados

| Nome             | Mesa   |
| ---------------- | ------ |
| Lucas Ferreira   | Mesa 1 |
| Beatriz Costa    | Mesa 1 |
| Rafael Almeida   | Mesa 2 |
| Camila Santos    | Mesa 2 |
| Diego Oliveira   | Mesa 2 |
| Fernanda Lima    | Mesa 3 |
| Carlos Mendes    | Mesa 3 |
| Sofia Rodrigues  | Mesa 3 |

## Como rodar

```bash
php -S localhost:8000 -t public public/index.php
```

Acesse `http://localhost:8000` e faça login com uma das credenciais acima.
