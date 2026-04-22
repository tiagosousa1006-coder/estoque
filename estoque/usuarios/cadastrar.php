<?php 
include("../auth.php");
include("../config/db.php");


if(!temPermissao('usuarios')){
    die("Acesso negado");
}

include("../assets/layout.php"); 
?>

<h3>👤 Novo Usuário</h3>

<?php
if($_POST){

    $nome = $conn->real_escape_string($_POST['nome']);
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $permissoes = isset($_POST['permissoes']) ? implode(",", $_POST['permissoes']) : '';

    if(empty($nome) || empty($usuario) || empty($_POST['senha'])){
        echo "<div class='alert alert-danger'>Preencha todos os campos</div>";
    } else {

        $check = $conn->query("SELECT id FROM usuarios WHERE usuario = '$usuario'");

        if($check->num_rows > 0){

            echo "<div class='alert alert-warning'>Usuário já existe</div>";

        } else {

            $sql = "INSERT INTO usuarios 
            (nome, usuario, senha, permissoes)
            VALUES ('$nome','$usuario','$senha','$permissoes')";

            if(!$conn->query($sql)){
                echo "<div class='alert alert-danger'>Erro: ".$conn->error."</div>";
            } else {
                echo "<div class='alert alert-success'>Usuário cadastrado com sucesso!</div>";
            }
        }
    }
}
?>

<form method="POST">

    <label>Nome</label>
    <input type="text" name="nome" class="form-control mb-2" required>

    <label>Usuário (login)</label>
    <input type="text" name="usuario" class="form-control mb-2" required>

    <label>Senha</label>
    <input type="password" name="senha" class="form-control mb-3" required>

    <label>Permissões</label><br>

    <div class="mb-3">
        <label><input type="checkbox" name="permissoes[]" value="produtos"> Produtos</label><br>
        <label><input type="checkbox" name="permissoes[]" value="entrada"> Entrada</label><br>
        <label><input type="checkbox" name="permissoes[]" value="saida"> Saída</label><br>
        <label><input type="checkbox" name="permissoes[]" value="relatorios"> Relatórios</label><br>
        <label><input type="checkbox" name="permissoes[]" value="usuarios"> Usuários</label>
    </div>

    <button class="btn btn-success">Salvar</button>

</form>

<?php include("../assets/footer.php"); ?>