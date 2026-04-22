<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('produtos')){
    die("Acesso negado");
}

// 🔥 PROCESSA ANTES DO HTML
$msg = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $nome = $conn->real_escape_string($_POST['nome']);

    if(empty($nome)){
        $msg = "<div class='alert alert-danger'>Informe o nome da categoria</div>";
    } else {

        $check = $conn->query("SELECT id FROM categorias WHERE nome = '$nome'");

        if($check->num_rows > 0){

            $msg = "<div class='alert alert-warning'>Categoria já existe</div>";

        } else {

            if(!$conn->query("INSERT INTO categorias (nome) VALUES ('$nome')")){
                $msg = "<div class='alert alert-danger'>Erro: ".$conn->error."</div>";
            } else {

                // 🔥 REDIRECIONA (EVITA TELA BRANCA E DUPLICAÇÃO)
                header("Location: listar.php?ok=1");
                exit;
            }
        }
    }
}

include("../assets/layout.php"); 
?>

<div class="container-fluid">

<div class="card p-4" style="border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.05);">

<h4 class="mb-3">📂 Nova Categoria</h4>

<?= $msg ?>

<form method="POST">

    <label class="fw-bold">Nome da Categoria</label>
    <input type="text" name="nome" class="form-control mb-3" required>

    <button class="btn btn-success w-100">
        💾 Salvar Categoria
    </button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>