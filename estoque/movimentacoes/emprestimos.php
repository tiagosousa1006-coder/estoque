<?php 
include("../auth.php");
include("../config/db.php");

// 🔁 DEVOLVER
if(isset($_GET['devolver'])){
    $id = intval($_GET['devolver']);

    $res = $conn->query("SELECT * FROM emprestimos WHERE id=$id");
    $e = $res->fetch_assoc();

    if($e && $e['devolvido'] == 0){

        if($e['tipo'] == 'emprestado'){
            $conn->query("
            UPDATE estoque_local 
            SET quantidade = quantidade + {$e['quantidade']}
            WHERE produto_id = {$e['produto_id']} 
            AND almoxarifado_id = {$e['almoxarifado_id']}
            ");
        } else {
            $conn->query("
            UPDATE estoque_local 
            SET quantidade = quantidade - {$e['quantidade']}
            WHERE produto_id = {$e['produto_id']} 
            AND almoxarifado_id = {$e['almoxarifado_id']}
            ");
        }

        $conn->query("UPDATE emprestimos SET devolvido=1 WHERE id=$id");
    }

    header("Location: emprestimos.php?sucesso=devolvido");
    exit;
}

// 🔥 CADASTRAR
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $produto = intval($_POST['produto']);
    $almox = intval($_POST['almoxarifado']);
    $tipo = $_POST['tipo'];
    $nome = $conn->real_escape_string($_POST['nome']);
    $quantidade = intval($_POST['quantidade']);

    // DATA
    if(isset($_POST['sem_data'])){
        $data_dev = "NULL";
    } else {
        $data_dev = !empty($_POST['data_devolucao']) 
            ? "'".$conn->real_escape_string($_POST['data_devolucao'])."'" 
            : "NULL";
    }

    // VALIDAÇÃO
    if($tipo == 'emprestado'){

        $check = $conn->query("
        SELECT quantidade FROM estoque_local 
        WHERE produto_id=$produto AND almoxarifado_id=$almox
        ");

        if(!$check || $check->num_rows == 0){
            header("Location: emprestimos.php?erro=sem_estoque");
            exit;
        }

        $q = $check->fetch_assoc();

        if($q['quantidade'] < $quantidade){
            header("Location: emprestimos.php?erro=estoque");
            exit;
        }
    }

    // MOVIMENTAÇÃO
    if($tipo == 'emprestado'){
        $conn->query("
        UPDATE estoque_local 
        SET quantidade = quantidade - $quantidade
        WHERE produto_id=$produto AND almoxarifado_id=$almox
        ");
    } else {
        $conn->query("
        INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade)
        VALUES ($produto, $almox, $quantidade)
        ON DUPLICATE KEY UPDATE quantidade = quantidade + $quantidade
        ");
    }

    // REGISTRO
    $conn->query("
    INSERT INTO emprestimos 
    (produto_id, almoxarifado_id, tipo, nome_pessoa, quantidade, data_emprestimo, data_devolucao)
    VALUES ($produto,$almox,'$tipo','$nome',$quantidade,CURDATE(),$data_dev)
    ");

    header("Location: emprestimos.php?sucesso=1");
    exit;
}

// 👇 IMPORTANTE: layout só depois do POST
include("../assets/layout.php");
?>

<div class="container-fluid">

<!-- FORM -->
<div class="card p-4 mb-3">

<h4 class="mb-4">🔁 Empréstimos</h4>

<!-- MENSAGENS -->
<?php if(isset($_GET['sucesso'])): ?>
<div class="alert alert-success">
✔ <?= ($_GET['sucesso']=='devolvido') ? 'Devolução realizada!' : 'Registrado com sucesso!' ?>
</div>
<?php endif; ?>

<?php if(isset($_GET['erro'])): ?>

<?php if($_GET['erro']=='sem_estoque'): ?>
<div class="alert alert-danger">❌ Produto sem estoque neste almoxarifado</div>
<?php endif; ?>

<?php if($_GET['erro']=='estoque'): ?>
<div class="alert alert-danger">❌ Estoque insuficiente</div>
<?php endif; ?>

<?php endif; ?>

<form method="POST" class="row g-3">

<div class="col-md-3">
<label>Produto</label>
<select name="produto" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<label>Almoxarifado</label>
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

<div class="col-md-2">
<label>Tipo</label>
<select name="tipo" class="form-control">
    <option value="emprestado">Emprestar</option>
    <option value="recebido">Receber</option>
</select>
</div>

<div class="col-md-4">
<label>Pessoa/Empresa</label>
<input type="text" name="nome" class="form-control" required>
</div>

<div class="col-md-2">
<label>Quantidade</label>
<input type="number" name="quantidade" class="form-control" required>
</div>

<div class="col-md-3">
<label>Data Devolução</label>
<input type="date" name="data_devolucao" id="data_dev" class="form-control">
</div>

<div class="col-md-3 d-flex align-items-end">
<div class="form-check">
<input type="checkbox" name="sem_data" id="sem_data" class="form-check-input">
<label class="form-check-label">Sem data de devolução</label>
</div>
</div>

<div class="col-md-4 d-flex align-items-end">
<button class="btn btn-primary w-100 py-2">
📦 Registrar Empréstimo
</button>
</div>

</form>

</div>

<!-- LISTA -->
<div class="card p-4">

<h5>📋 Lista de Empréstimos</h5>

<div class="table-responsive">
<table class="table table-bordered mt-3">

<tr>
<th>Produto</th>
<th>Almox</th>
<th>Tipo</th>
<th>Pessoa</th>
<th>Qtd</th>
<th>Devolução</th>
<th>Status</th>
<th>Ação</th>
</tr>

<?php
$res = $conn->query("
SELECT e.*, p.nome as produto, a.nome as almoxarifado
FROM emprestimos e
JOIN produtos p ON p.id = e.produto_id
JOIN almoxarifados a ON a.id = e.almoxarifado_id
ORDER BY e.id DESC
");

while($row = $res->fetch_assoc()){

    $cor = "";

    if($row['devolvido']){
        $status = "<span class='badge bg-success'>Devolvido</span>";
    } elseif($row['data_devolucao'] && $row['data_devolucao'] < date('Y-m-d')){
        $status = "<span class='badge bg-danger'>Atrasado</span>";
        $cor = "style='background:#f8d7da'";
    } else {
        $status = "<span class='badge bg-warning text-dark'>Pendente</span>";
    }

    echo "<tr $cor>
        <td>{$row['produto']}</td>
        <td>{$row['almoxarifado']}</td>
        <td>{$row['tipo']}</td>
        <td>{$row['nome_pessoa']}</td>
        <td>{$row['quantidade']}</td>
        <td>".($row['data_devolucao'] ? date('d/m/Y', strtotime($row['data_devolucao'])) : 'Sem data')."</td>
        <td>$status</td>
        <td>";

    if(!$row['devolvido']){
        echo "<a href='?devolver={$row['id']}' 
              class='btn btn-success btn-sm'
              onclick=\"return confirm('Confirmar devolução?')\">
              Devolver
              </a>";
    }

    echo "</td></tr>";
}
?>

</table>
</div>

</div>

</div>

<script>
document.getElementById('sem_data').addEventListener('change', function(){
    let campo = document.getElementById('data_dev');

    if(this.checked){
        campo.value = '';
        campo.disabled = true;
    } else {
        campo.disabled = false;
    }
});
</script>

<?php include("../assets/footer.php"); ?>