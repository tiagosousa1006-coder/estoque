<?php 
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../security.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sistema de Estoque</title>

<!-- 🔥 PWA -->
<link rel="manifest" href="/estoque/manifest.json">
<meta name="theme-color" content="#f97316">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>

<style>
:root{
    --orange-700: #c2410c;
    --orange-600: #ea580c;
    --orange-500: #f97316;
    --orange-400: #fb923c;
    --orange-100: #ffedd5;
}

body {
    margin: 0;
    background: #fff7ed;
    font-family: 'Segoe UI', sans-serif;
}

/* SIDEBAR */
.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    background: var(--orange-700);
    color: #fff;
    padding: 20px;
    overflow-y: auto;
    overflow-x: hidden;
    transition: 0.3s;
    z-index: 1000;
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
    background: var(--orange-500);
    border-radius: 10px;
}

.sidebar h4 {
    margin-bottom: 20px;
    font-weight: bold;
}

.sidebar small {
    color: #ffedd5;
    display: block;
    margin-top: 15px;
    margin-bottom: 5px;
}

.sidebar a {
    display: block;
    color: #fff7ed;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    margin-bottom: 5px;
}

.sidebar a:hover {
    background: var(--orange-600);
    color: #fff;
}

/* CONTEÚDO */
.main {
    margin-left: 240px;
    transition: 0.3s;
}

.topbar {
    height: 60px;
    background: #fff;
    border-bottom: 2px solid var(--orange-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.content {
    padding: 20px;
}

.card-custom {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: none;
}

.btn-primary,
.btn-success{
    background: var(--orange-500);
    border-color: var(--orange-500);
}

.btn-primary:hover,
.btn-success:hover{
    background: var(--orange-600);
    border-color: var(--orange-600);
}

/* MOBILE */
.menu-toggle {
    display: none;
    font-size: 22px;
    cursor: pointer;
}

@media (max-width: 768px){
    .sidebar {
        left: -240px;
    }

    .sidebar.active {
        left: 0;
    }

    .main {
        margin-left: 0;
    }

    .menu-toggle {
        display: block;
    }

    .content {
        padding: 10px;
    }

    table {
        font-size: 12px;
    }

    .btn {
        font-size: 12px;
        padding: 5px;
    }
}

.table-responsive {
    overflow-x: auto;
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">

<h4>📦 Agility</h4>

<?php if($_SESSION['user_tipo'] === 'admin'): ?>

<small>GERAL</small>
<a href="/estoque/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
<a href="/estoque/produtos/listar.php"><i class="bi bi-box"></i> Produtos</a>
<a href="/estoque/categorias/listar.php"><i class="bi bi-tags"></i> Categorias</a>
<a href="/estoque/estoque/listar.php"><i class="bi bi-boxes"></i> Estoque</a>
<a href="/estoque/tecnicos/listar.php"><i class="bi bi-person-gear"></i> Técnicos</a>

<hr>

<small>MOVIMENTAÇÕES</small>
<a href="/estoque/movimentacoes/entrada.php"><i class="bi bi-arrow-down-circle"></i> Entrada</a>
<a href="/estoque/movimentacoes/saida.php"><i class="bi bi-arrow-up-circle"></i> Saída</a>
<a href="/estoque/movimentacoes/emprestimos.php"><i class="bi bi-arrow-left-right"></i> Empréstimos</a>
<a href="/estoque/movimentacoes/historico.php"><i class="bi bi-clock-history"></i> Histórico</a>
<a href="/estoque/movimentacoes/relatorio_emprestimos.php"><i class="bi bi-clipboard-data"></i> Relatório Empréstimos</a>

<hr>

<small>ADMIN</small>
<a href="/estoque/almoxarifado/listar.php">🏢 Almoxarifados</a>
<a href="/estoque/movimentacoes/transferencia.php">🔁 Transferências</a>
<a href="/estoque/movimentacoes/relatorio_transferencias.php">
📦 Movimentações Almoxarifado
</a>

<hr>

<small>FERRAMENTAS</small>
<a href="/estoque/movimentacoes/ferramentas.php"><i class="bi bi-tools"></i> Ferramentas</a>
<a href="/estoque/movimentacoes/relatorio_ferramentas.php"><i class="bi bi-clipboard-data"></i> Relatório</a>

<hr>

<small>SISTEMA</small>
<a href="/estoque/usuarios/listar.php"><i class="bi bi-people"></i> Usuários</a>

<?php else: ?>

<small>GERAL</small>
<a href="/estoque/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

<hr>

<small>OPERAÇÃO</small>
<a href="/estoque/movimentacoes/saida.php">📤 Saída de Produtos</a>

<hr>

<small>CONSULTAS</small>
<a href="/estoque/estoque/listar.php">📦 Meu Estoque</a>
<a href="/estoque/movimentacoes/historico.php">📋 Histórico de Movimentações</a>
<a href="/estoque/movimentacoes/relatorio_ferramentas.php">🛠 Ferramentas</a>

<?php endif; ?>

<hr>

<a href="/estoque/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>

</div>

<div class="main">

<div class="topbar">
    <div>
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <strong>Painel</strong>
    </div>

    <div>
        👤 <?= e($_SESSION['user_nome'] ?? 'Usuário') ?>
    </div>
</div>

<div class="content">
<div class="container-fluid">

<script>
function toggleMenu(){
    document.getElementById('sidebar').classList.toggle('active');
}
</script>

<!-- 🔥 SERVICE WORKER -->
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/estoque/sw.js');
}
</script>