<?php
include("auth.php");
include("config/db.php");
include("assets/layout.php");

// SALVAR
if($_POST){

    $almox = intval($_POST['almoxarifado']);
    $produto = intval($_POST['produto']);
    $obrigatorio = isset($_POST['obrigatorio']) ? 1 : 0;

    // EVITAR DUPLICADO
    $check = $conn->query("
    SELECT id FROM almox_produtos 
    WHERE almoxarifado_id = $almox AND produto_id = $produto
    ");

    if($check->num_rows > 0){

        $conn->query("
        UPDATE almox_produtos 
        SET obrigatorio = $obrigatorio
        WHERE almoxarifado_id = $almox AND produto_id = $produto
        ");

    } else {

        $conn->query("
        INSERT INTO almox_produtos (almoxarifado_id, produto_id, obrigatorio)
        VALUES ($almox, $produto, $obrigatorio)
        ");
    }

    header("Location: almox_produtos.php?sucesso=1");
    exit;
}

// EXCLUIR
if(isset($_GET['excluir'])){
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM almox_produtos WHERE id = $id");
    header("Location: almox_produtos.php");
    exit;
}
?>

<div class="container-fluid">

<div class="card p-4 mb-3" style="border-radius:15px;">

<h4 class="mb-3">📦 Vincular Produtos ao Almoxarifado</h4>

<?php if(isset($_GET['sucesso'])): ?>
<div class="alert alert-success">Salvo com sucesso</div>
<?php endif; ?>

<form method="POST" class="row g-3">

<div class="col-md-4">
<label>Almoxarifado</label>
<select name="almoxarifado" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label>Produto</label>
<select name="produto" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4 d-flex align-items-end">
<div class="form-check">
<input type="checkbox" name="obrigatorio" class="form-check-input" checked>
<label class="form-check-label">Produto obrigatório</label>
</div>
</div>

<div class="col-12">
<button class="btn btn-primary w-100">
Salvar vínculo
</button>
</div>

</form>

</div>

<!-- LISTA -->
<div class="card p-4" style="border-radius:15px;">

<h5>📋 Produtos vinculados</h5>

<div class="table-responsive">
<table class="table table-bordered mt-3">

<tr>
<th>Almoxarifado</th>
<th>Produto</th>
<th>Obrigatório</th>
<th>Ação</th>
</tr>

<?php
$res = $conn->query("
SELECT ap.*, a.nome as almox, p.nome as produto
FROM almox_produtos ap
JOIN almoxarifados a ON a.id = ap.almoxarifado_id
JOIN produtos p ON p.id = ap.produto_id
ORDER BY a.nome, p.nome
");

if($res->num_rows == 0){
    echo "<tr><td colspan='4'>Nenhum vínculo cadastrado</td></tr>";
}

while($row = $res->fetch_assoc()){

    $obrig = $row['obrigatorio'] 
        ? "<span class='badge bg-danger'>Sim</span>"
        : "<span class='badge bg-secondary'>Não</span>";

    echo "<tr>
        <td>{$row['almox']}</td>
        <td>{$row['produto']}</td>
        <td>$obrig</td>
        <td>
            <a href='?excluir={$row['id']}' 
               class='btn btn-sm btn-danger'
               onclick=\"return confirm('Excluir vínculo?')\">
               Excluir
            </a>
        </td>
    </tr>";
}
?>

</table>
</div>

</div>

</div>

<?php include("assets/footer.php"); ?>