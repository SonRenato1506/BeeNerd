<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   VALIDAÇÃO DE ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz_id = (int) $_GET['id'];

/* ===============================
   BUSCAR QUIZ
================================ */
$stmt = $conexao->prepare("SELECT * FROM quizzes WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz = $result->fetch_assoc();

/* ===============================
   EXCLUIR QUIZ COMPLETO
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_quiz'])) {

    $stmt = $conexao->prepare("
        DELETE r FROM respostas r
        INNER JOIN perguntas p ON r.pergunta_id = p.id
        WHERE p.quizz_id = ?
    ");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    $stmt = $conexao->prepare("DELETE FROM perguntas WHERE quizz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    $stmt = $conexao->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    header("Location: Quizzes.php");
    exit;
}

/* ===============================
   EXCLUIR PERGUNTA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_pergunta'])) {

    $pergunta_id = (int) $_POST['excluir_pergunta'];

    $stmt = $conexao->prepare("DELETE FROM respostas WHERE pergunta_id = ?");
    $stmt->bind_param("i", $pergunta_id);
    $stmt->execute();

    $stmt = $conexao->prepare("DELETE FROM perguntas WHERE id = ?");
    $stmt->bind_param("i", $pergunta_id);
    $stmt->execute();

    header("Location: EditorQuiz.php?id=$quiz_id");
    exit;
}

/* ===============================
   SALVAR QUIZ
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_quiz'])) {

    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $imagem = $_POST['imagem'];

    $stmt = $conexao->prepare("
        UPDATE quizzes SET 
            titulo=?, descricao=?, categoria=?, imagem=?
        WHERE id=?
    ");
    $stmt->bind_param("ssssi", $titulo, $descricao, $categoria, $imagem, $quiz_id);
    $stmt->execute();

    header("Location: EditorQuiz.php?id=$quiz_id");
    exit;
}

/* ===============================
   SALVAR PERGUNTAS E RESPOSTAS
================================ */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['salvar_perguntas']) &&
    !empty($_POST['pergunta'])
) {

    foreach ($_POST['pergunta'] as $pid => $pergunta_texto) {

        $stmt = $conexao->prepare("UPDATE perguntas SET texto=? WHERE id=?");
        $stmt->bind_param("si", $pergunta_texto, $pid);
        $stmt->execute();

        $resposta_correta_id = $_POST['correta'][$pid] ?? 0;

        foreach ($_POST['resposta_texto'][$pid] as $rid => $resposta_texto) {

            $correta = ($rid == $resposta_correta_id) ? 1 : 0;

            $stmt = $conexao->prepare("
                UPDATE respostas SET texto=?, correta=? WHERE id=?
            ");
            $stmt->bind_param("sii", $resposta_texto, $correta, $rid);
            $stmt->execute();
        }
    }

    header("Location: EditorQuiz.php?id=$quiz_id");
    exit;
}

/* ===============================
   ADICIONAR PERGUNTA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_pergunta'])) {

    $stmt = $conexao->prepare("INSERT INTO perguntas (quizz_id, texto) VALUES (?, '')");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();

    $nova_pergunta_id = $conexao->insert_id;

    $stmt = $conexao->prepare("
        INSERT INTO respostas (pergunta_id, texto, correta) 
        VALUES (?, '', 0)
    ");

    for ($i = 0; $i < 4; $i++) {
        $stmt->bind_param("i", $nova_pergunta_id);
        $stmt->execute();
    }

    header("Location: EditorQuiz.php?id=$quiz_id");
    exit;
}

/* ===============================
   BUSCAR PERGUNTAS
================================ */
$perguntas = [];

$sql = "
    SELECT p.id AS pergunta_id, p.texto AS pergunta_texto,
           r.id AS resposta_id, r.texto AS resposta_texto, r.correta
    FROM perguntas p
    JOIN respostas r ON r.pergunta_id = p.id
    WHERE p.quizz_id = ?
    ORDER BY p.id
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $pid = $row['pergunta_id'];

    if (!isset($perguntas[$pid])) {
        $perguntas[$pid] = [
            'texto' => $row['pergunta_texto'],
            'respostas' => []
        ];
    }

    $perguntas[$pid]['respostas'][$row['resposta_id']] = [
        'texto' => $row['resposta_texto'],
        'correta' => $row['correta']
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Quiz</title>
    <link rel="stylesheet" href="../../Styles/Criador.css?v=5">
</head>

<body>

<div class="container">

    <h2>Editar Quiz: <?= htmlspecialchars($quiz['titulo']) ?></h2>

    <!-- FORM QUIZ -->
    <form method="POST">
        <input type="hidden" name="salvar_quiz">

        <label>Título</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($quiz['titulo']) ?>" required>

        <label>Descrição</label>
        <textarea name="descricao"><?= htmlspecialchars($quiz['descricao']) ?></textarea>

        <label>Categoria</label>
        <input type="text" name="categoria" value="<?= htmlspecialchars($quiz['categoria']) ?>">

        <label>Imagem</label>
        <input type="text" name="imagem" value="<?= htmlspecialchars($quiz['imagem']) ?>">

        <button type="submit">Salvar Quiz</button>

        <button type="submit"
                name="excluir_quiz"
                style="background:#c0392b;color:#fff"
                onclick="return confirm('Excluir este quiz permanentemente?')">
            Excluir Quiz
        </button>
    </form>

    <hr>

    <h3>Perguntas</h3>

    <!-- BOTÃO ADICIONAR -->
    <form method="POST">
        <button type="submit" 
                name="adicionar_pergunta" 
                class="btn-secundario">
            + Adicionar Pergunta
        </button>
    </form>

    <!-- FORM PRINCIPAL -->
    <form method="POST">
    <input type="hidden" name="salvar_perguntas">

    <?php foreach ($perguntas as $pid => $p): ?>
        <div class="pergunta">

            <input type="text"
                   name="pergunta[<?= $pid ?>]"
                   value="<?= htmlspecialchars($p['texto']) ?>"
                   required>

            <?php foreach ($p['respostas'] as $rid => $r): ?>
                <div class="opcao">
                    <input type="text"
                           name="resposta_texto[<?= $pid ?>][<?= $rid ?>]"
                           value="<?= htmlspecialchars($r['texto']) ?>"
                           required>

                    <label>
                        <input type="radio"
                               name="correta[<?= $pid ?>]"
                               value="<?= $rid ?>"
                               <?= $r['correta'] ? 'checked' : '' ?>>
                        Correta
                    </label>
                </div>
            <?php endforeach; ?>

            <button type="submit"
                    name="excluir_pergunta"
                    value="<?= $pid ?>"
                    class="btn-excluir"
                    onclick="return confirm('Excluir esta pergunta?')">
                Excluir Pergunta
            </button>

        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn-secundario">
        Salvar Perguntas
    </button>

    </form>

</div>

</body>
</html>