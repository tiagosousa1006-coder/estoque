<?php 
include("../auth.php");
include("../config/db.php");

// 🔥 PROCESSA ANTES DO LAYOUT
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nome = $conn->real_escape_string($_POST['nome']);

    $conn->query("INSERT INTO almoxarifados (nome) VALUES ('$nome')");
    $almox_id = $conn->insert_id;

    if(isset($_POST['produtos'])){
        foreach($_POST['produtos'] as $produto_id){

            $conn->query("
            INSERT INTO almox_produtos (almoxarifado_id, produto_id, obrigatorio)
            VALUES ($almox_id, $produto_id, 1)
            ");
        }
    }

    header("Location: listar.php?sucesso=1");
    exit;
}

// 👇 AGORA SIM CARREGA LAYOUT
include("../assets/layout.php");
?>

<div class="container-fluid">

<div class="card p-4">

<h4>Novo Almoxarifado</h4>

<form method="POST">

<label>Nome</label>
<input type="text" name="nome" class="form-control mb-3" required>

<h5>Produtos obrigatórios</h5>

<div class="row">

<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome");

while($p = $res->fetch_assoc()){
?>

<div class="col-md-4 mb-2">

<label style="border:1px solid #ddd;padding:10px;border-radius:10px;display:block;">
<input type="checkbox" name="produtos[]" value="<?= $p['id'] ?>">
<?= $p['nome'] ?>
</label>

</div>

<?php } ?>

</div>

<button class="btn btn-success mt-3">Salvar</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>