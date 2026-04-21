<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $numero = $_POST['numero'];
    $fornecedor = $_POST['fornecedor'];
    $data = $_POST['data'];

    // 🔥 SALVA NOTA
    $conn->query("
        INSERT INTO notas_entrada (numero, fornecedor, data)
        VALUES ('$numero', '$fornecedor', '$data')
    ");

    $nota_id = $conn->insert_id;

    foreach($_POST['produto_id'] as $i => $produto_id){

        $qtd = intval($_POST['quantidade'][$i]);
        $almox = intval($_POST['almoxarifado_id'][$i]);

        if($produto_id && $qtd > 0){

            // ITEM
            $conn->query("
                INSERT INTO notas_itens (nota_id, produto_id, quantidade)
                VALUES ($nota_id, $produto_id, $qtd)
            ");

            // MOVIMENTAÇÃO
            $conn->query("
                INSERT INTO movimentacoes 
                (produto_id, quantidade, tipo, almoxarifado_id, data_movimentacao)
                VALUES ($produto_id, $qtd, 'entrada', $almox, NOW())
            ");

            // ESTOQUE
            $conn->query("
                INSERT INTO estoque_local (produto_id, almoxarifado_id, quantidade)
                VALUES ($produto_id, $almox, $qtd)
            ");
        }
    }

    echo "<div class='alert alert-success'>Nota lançada com sucesso</div>";
}
?>

<div class="container-fluid">
<div class="card p-4">

<h4>📥 Entrada por Nota Fiscal</h4>

<form method="POST">

<div class="row">

<div class="col-md-4">
<label>Nº Nota</label>
<input type="text" name="numero" class="form-control" required>
</div>

<div class="col-md-4">
<label>Fornecedor</label>
<input type="text" name="fornecedor" class="form-control" required>
</div>

<div class="col-md-4">
<label>Data</label>
<input type="date" name="data" class="form-control" required>
</div>

</div>

<hr>

<h5>Itens</h5>

<div id="itens">

<div class="row mb-2">

<div class="col-md-4">
<select name="produto_id[]" class="form-control">
<option value="">Produto</option>
<?php
$res = $conn->query("SELECT * FROM produtos ORDER BY nome ASC");
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
<select name="almoxarifado_id[]" class="form-control">
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

<button class="btn btn-primary mt-3">Salvar Nota</button>

</form>

</div>
</div>

<script>
function addItem(){

    let html = document.querySelector("#itens .row").outerHTML;

    document.getElementById("itens").insertAdjacentHTML("beforeend", html);
}
</script>

<?php include("../assets/footer.php"); ?>