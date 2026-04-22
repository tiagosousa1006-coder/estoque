<?php 
include_once("../auth.php");
include_once("../config/db.php");

if(!isset($_SESSION['user_id'])){
    die("Acesso negado");
}

$admin = ($_SESSION['user_tipo'] === 'admin');

// 🔄 DEVOLVER
if(isset($_GET['devolver']) && $admin){

    $id = intval($_GET['devolver']);

    $res = $conn->query("
    SELECT produto_id, almoxarifado_id 
    FROM ferramentas_retiradas 
    WHERE id = $id
    ");

    if($res && $res->num_rows > 0){
        $d = $res->fetch_assoc();

        $conn->query("
        UPDATE estoque_local 
        SET quantidade = quantidade + 1
        WHERE produto_id = {$d['produto_id']} 
        AND almoxarifado_id = {$d['almoxarifado_id']}
        ");
    }

    $conn->query("UPDATE ferramentas_retiradas SET devolvido = 1 WHERE id = $id");

    header("Location: ferramentas.php?ok=1");
    exit;
}

// 🔥 REGISTRAR
if($_SERVER['REQUEST_METHOD'] == 'POST' && $admin){

    $produto = intval($_POST['produto']);
    $tecnico = intval($_POST['tecnico']);
    $almox = intval($_POST['almoxarifado']);
    $data_retirada = $_POST['data_retirada'];

    // 🔥 DATA OU NULL
    $data_devolucao = !empty($_POST['data_devolucao']) 
        ? "'".$_POST['data_devolucao']."'" 
        : "NULL";

    if(!$produto || !$tecnico || !$almox || !$data_retirada){
        header("Location: ferramentas.php?erro=1");
        exit;
    }

    // estoque
    $check = $conn->query("
    SELECT quantidade FROM estoque_local 
    WHERE produto_id = $produto AND almoxarifado_id = $almox
    ");

    $saldo = ($check && $check->num_rows > 0) 
        ? $check->fetch_assoc()['quantidade'] 
        : 0;

    if($saldo <= 0){
        header("Location: ferramentas.php?erro=2");
        exit;
    }

    // baixa estoque
    $conn->query("
    UPDATE estoque_local 
    SET quantidade = quantidade - 1
    WHERE produto_id = $produto AND almoxarifado_id = $almox
    ");

    // registra
    $conn->query("
    INSERT INTO ferramentas_retiradas 
    (produto_id, tecnico_id, almoxarifado_id, data_retirada, data_devolucao, devolvido, notificado)
    VALUES ($produto,$tecnico,$almox,'$data_retirada',$data_devolucao,0,0)
    ");

    header("Location: ferramentas.php?sucesso=1");
    exit;
}

include_once("../assets/layout.php");
?>

<style>
.card-form {
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    border: none;
}

.table-box {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    margin-top: 20px;
}

.label-title {
    font-weight: 600;
}
</style>

<div class="container-fluid">

<!-- FORM -->
<div class="card card-form p-4">

<h4 class="mb-4">🧰 Controle de Ferramentas</h4>

<?php
if(isset($_GET['sucesso'])){
    echo "<div class='alert alert-success'>✔ Retirada registrada</div>";
}
if(isset($_GET['ok'])){
    echo "<div class='alert alert-success'>✔ Devolução realizada</div>";
}
if(isset($_GET['erro'])){
    if($_GET['erro']==1) echo "<div class='alert alert-danger'>Preencha todos os campos</div>";
    if($_GET['erro']==2) echo "<div class='alert alert-danger'>Sem estoque disponível</div>";
}
?>

<?php if($admin): ?>

<form method="POST" class="row g-3">

<div class="col-md-4">
<label class="label-title">Ferramenta</label>
<select name="produto" class="form-control select2" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label class="label-title">Almoxarifado</label>
<select name="almoxarifado" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label class="label-title">Técnico</label>
<select name="tecnico" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM tecnicos ORDER BY nome ASC");
while($t = $res->fetch_assoc()){
    echo "<option value='{$t['id']}'>{$t['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-6">
<label class="label-title">Data Retirada</label>
<input type="date" name="data_retirada" class="form-control" required>
</div>

<div class="col-md-6">
<label class="label-title">Data Devolução</label>

<input type="date" name="data_devolucao" id="data_devolucao" class="form-control">

<div class="form-check mt-2">
    <input class="form-check-input" type="checkbox" id="sem_data">
    <label class="form-check-label">
        Sem data de devolução
    </label>
</div>

</div>

<div class="col-12">
<button class="btn btn-primary w-100 py-2">🔧 Registrar Retirada</button>
</div>

</form>

<?php endif; ?>

</div>

<!-- TABELA -->
<div class="table-box">

<h5>📋 Ferramentas em uso</h5>

<div class="table-responsive">
<table class="table table-bordered table-striped mt-3">

<tr>
<th>Ferramenta</th>
<th>Técnico</th>
<th>Devolução</th>
<th>Status</th>
<?php if($admin): ?><th>Ação</th><?php endif; ?>
</tr>

<?php
$res = $conn->query("
SELECT f.*, p.nome as produto, t.nome as tecnico
FROM ferramentas_retiradas f
JOIN produtos p ON p.id = f.produto_id
JOIN tecnicos t ON t.id = f.tecnico_id
WHERE f.devolvido = 0
ORDER BY f.data_devolucao ASC
");

if($res->num_rows == 0){
    echo "<tr><td colspan='5'>Nenhuma ferramenta em uso</td></tr>";
}

while($f = $res->fetch_assoc()){

    if(empty($f['data_devolucao'])){
        $status = "<span class='badge bg-secondary'>Sem prazo</span>";
        $cor = "";
        $data = "-";
    } else if($f['data_devolucao'] < date('Y-m-d')){
        $status = "<span class='badge bg-danger'>Atrasado</span>";
        $cor = "style='background:#f8d7da'";
        $data = date('d/m/Y', strtotime($f['data_devolucao']));
    } else {
        $status = "<span class='badge bg-success'>Em dia</span>";
        $cor = "";
        $data = date('d/m/Y', strtotime($f['data_devolucao']));
    }

    echo "<tr $cor>
        <td>{$f['produto']}</td>
        <td>{$f['tecnico']}</td>
        <td>$data</td>
        <td>$status</td>";

    if($admin){
        echo "<td>
            <a href='?devolver={$f['id']}' 
               class='btn btn-success btn-sm'
               onclick=\"return confirm('Confirmar devolução?')\">
               Devolver
            </a>
        </td>";
    }

    echo "</tr>";
}
?>

</table>
</div>

</div>

</div>

<script>
document.getElementById("sem_data").addEventListener("change", function(){
    let campo = document.getElementById("data_devolucao");

    if(this.checked){
        campo.value = "";
        campo.disabled = true;
    } else {
        campo.disabled = false;
    }
});
</script>

<?php include_once("../assets/footer.php"); ?>