<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

// 🔎 FILTROS
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim    = $_GET['data_fim'] ?? '';
$produto     = $_GET['produto'] ?? '';
$almox       = $_GET['almox'] ?? '';

$where = "m.subtipo = 'transferencia' AND m.tipo = 'saida'";

// 📅 DATA
if($data_inicio){
    $where .= " AND DATE(m.data_movimentacao) >= '$data_inicio'";
}

if($data_fim){
    $where .= " AND DATE(m.data_movimentacao) <= '$data_fim'";
}

// 📦 PRODUTO
if($produto){
    $where .= " AND m.produto_id = ".intval($produto);
}

// 🏢 ALMOXARIFADO (origem ou destino)
if($almox){
    $where .= " AND (m.almoxarifado_id = ".intval($almox)." OR m.destino = ".intval($almox).")";
}
?>

<style>
.card-box {
    background:#fff;
    border-radius:15px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}
.badge-transf {
    background:#0dcaf0;
    color:#000;
    padding:5px 10px;
    border-radius:8px;
}
</style>

<div class="container-fluid">

<div class="card-box">

<h4 class="mb-3">🔁 Relatório de Transferências</h4>

<!-- 🔍 FILTRO -->
<form method="GET" class="row g-2 mb-3">

<div class="col-md-2">
<label>Data Início</label>
<input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
</div>

<div class="col-md-2">
<label>Data Fim</label>
<input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
</div>

<div class="col-md-3">
<label>Produto</label>
<select name="produto" class="form-control select2">
<option value="">Todos</option>
<?php
$res = $conn->query("SELECT id, nome FROM produtos ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    $sel = ($produto == $p['id']) ? "selected" : "";
    echo "<option value='{$p['id']}' $sel>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<label>Almoxarifado</label>
<select name="almox" class="form-control">
<option value="">Todos</option>
<?php
$res = $conn->query("SELECT id, nome FROM almoxarifados ORDER BY nome ASC");
while($a = $res->fetch_assoc()){
    $sel = ($almox == $a['id']) ? "selected" : "";
    echo "<option value='{$a['id']}' $sel>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-2 d-flex align-items-end">
<button class="btn btn-primary w-100">🔍 Filtrar</button>
</div>

</form>

<!-- 📋 TABELA -->
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<tr class="table-light text-center">
    <th>Data</th>
    <th>Produto</th>
    <th>Origem</th>
    <th>Destino</th>
    <th>Quantidade</th>
    <th>Observação</th>
</tr>

<?php
$res = $conn->query("
SELECT m.*, 
       p.nome as produto,
       a.nome as origem,
       ad.nome as destino_nome
FROM movimentacoes m
JOIN produtos p ON p.id = m.produto_id
LEFT JOIN almoxarifados a ON a.id = m.almoxarifado_id
LEFT JOIN almoxarifados ad ON ad.id = m.destino
WHERE $where
ORDER BY m.id DESC
");

if($res->num_rows == 0){
    echo "<tr><td colspan='6' class='text-center'>Nenhuma transferência encontrada</td></tr>";
}

while($m = $res->fetch_assoc()){

    echo "<tr>
        <td>".date('d/m/Y H:i', strtotime($m['data_movimentacao']))."</td>
        <td>{$m['produto']}</td>
        <td>{$m['origem']}</td>
        <td>{$m['destino_nome']}</td>
        <td><strong>{$m['quantidade']}</strong></td>
        <td>{$m['observacao']}</td>
    </tr>";
}
?>

</table>
</div>

</div>

</div>

<?php include("../assets/footer.php"); ?>