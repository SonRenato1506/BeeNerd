<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   VALIDAR ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Notícia inválida.");
}

$id = (int) $_GET['id'];
$mensagem = "";

/* ===============================
   EXCLUIR NOTÍCIA
================================ */
if (isset($_POST['excluir'])) {

    $stmtDelete = $conexao->prepare(
        "DELETE FROM noticias WHERE id = ?"
    );
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        header("Location: Noticias.php?msg=excluida");
        header("Location: noticias.php");
        exit;
    } else {
        $mensagem = "Erro ao excluir a notícia.";
    }
}


/* ===============================
   ATUALIZAR NOTÍCIA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = $_POST['titulo'];
    $texto = $_POST['texto'];
    $imagem = $_POST['imagem'];
    $categoria = $_POST['categoria'];
    $palavrachave = $_POST['palavrachave'];

    $stmtUpdate = $conexao->prepare(
        "UPDATE noticias 
         SET titulo = ?, texto = ?, imagem = ?, categoria = ?, palavrachave = ?
         WHERE id = ?"
    );
    $stmtUpdate->bind_param(
        "sssssi",
        $titulo,
        $texto,
        $imagem,
        $categoria,
        $palavrachave,
        $id
    );

    if ($stmtUpdate->execute()) {
        $mensagem = "Notícia atualizada com sucesso!";
        header("Location: ../Home/noticias.php");
        exit;

    } else {
        $mensagem = "Erro ao atualizar a notícia.";
    }
}

/* ===============================
   BUSCAR NOTÍCIA
================================ */
$stmt = $conexao->prepare(
    "SELECT * FROM noticias WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Notícia não encontrada.");
}

$noticia = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Notícia - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: #fff;
            padding-top: 100px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #1c1c1c;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            text-align: left;

        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: none;
        }

        textarea {
            min-height: 200px;
            resize: vertical;
        }

        #salvar {
            margin-top: 20px;
            padding: 12px 20px;
            background: #ff2d2d;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .mensagem {
            margin-bottom: 20px;
            padding: 10px;
            background: #0a3;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Editar Notícia</h1>

        <?php if ($mensagem): ?>
            <div class="mensagem"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="post">

            <label>Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" required>

            <label>Palavra-chave (URL)</label>
            <input type="text" name="palavrachave" value="<?= htmlspecialchars($noticia['palavrachave']) ?>" required>

            <label>Imagem (URL)</label>
            <input type="text" name="imagem" value="<?= htmlspecialchars($noticia['imagem']) ?>">

            <label>Categoria</label>
            <select name="categoria" required>
                <?php
                $categorias = [
                    "Animes",
                    "Jogos",
                    "Livros",
                    "RPG",
                    "Series/Filmes",
                    "Tecnologia"
                ];

                foreach ($categorias as $cat):
                    ?>
                    <option value="<?= $cat ?>" <?= $noticia['categoria'] === $cat ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Texto da Notícia</label>
            <textarea name="texto" required><?= htmlspecialchars($noticia['texto']) ?></textarea>

            <button id="salvar" type="submit">Salvar Alterações</button>

            <button type="submit" name="excluir"
                onclick="return confirm('Tem certeza que deseja excluir esta notícia? Essa ação não pode ser desfeita!')"
                style="
        margin-top: 10px;
        padding: 12px 20px;
        background: #900;
        border: none;
        color: #fff;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        width: 20%;
    ">
                Excluir Notícia
            </button>

        </form>
    </div>

</body>

</html>