<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

$id = intval($_GET['id']);

// BUSCAR ALMOX
$res = $conn->query("SELECT * FROM almoxarifados WHERE id = $id");
$almox = $res->fetch_assoc();

// SALVAR
if($_POST){

    $nome = $conn->real_escape_string($_POST['nome']);

    $conn->query("UPDATE almoxarifados SET nome='$nome' WHERE id=$id");

    // LIMPA VÍNCULOS
    $conn->query("DELETE FROM almox_produtos WHERE almoxarifado_id = $id");

    // INSERE NOVOS
    if(isset($_POST['produtos'])){
        foreach($_POST['produtos'] as $produto_id){

            $conn->query("
            INSERT INTO almox_produtos (almoxarifado_id, produto_id, obrigatorio)
            VALUES ($id, $produto_id, 1)
            ");
        }
    }

    header("Location: listar.php?sucesso=1");
    exit;
}

// PRODUTOS JÁ VINCULADOS
$vinculados = [];
$resV = $conn->query("SELECT produto_id FROM almox_produtos WHERE almoxarifado_id = $id");
while($v = $resV->fetch_assoc()){
    $vinculados[] = $v['produto_id'];
}
?>

<div class="container-fluid">

<div class="card p-4">

<h4>Editar Almoxarifado</h4>

<form method="POST">

<label>Nome</label>
<input type="text" name="nome" class="form-control mb-3" value="<?= $almox['nome'] ?>" required>

<h5>Produtos obrigatórios</h5>

<div class="row">

<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome");

while($p = $res->fetch_assoc()){

    $checked = in_array($p['id'], $vinculados) ? 'checked' : '';
?>

<div class="col-md-4 mb-2">

<label style="border:1px solid #ddd;padding:10px;border-radius:10px;display:block;">
<input type="checkbox" name="produtos[]" value="<?= $p['id'] ?>" <?= $checked ?>>
<?= $p['nome'] ?>
</label>

</div>

<?php } ?>

</div>

<button class="btn btn-primary mt-3">Salvar Alterações</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>