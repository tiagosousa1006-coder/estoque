<?php 
include("auth.php");
include("config/db.php");
include("assets/layout.php");

// 🔒 DADOS
$user_tipo = $_SESSION['user_tipo'] ?? 'usuario';
$almox_usuario = $_SESSION['almoxarifado_id'] ?? null;

// 🔥 FILTRO ALMOX
$where_almox = "";
if($user_tipo != 'admin' && $almox_usuario){
    $where_almox = "WHERE id = $almox_usuario";
}

$almoxarifados = $conn->query("
SELECT * FROM almoxarifados
$where_almox
ORDER BY nome
");
?>

<style>
.card-mini {
    border-radius: 12px;
    padding: 20px;
    color: #fff;
    text-decoration: none;
    display: block;
    transition: 0.2s;
}
.card-mini:hover {
    transform: scale(1.03);
    opacity: 0.9;
}

.verde { background: #22c55e; }
.vermelho { background: #ef4444; }

.alert-box {
    border-radius: 12px;
    padding: 15px;
    margin-top: 20px;
}

.alert-vermelho { background: #f8d7da; }
.alert-amarelo { background: #fff3cd; }
</style>

<div class="container-fluid">

<h4 class="mb-4">📊 Dashboard</h4>

<!-- 🔥 CARDS -->
<div class="row g-3">

<?php while($a = $almoxarifados->fetch_assoc()): ?>

<?php
$almox_id = $a['id'];

$check = $conn->query("
SELECT COUNT(*) as total
FROM almox_produtos ap
JOIN produtos p ON p.id = ap.produto_id
LEFT JOIN estoque_local e 
    ON e.produto_id = ap.produto_id 
    AND e.almoxarifado_id = ap.almoxarifado_id
WHERE ap.almoxarifado_id = $almox_id
AND ap.obrigatorio = 1
AND p.estoque_minimo > 0
AND IFNULL(e.quantidade,0) <= 0
");

$tem_falta = $check->fetch_assoc()['total'] > 0;
$classe = $tem_falta ? 'vermelho' : 'verde';
?>

<div class="col-md-3">

<a href="/estoque/estoque/listar.php?almoxarifado=<?= $almox_id ?>" 
   class="card-mini <?= $classe ?>">

<h5>🏢 <?= $a['nome'] ?></h5>

<?php if($tem_falta): ?>
<small>⚠️ Estoque pendente</small>
<?php else: ?>
<small>✔ Tudo OK</small>
<?php endif; ?>

</a>

</div>

<?php endwhile; ?>

</div>

<?php if($user_tipo == 'admin'): ?>

<?php
// 🔴 ZERADOS (mínimo > 0)
$zerados = $conn->query("
SELECT p.nome
FROM produtos p
WHERE p.estoque_minimo > 0
AND (
    SELECT IFNULL(SUM(e.quantidade),0)
    FROM estoque_local e
    WHERE e.produto_id = p.id
) <= 0
ORDER BY p.nome
");

// 🟡 ABAIXO DO MÍNIMO
$baixo = $conn->query("
SELECT p.nome,
(
    SELECT IFNULL(SUM(e.quantidade),0)
    FROM estoque_local e
    WHERE e.produto_id = p.id
) as saldo
FROM produtos p
WHERE p.estoque_minimo > 0
AND (
    SELECT IFNULL(SUM(e.quantidade),0)
    FROM estoque_local e
    WHERE e.produto_id = p.id
) > 0
AND (
    SELECT IFNULL(SUM(e.quantidade),0)
    FROM estoque_local e
    WHERE e.produto_id = p.id
) <= p.estoque_minimo
ORDER BY saldo ASC
");
?>

<!-- 🔴 ZERADOS -->
<?php if($zerados->num_rows > 0): ?>
<div class="alert-box alert-vermelho">
<strong>🔴 Produtos zerados:</strong>
<ul>
<?php while($p = $zerados->fetch_assoc()): ?>
<li><?= $p['nome'] ?></li>
<?php endwhile; ?>
</ul>
</div>
<?php endif; ?>

<!-- 🟡 BAIXO -->
<?php if($baixo->num_rows > 0): ?>
<div class="alert-box alert-amarelo">
<strong>🟡 Estoque baixo:</strong>
<ul>
<?php while($p = $baixo->fetch_assoc()): ?>
<li><?= $p['nome'] ?> (<?= $p['saldo'] ?>)</li>
<?php endwhile; ?>
</ul>
</div>
<?php endif; ?>

<?php endif; ?>

</div>

<?php include("assets/footer.php"); ?>