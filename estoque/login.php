<?php
session_start();
include("config/db.php");

$erro = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $usuario = $conn->real_escape_string($_POST['usuario']);
    $senha   = $_POST['senha'];

    $res = $conn->query("SELECT * FROM usuarios WHERE usuario = '$usuario' LIMIT 1");

    if($res && $res->num_rows > 0){

        $user = $res->fetch_assoc();

        if(password_verify($senha, $user['senha'])){

            // 🔐 SALVA SESSÃO
            $_SESSION['user_id'] = $user['id'];

            // 🔥 REDIRECIONAMENTO POR PERFIL
            if($user['tipo'] == 'admin'){
                header("Location: /estoque/index.php");
            } else {
                header("Location: /estoque/index.php");
            }

            exit;

        } else {
            $erro = "Senha incorreta";
        }

    } else {
        $erro = "Usuário não encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login - Sistema</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.card-login{
    width:100%;
    max-width:400px;
    border-radius:15px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}
</style>

</head>
<body>

<div class="card card-login">

<h4 class="text-center mb-3">🔐 Login</h4>

<?php if($erro): ?>
<div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Usuário</label>
<input type="text" name="usuario" class="form-control" required>
</div>

<div class="mb-3">
<label>Senha</label>
<input type="password" name="senha" class="form-control" required>
</div>

<button class="btn btn-primary w-100">
Entrar
</button>

</form>

</div>

</body>
</html>