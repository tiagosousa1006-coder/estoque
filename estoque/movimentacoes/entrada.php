<?php 
include_once("../auth.php");
include_once("../config/db.php");

if($_SESSION['user_tipo'] !== 'admin'){
    die("Acesso restrito");
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $modo = $_POST['modo'];

    // 🔵 ENTRADA SIMPLES (SEU CÓDIGO ORIGINAL)
    if($modo == 'simples'){

        $produto = intval($_POST['produto']);
        $almox = intval($_POST['almoxarifado']);
        $quantidade = intval($_POST['quantidade']);
        $tipo = $_POST['tipo'];
        $obs = $conn->real_escape_string($_POST['observacao']);

        if($quantidade <= 0){
            header("Location: entrada.php?erro=1");
            exit;
        }

        if(!in_array($tipo, ['compra','retorno'])){
            header("Location: entrada.php?erro=2");
            exit;
        }

        $conn->query("
        INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade)
        VALUES ($produto, $almox, $quantidade)
        ON DUPLICATE KEY UPDATE quantidade = quantidade + $quantidade
        ");

        $conn->query("
        INSERT INTO movimentacoes 
        (produto_id, tipo, subtipo, quantidade, almoxarifado_id, observacao, data_movimentacao)
        VALUES ($produto,'entrada','$tipo',$quantidade,$almox,'$obs',NOW())
        ");

        header("Location: entrada.php?sucesso=1");
        exit;
    }

    // 🟢 ENTRADA POR NOTA FISCAL
    if($modo == 'nota'){

        $numero = $_POST['numero'];
        $fornecedor = $_POST['fornecedor'];
        $data = $_POST['data'];

        // salva nota
        $conn->query("
        INSERT INTO notas_entrada (numero, fornecedor, data)
        VALUES ('$numero','$fornecedor','$data')
        ");

        $nota_id = $conn->insert_id;

        foreach($_POST['produto_id'] as $i => $produto){

            $produto = intval($produto);
            $quantidade = intval($_POST['quantidade'][$i]);
            $almox = intval($_POST['almoxarifado'][$i]);

            if($produto && $quantidade > 0){

                // item da nota
                $conn->query("
                INSERT INTO notas_itens (nota_id, produto_id, quantidade)
                VALUES ($nota_id,$produto,$quantidade)
                ");

                // estoque
                $conn->query("
                INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade)
                VALUES ($produto,$almox,$quantidade)
                ON DUPLICATE KEY UPDATE quantidade = quantidade + $quantidade
                ");

                // movimentação
                $conn->query("
                INSERT INTO movimentacoes 
                (produto_id, tipo, subtipo, quantidade, almoxarifado_id, observacao, data_movimentacao)
                VALUES ($produto,'entrada','nota',$quantidade,$almox,'NF: $numero',NOW())
                ");
            }
        }

        header("Location: entrada.php?sucesso=1");
        exit;
    }
}

include_once("../assets/layout.php");
?>

<style>
.card-form {
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    border: none;
}
.label-title {
    font-weight: 600;
    margin-bottom: 5px;
}
input, select {
    border-radius: 10px !important;
}
button {
    border-radius: 10px !important;
}
</style>

<div class="container-fluid">
<div class="card card-form p-4">

<h4 class="mb-4">⬇️ Entrada de Produtos</h4>

<?php
if(isset($_GET['sucesso'])){
    echo "<div class='alert alert-success'>✔ Entrada registrada com sucesso</div>";
}
if(isset($_GET['erro'])){
    if($_GET['erro'] == 1){
        echo "<div class='alert alert-danger'>Quantidade deve ser maior que zero</div>";
    }
    if($_GET['erro'] == 2){
        echo "<div class='alert alert-danger'>Tipo de entrada inválido</div>";
    }
}
?>

<form method="POST" class="row g-3">

<!-- 🔥 MODO -->
<div class="col-12">
<label class="label-title">Modo</label>
<select name="modo" id="modo" class="form-control" onchange="toggleModo()">
<option value="simples">Entrada Simples</option>
<option value="nota">Nota Fiscal</option>
</select>
</div>

<!-- 🔵 SIMPLES -->
<div id="simples">

<div class="col-md-4">
<label class="label-title">Produto</label>
<select name="produto" class="form-control select2">
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
<label class="label-title">Almoxarifado</label>
<select name="almoxarifado" class="form-control">
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
<input type="number" name="quantidade" class="form-control">
</div>

<div class="col-md-6">
<label class="label-title">Tipo de entrada</label>
<select name="tipo" class="form-control">
<option value="compra">🛒 Compra</option>
<option value="retorno">🔁 Retorno</option>
</select>
</div>

<div class="col-md-6">
<label class="label-title">Observação</label>
<input type="text" name="observacao" class="form-control">
</div>

</div>

<!-- 🟢 NOTA -->
<div id="nota" style="display:none;">

<div class="col-md-4">
<label>Nº Nota</label>
<input type="text" name="numero" class="form-control">
</div>

<div class="col-md-4">
<label>Fornecedor</label>
<input type="text" name="fornecedor" class="form-control">
</div>

<div class="col-md-4">
<label>Data</label>
<input type="date" name="data" class="form-control">
</div>

<hr>

<h5>Itens</h5>

<div id="itens">

<div class="row mb-2">

<div class="col-md-4">
<select name="produto_id[]" class="form-control">
<option value="">Produto</option>
<?php
$res = $conn->query("SELECT * FROM produtos WHERE ativo=1 ORDER BY nome ASC");
while($p = $res->fetch_assoc()){
    echo "<option value='{$p['id']}'>{$p['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-3">
<input type="number" name="quantidade[]" class="form-control" placeholder="Qtd">
</div>

<div class="col-md-3">
<select name="almoxarifado[]" class="form-control">
<?php
$res = $conn->query("SELECT * FROM almoxarifados");
while($a = $res->fetch_assoc()){
    echo "<option value='{$a['id']}'>{$a['nome']}</option>";
}
?>
</select>
</div>

<div class="col-md-2">
<button type="button" class="btn btn-success" onclick="addItem()">+</button>
</div>

</div>

</div>

</div>

<div class="col-12">
<button class="btn btn-success w-100 py-2">
📦 Registrar Entrada
</button>
</div>

</form>

</div>
</div>

<script>
function toggleModo(){
    let modo = document.getElementById('modo').value;

    document.getElementById('simples').style.display = (modo == 'simples') ? 'block' : 'none';
    document.getElementById('nota').style.display = (modo == 'nota') ? 'block' : 'none';
}

function addItem(){
    let html = document.querySelector("#itens .row").outerHTML;
    document.getElementById("itens").insertAdjacentHTML("beforeend", html);
}
</script>

<?php include_once("../assets/footer.php"); ?>