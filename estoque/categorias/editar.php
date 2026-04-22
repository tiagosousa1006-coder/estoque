<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('produtos')){
    die("Acesso negado");
}

$id = intval($_GET['id'] ?? 0);

$res = $conn->query("SELECT * FROM categorias WHERE id = $id");

if($res->num_rows == 0){
    die("Categoria não encontrada");
}

$categoria = $res->fetch_assoc();

$msg = "";

// 🔥 PROCESSA
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nome = $conn->real_escape_string($_POST['nome']);

    if(empty($nome)){
        $msg = "<div class='alert alert-danger'>Informe o nome</div>";
    } else {

        $check = $conn->query("
        SELECT id FROM categorias 
        WHERE nome = '$nome' AND id != $id
        ");

        if($check->num_rows > 0){
            $msg = "<div class='alert alert-warning'>Categoria já existe</div>";
        } else {

            $conn->query("
            UPDATE categorias 
            SET nome = '$nome'
            WHERE id = $id
            ");

            header("Location: listar.php?editado=1");
            exit;
        }
    }
}

include("../assets/layout.php");
?>

<div class="container-fluid">

<div class="card p-4" style="border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.05);">

<h4 class="mb-3">✏️ Editar Categoria</h4>

<?= $msg ?>

<form method="POST">

<label class="fw-bold">Nome</label>
<input type="text" name="nome" class="form-control mb-3"
value="<?= $categoria['nome'] ?>" required>

<button class="btn btn-primary w-100">
💾 Salvar Alterações
</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>