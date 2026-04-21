<?php 
include("../auth.php");
include("../config/db.php");
include("../assets/layout.php");

// 🔥 EXCLUIR
if(isset($_GET['excluir'])){
    $id = intval($_GET['excluir']);

    $check = $conn->query("
    SELECT COUNT(*) as total 
    FROM movimentacoes 
    WHERE produto_id = $id
    ");

    if($check->fetch_assoc()['total'] > 0){
        header("Location: listar.php?erro=movimentacao");
        exit;
    }

    $conn->query("DELETE FROM produtos WHERE id = $id");

    header("Location: listar.php?sucesso=excluido");
    exit;
}
?>

<div class="container-fluid">

<div class="card p-4">

<h4 class="mb-3">📦 Produtos</h4>

<!-- MENSAGENS -->
<?php if(isset($_GET['sucesso'])): ?>
<div class="alert alert-success">✔ Operação realizada com sucesso</div>
<?php endif; ?>

<?php if(isset($_GET['erro']) && $_GET['erro']=='movimentacao'): ?>
<div class="alert alert-danger">
❌ Produto possui movimentações e não pode ser excluído
</div>
<?php endif; ?>

<a href="cadastrar.php" class="btn btn-primary mb-3">
➕ Novo Produto
</a>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<tr class="table-light text-center">
    <th>Imagem</th>
    <th>Produto</th>
    <th>Saldo</th>
    <th>Estoque Mínimo</th>
    <th>Ações</th>
</tr>

<?php
$res = $conn->query("
SELECT 
    p.*,
    IFNULL(SUM(e.quantidade),0) as saldo
FROM produtos p
LEFT JOIN estoque_local e ON e.produto_id = p.id
GROUP BY p.id
ORDER BY p.nome ASC
");

if($res->num_rows == 0){
    echo "<tr><td colspan='5' class='text-center'>Nenhum produto cadastrado</td></tr>";
}

while($p = $res->fetch_assoc()){

    $saldo = $p['saldo'];
    $minimo = $p['estoque_minimo'];

    // 🔥 CORES
    if($saldo <= 0){
        $cor = "#f8d7da";
    } elseif($saldo <= $minimo){
        $cor = "#fff3cd";
    } else {
        $cor = "";
    }

    // 🔥 IMAGEM
    $caminho = "../uploads/".$p['imagem'];

    if(!empty($p['imagem']) && file_exists($caminho)){
        $img = "<img src='$caminho' 
                     style='width:50px;height:50px;object-fit:cover;border-radius:6px;cursor:pointer;'
                     onclick=\"ampliarImagem('$caminho')\">";
    } else {
        $img = "<span class='text-muted'>Sem imagem</span>";
    }

    echo "<tr>

        <td style='background:$cor' class='text-center'>$img</td>
        <td style='background:$cor'>{$p['nome']}</td>
        <td style='background:$cor' class='text-center'><strong>{$saldo}</strong></td>
        <td style='background:$cor' class='text-center'>{$minimo}</td>

        <td style='background:$cor' class='text-center'>

            <a href='editar.php?id={$p['id']}' 
               class='btn btn-sm btn-warning'>
               ✏️
            </a>

            <a href='?excluir={$p['id']}' 
               class='btn btn-sm btn-danger'
               onclick=\"return confirm('Tem certeza?')\">
               🗑️
            </a>

        </td>

    </tr>";
}
?>

</table>
</div>

</div>

</div>

<!-- 🔍 MODAL IMAGEM -->
<div class="modal fade" id="modalImagem" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark border-0">
      <div class="modal-body text-center p-0">
        <img id="imgGrande" src="" style="width:100%;">
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function ampliarImagem(src){
    document.getElementById('imgGrande').src = src;
    var modal = new bootstrap.Modal(document.getElementById('modalImagem'));
    modal.show();
}
</script>

<?php include("../assets/footer.php"); ?>