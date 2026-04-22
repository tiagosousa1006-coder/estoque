<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('produtos')){
    die("Acesso negado");
}

include("../assets/layout.php"); 
?>

<h3>👨‍🔧 Novo Técnico</h3>

<?php
if($_POST){

    $nome = $conn->real_escape_string($_POST['nome']);

    if(empty($nome)){
        echo "<div class='alert alert-danger'>Informe o nome do técnico</div>";
    } else {

        $check = $conn->query("SELECT id FROM tecnicos WHERE nome = '$nome'");

        if($check->num_rows > 0){

            echo "<div class='alert alert-warning'>Técnico já cadastrado</div>";

        } else {

            if(!$conn->query("INSERT INTO tecnicos (nome) VALUES ('$nome')")){
                echo "<div class='alert alert-danger'>Erro: ".$conn->error."</div>";
            } else {
                echo "<div class='alert alert-success'>Técnico cadastrado com sucesso!</div>";
            }
        }
    }
}
?>

<form method="POST">

    <label>Nome do Técnico</label>
    <input type="text" name="nome" class="form-control mb-3" required>

    <button class="btn btn-success">Salvar</button>

</form>

<?php include("../assets/footer.php"); ?>