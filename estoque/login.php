<?php
session_start();
include("config/db.php");
include("security.php");

$erro = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $erro = "Token CSRF inválido";
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $senha   = $_POST['senha'];
        $stmt = $conn->prepare("SELECT id, nome, usuario, senha, tipo FROM usuarios WHERE usuario = ? LIMIT 1");

        if (!$stmt) {
            $erro = "Erro interno ao preparar autenticação";
        } else {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $stmt->store_result();
        }

        if(isset($stmt) && $stmt && $stmt->num_rows > 0){
            $stmt->bind_result($id, $nome, $usuarioDb, $senhaHash, $tipo);
            $stmt->fetch();

            if(password_verify($senha, $senhaHash)){
                session_regenerate_id(true);

                // 🔐 SALVA SESSÃO
                $_SESSION['user_id'] = $id;

                // 🔥 REDIRECIONAMENTO POR PERFIL
                if($tipo == 'admin'){
                    header("Location: /estoque/index.php");
                } else {
                    header("Location: /estoque/index.php");
                }

                exit;

            } else {
                $erro = "Senha incorreta";
            }

        } else {
            if ($erro === '') {
                $erro = "Usuário não encontrado";
            }
        }

        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
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
    background: linear-gradient(135deg, #3b82f6, #1e40af);
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

.btn-primary{
    background: #3b82f6;
    border-color: #3b82f6;
}

.btn-primary:hover{
    background: #2563eb;
    border-color: #2563eb;
}
</style>

</head>
<body>

<div class="card card-login">

<h4 class="text-center mb-3">🔐 Login</h4>

<?php if($erro): ?>
<div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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
