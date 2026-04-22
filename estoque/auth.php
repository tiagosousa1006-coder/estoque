<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require_once __DIR__ . "/security.php";

// 🔒 VERIFICA LOGIN
if(!isset($_SESSION['user_id'])){
    header("Location: /estoque/login.php");
    exit;
}

// 🔐 CSRF GLOBAL PARA ROTAS AUTENTICADAS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!csrf_validate($csrf)) {
        http_response_code(403);
        exit("Token CSRF inválido");
    }
}

// 🔥 SEMPRE ATUALIZA OS DADOS DO USUÁRIO
require_once __DIR__ . "/config/db.php";

$id = intval($_SESSION['user_id']);

$stmt = $conn->prepare("
SELECT id, nome, tipo, permissoes, almoxarifado_id, tecnico_id
FROM usuarios
WHERE id = ?
");

if (!$stmt) {
    session_destroy();
    header("Location: /estoque/login.php");
    exit;
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    $stmt->close();
    session_destroy();
    header("Location: /estoque/login.php");
    exit;
}

$stmt->store_result();

if($stmt->num_rows == 0){
    $stmt->close();
    session_destroy();
    header("Location: /estoque/login.php");
    exit;
}

$stmt->bind_result($userId, $nome, $tipo, $permissoes, $almoxarifadoId, $tecnicoId);
$stmt->fetch();
$stmt->close();

// 🔥 ATUALIZA SESSÃO SEMPRE
$_SESSION['user_nome'] = $nome;
$_SESSION['user_tipo'] = $tipo;
$_SESSION['permissoes'] = $permissoes;
$_SESSION['almoxarifado_id'] = $almoxarifadoId;
$_SESSION['tecnico_id'] = $tecnicoId; // 🔥 AGORA VAI VIR

// 🔐 PERMISSÕES
if(!function_exists('temPermissao')){
    function temPermissao($perm){

        if($_SESSION['user_tipo'] === 'admin'){
            return true;
        }

        if(empty($_SESSION['permissoes'])){
            return false;
        }

        $permissoes = explode(",", $_SESSION['permissoes']);

        return in_array($perm, $permissoes);
    }
}
?>
