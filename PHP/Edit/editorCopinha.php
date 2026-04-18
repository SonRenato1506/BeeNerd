<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   VALIDAÇÃO DE ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Copinha não encontrada.";
    exit;
}

$copinha_id = (int) $_GET['id'];

/* ===============================
   BUSCAR COPINHA
================================ */
$stmt = $conexao->prepare("SELECT * FROM copinha WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $copinha_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Copinha não encontrada.";
    exit;
}

$copinha = $result->fetch_assoc();

/* ===============================
   POST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* -------- SALVAR COPINHA -------- */
    if (isset($_POST['salvar_copinha'])) {
        $titulo = $_POST['titulo'];
        $imagem = $_POST['imagem'];

        $stmt = $conexao->prepare(
            "UPDATE copinha SET titulo=?, imagem=? WHERE id=?"
        );
        $stmt->bind_param("ssi", $titulo, $imagem, $copinha_id);
        $stmt->execute();

        header("Location: EditorCopinha.php?id=$copinha_id");
        exit;
    }

    /* -------- EXCLUIR ITEM -------- */
    if (isset($_POST['excluir_item'])) {
        $item_id = (int) $_POST['excluir_item'];

        $stmt = $conexao->prepare("
            DELETE FROM item_copinha 
            WHERE id = ? AND copinha_id = ?
        ");
        $stmt->bind_param("ii", $item_id, $copinha_id);
        $stmt->execute();

        header("Location: EditorCopinha.php?id=$copinha_id");
        exit;
    }

    /* -------- SALVAR ITENS -------- */
    if (isset($_POST['salvar_itens'])) {
        foreach ($_POST['item_nome'] as $item_id => $nome) {
            $imagem = $_POST['item_imagem'][$item_id];

            $stmt = $conexao->prepare("
                UPDATE item_copinha 
                SET nome=?, imagem=? 
                WHERE id=? AND copinha_id=?
            ");
            $stmt->bind_param("ssii", $nome, $imagem, $item_id, $copinha_id);
            $stmt->execute();
        }

        header("Location: EditorCopinha.php?id=$copinha_id");
        exit;
    }

    /* -------- ADICIONAR ITENS -------- */
    if (isset($_POST['adicionar_item'])) {

        if (!empty($_POST['novo']['nome'])) {
            foreach ($_POST['novo']['nome'] as $i => $nome) {
                if (trim($nome) === '')
                    continue;

                $imagem = $_POST['novo']['imagem'][$i] ?? '';

                $stmt = $conexao->prepare("
                    INSERT INTO item_copinha (copinha_id, nome, imagem)
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iss", $copinha_id, $nome, $imagem);
                $stmt->execute();
            }
        }

        header("Location: EditorCopinha.php?id=$copinha_id");
        exit;
    }

    /* -------- EXCLUIR COPINHA -------- */
    if (isset($_POST['excluir_copinha'])) {

        $stmt = $conexao->prepare("
            DELETE FROM item_copinha WHERE copinha_id = ?
        ");
        $stmt->bind_param("i", $copinha_id);
        $stmt->execute();

        $stmt = $conexao->prepare("
            DELETE FROM copinha WHERE id = ?
        ");
        $stmt->bind_param("i", $copinha_id);
        $stmt->execute();

        header("Location: ../Home/copinhas.php");
        exit;
    }
}

/* ===============================
   BUSCAR ITENS
================================ */
$itens = [];
$stmt = $conexao->prepare("
    SELECT id, nome, imagem 
    FROM item_copinha 
    WHERE copinha_id = ?
    ORDER BY id
");
$stmt->bind_param("i", $copinha_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $itens[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Copinha - <?= htmlspecialchars($copinha['titulo']) ?></title>
    <link rel="stylesheet" href="../../Styles/Criador.css?v=3">
</head>

<body>

    <div class="container">

        <h2>Editar Copinha</h2>

        <!-- COPINHA -->
        <form method="POST">
            <input type="hidden" name="salvar_copinha">

            <label>Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($copinha['titulo']) ?>" required>

            <label>Imagem</label>
            <input type="text" name="imagem" value="<?= htmlspecialchars($copinha['imagem']) ?>">

            <button type="submit">Salvar Copinha</button>
        </form>

        <hr>

        <!-- ITENS -->
        <h3>Itens</h3>

        <form method="POST">
            <input type="hidden" name="salvar_itens">

            <?php foreach ($itens as $item): ?>
                <div class="pergunta">

                    <label>Nome</label>
                    <input type="text" name="item_nome[<?= $item['id'] ?>]" value="<?= htmlspecialchars($item['nome']) ?>">

                    <label>Imagem</label>
                    <input type="text" name="item_imagem[<?= $item['id'] ?>]"
                        value="<?= htmlspecialchars($item['imagem']) ?>">

                    <button type="submit" name="excluir_item" value="<?= $item['id'] ?>"
                        onclick="return confirm('Excluir este item?')"
                        style="background:#c0392b;color:#fff;margin-top:10px">
                        🗑️ Excluir Item
                    </button>

                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-secundario">Salvar Itens</button>
        </form>

        <hr>

        <!-- ADICIONAR -->
        <h3>Adicionar Itens</h3>

        <form method="POST">
            <input type="hidden" name="adicionar_item">

            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="pergunta">
                    <input type="text" name="novo[nome][]" placeholder="Nome">
                    <input type="text" name="novo[imagem][]" placeholder="Imagem">
                </div>
            <?php endfor; ?>

            <button type="submit">➕ Adicionar</button>
        </form>

        <hr>

        <!-- EXCLUIR COPINHA -->
        <form method="POST">
            <input type="hidden" name="excluir_copinha">
            <button type="submit" onclick="return confirm('Excluir a copinha e TODOS os itens?')"
                style="background:#8e0000;color:#fff">
                ❌ Excluir Copinha
            </button>
        </form>

    </div>
</body>

</html>