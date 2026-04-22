<?php 
include("../auth.php");
include("../config/db.php");

$id = intval($_GET['id'] ?? 0);

// 🔎 BUSCAR PRODUTO
$res = $conn->query("SELECT * FROM produtos WHERE id = $id");
$produto = $res->fetch_assoc();

if(!$produto){
    die("Produto não encontrado");
}

// 🔥 PROCESSAR POST (ANTES DO LAYOUT)
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        die("Token CSRF inválido");
    }

    $nome = $conn->real_escape_string($_POST['nome']);
    $estoque_minimo = intval($_POST['estoque_minimo']);
    $unidade = $conn->real_escape_string($_POST['unidade']);

    // 🔥 MANTÉM IMAGEM ATUAL
    $imagem_nome = $produto['imagem'];

    // 🔥 NOVO UPLOAD
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        $mime = mime_content_type($_FILES['foto']['tmp_name']);
        $mimes_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $tamanho_maximo = 2 * 1024 * 1024; // 2MB

        if(in_array($ext, $permitidas) && in_array($mime, $mimes_permitidos) && $_FILES['foto']['size'] <= $tamanho_maximo){

            $nova_imagem = uniqid().".".$ext;
            $destino = "../uploads/".$nova_imagem;

            if(is_uploaded_file($_FILES['foto']['tmp_name']) && move_uploaded_file($_FILES['foto']['tmp_name'], $destino)){

                // 🔥 REMOVE IMAGEM ANTIGA
                if(!empty($produto['imagem'])){
                    $antiga = "../uploads/".$produto['imagem'];
                    if(file_exists($antiga)){
                        unlink($antiga);
                    }
                }

                $imagem_nome = $nova_imagem;
            }
        }
    }

    // 🔥 UPDATE
    $conn->query("
    UPDATE produtos SET
        nome = '$nome',
        estoque_minimo = $estoque_minimo,
        unidade = '$unidade',
        imagem = '$imagem_nome'
    WHERE id = $id
    ");

    header("Location: listar.php?sucesso=1");
    exit;
}

// 👇 LAYOUT
include("../assets/layout.php");
?>

<div class="container-fluid">

<div class="card p-4">

<h4 class="mb-3">✏️ Editar Produto</h4>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

<div class="mb-3">
<label>Nome do Produto</label>
<input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($produto['nome']) ?>" required>
</div>

<div class="mb-3">
<label>Estoque Mínimo</label>
<input type="number" name="estoque_minimo" class="form-control" value="<?= $produto['estoque_minimo'] ?>">
</div>

<div class="mb-3">
<label>Unidade</label>
<select name="unidade" class="form-control">
    <option value="un" <?= $produto['unidade']=='un'?'selected':'' ?>>Unidade</option>
    <option value="metro" <?= $produto['unidade']=='metro'?'selected':'' ?>>Metro</option>
    <option value="kg" <?= $produto['unidade']=='kg'?'selected':'' ?>>Kg</option>
    <option value="litro" <?= $produto['unidade']=='litro'?'selected':'' ?>>Litro</option>
</select>
</div>

<div class="mb-3">
<label>Imagem Atual</label><br>

<?php 
$caminho = "../uploads/".$produto['imagem'];

if(!empty($produto['imagem']) && file_exists($caminho)): ?>
    <img src="<?= $caminho ?>" 
         style="width:80px;height:80px;object-fit:cover;border-radius:10px;">
<?php else: ?>
    <span class="text-muted">Sem imagem</span>
<?php endif; ?>

</div>

<div class="mb-3">
<label>Trocar Imagem</label>
<input type="file" name="foto" class="form-control" accept="image/*">
</div>

<button class="btn btn-primary w-100">
💾 Salvar Alterações
</button>

</form>

</div>

</div>

<?php include("../assets/footer.php"); ?>