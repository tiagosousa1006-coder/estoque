<?php 
include_once("../auth.php");
include_once("../config/db.php");

if(!temPermissao('saida')){
    die("Acesso negado");
}

// 🔥 PROCESSAMENTO
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $produto   = intval($_POST['produto']);
    $origem    = intval($_POST['origem']);
    $destino   = intval($_POST['destino']);
    $quantidade = intval($_POST['quantidade']);
    $obs = $conn->real_escape_string($_POST['observacao']);

    // VALIDAÇÕES
    if(!$produto || !$origem || !$destino || $quantidade <= 0){
        header("Location: transferencia.php?erro=1");
        exit;
    }

    if($origem == $destino){
        header("Location: transferencia.php?erro=2");
        exit;
    }

    // 🔎 VERIFICA SALDO
    $check = $conn->query("
    SELECT quantidade FROM estoque_local 
    WHERE produto_id = $produto AND almoxarifado_id = $origem
    ");

    $saldo = ($check && $check->num_rows > 0)
        ? $check->fetch_assoc()['quantidade']
        : 0;

    if($saldo < $quantidade){
        header("Location: transferencia.php?erro=3");
        exit;
    }

    // 🔻 BAIXA NA ORIGEM
    $conn->query("
    UPDATE estoque_local 
    SET quantidade = quantidade - $quantidade
    WHERE produto_id = $produto AND almoxarifado_id = $origem
    ");

    // 🔺 ADICIONA NO DESTINO
    $conn->query("
    INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade)
    VALUES ($produto, $destino, $quantidade)
    ON DUPLICATE KEY UPDATE quantidade = quantidade + $quantidade
    ");

    // 📝 REGISTRO SAÍDA
    $conn->query("
    INSERT INTO movimentacoes 
    (produto_id, tipo, subtipo, quantidade, almoxarifado_id, destino, observacao, data_movimentacao)
    VALUES ($produto,'saida','transferencia',$quantidade,$origem,$destino,'$obs',NOW())
    ");

    // 📝 REGISTRO ENTRADA
    $conn->query("
    INSERT INTO movimentacoes 
    (produto_id, tipo, subtipo, quantidade, almoxarifado_id, destino, observacao, data_movimentacao)
    VALUES ($produto,'entrada','transferencia',$quantidade,$destino,$origem,'$obs',NOW())
    ");

    header("Location: transferencia.php?sucesso=1");
    exit;
}

include_once("../assets/layout.php");
?>

<style>
.card-box {
    background:#fff;
    border-radius:15px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
}

.label-title {
    font-weight:600;
}
</style>

<div class="container-fluid">

<div class="card-box">

<h4 class="mb-4">🔁 Transferência entre Almoxarifados</h4>

<?php
if(isset($_GET['sucesso'])){
    echo "<div class='alert alert-success'>✔ Transferência realizada com sucesso</div>";
}

if(isset($_GET['erro'])){
    if($_GET['erro']==1) echo "<div class='alert alert-danger'>Preencha todos os campos corretamente</div>";
    if($_GET['erro']==2) echo "<div class='alert alert-danger'>Origem e destino não podem ser iguais</div>";
    if($_GET['erro']==3) echo "<div class='alert alert-danger'>Saldo insuficiente no almoxarifado de origem</div>";
}
?>

<form method="POST" class="row g-3">

<div class="col-md-4">
<label class="label-title">Produto</label>
<select name="produto" class="form-control select2" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label class="label-title">Origem</label>
<select name="origem" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label class="label-title">Destino</label>
<select name="destino" class="form-control" required>
<option value="">Selecione</option>
<?php
$res = $conn->query("SELECT * FROM almoxarifados ORDER BY nome ASC");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-4">
<label class="label-title">Quantidade</label>
<input type="number" name="quantidade" class="form-control" min="1" required>
</div>

<div class="col-md-8">
<label class="label-title">Observação</label>
<input type="text" name="observacao" class="form-control" placeholder="Opcional">
</div>

<div class="col-12">
<button class="btn btn-primary w-100 py-2">
🔁 Transferir
</button>
</div>

</form>

</div>

</div>

<?php include_once("../assets/footer.php"); ?>