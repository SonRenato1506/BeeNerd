<?php
include_once('../Partial/config.php');
include_once("../Partial/header.php");

/* ===============================
   VALIDAÇÃO DO ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz_id = (int) $_GET['id'];

/* ===============================
   BUSCAR QUIZ
================================ */
$stmt = $conexao->prepare("SELECT * FROM personalidade WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz = $result->fetch_assoc();

/* ===============================
   RESULTADOS
================================ */
$resultados = [];
$res = $conexao->query(
    "SELECT * FROM personalidade_resultados WHERE personalidade_id = $quiz_id"
);
while ($r = $res->fetch_assoc()) {
    $resultados[$r['id']] = $r;
}

/* ===============================
   AÇÕES POST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== EXCLUIR QUIZ ===== */
    if (isset($_POST['excluir_quiz'])) {

        $conexao->query("
            DELETE prp FROM personalidade_respostas_pontuacao prp
            JOIN personalidade_respostas r ON r.id = prp.resposta_id
            JOIN personalidade_perguntas p ON p.id = r.pergunta_id
            WHERE p.personalidade_id = $quiz_id
        ");

        $conexao->query("
            DELETE r FROM personalidade_respostas r
            JOIN personalidade_perguntas p ON p.id = r.pergunta_id
            WHERE p.personalidade_id = $quiz_id
        ");

        $conexao->query("DELETE FROM personalidade_perguntas WHERE personalidade_id = $quiz_id");
        $conexao->query("DELETE FROM personalidade_resultados WHERE personalidade_id = $quiz_id");
        $conexao->query("DELETE FROM personalidade WHERE id = $quiz_id");

        header("Location: Quizzes.php");
        exit;
    }

    /* ===== EXCLUIR RESULTADO ===== */
    if (isset($_POST['excluir_resultado'])) {
        $rid = (int) $_POST['resultado_id'];

        $conexao->query("DELETE FROM personalidade_respostas_pontuacao WHERE resultado_id = $rid");
        $conexao->query("DELETE FROM personalidade_resultados WHERE id = $rid");

        header("Location: EditorPersonalidade.php?id=$quiz_id");
        exit;
    }

    /* ===== EXCLUIR PERGUNTA ===== */
    if (isset($_POST['excluir_pergunta'])) {
        $pid = (int) $_POST['pergunta_id'];

        $conexao->query("
            DELETE prp FROM personalidade_respostas_pontuacao prp
            JOIN personalidade_respostas r ON r.id = prp.resposta_id
            WHERE r.pergunta_id = $pid
        ");

        $conexao->query("DELETE FROM personalidade_respostas WHERE pergunta_id = $pid");
        $conexao->query("DELETE FROM personalidade_perguntas WHERE id = $pid");

        header("Location: EditorPersonalidade.php?id=$quiz_id");
        exit;
    }

    /* ===== EXCLUIR RESPOSTA ===== */
    if (isset($_POST['excluir_resposta'])) {
        $rid = (int) $_POST['resposta_id'];

        $conexao->query("DELETE FROM personalidade_respostas_pontuacao WHERE resposta_id = $rid");
        $conexao->query("DELETE FROM personalidade_respostas WHERE id = $rid");

        header("Location: EditorPersonalidade.php?id=$quiz_id");
        exit;
    }

    /* ===== SALVAR QUIZ ===== */
    if (isset($_POST['salvar_quiz'])) {
        $stmt = $conexao->prepare(
            "UPDATE personalidade SET titulo=?, descricao=?, imagem=? WHERE id=?"
        );
        $stmt->bind_param(
            "sssi",
            $_POST['titulo'],
            $_POST['descricao'],
            $_POST['imagem'],
            $quiz_id
        );
        $stmt->execute();
        header("Location: EditorPersonalidade.php?id=$quiz_id");
        exit;
    }

    /* ===== SALVAR PERGUNTAS ===== */
    if (isset($_POST['salvar_perguntas'])) {

        foreach ($_POST['pergunta'] as $pid => $texto) {

            $stmt = $conexao->prepare(
                "UPDATE personalidade_perguntas SET texto=? WHERE id=?"
            );
            $stmt->bind_param("si", $texto, $pid);
            $stmt->execute();

            foreach ($_POST['resposta'][$pid] as $rid => $textoResp) {

                $stmt = $conexao->prepare(
                    "UPDATE personalidade_respostas SET texto=? WHERE id=?"
                );
                $stmt->bind_param("si", $textoResp, $rid);
                $stmt->execute();

                foreach ($_POST['pontos'][$pid][$rid] as $resultado_id => $pontos) {
                    $stmt = $conexao->prepare(
                        "UPDATE personalidade_respostas_pontuacao
                         SET pontos=?
                         WHERE resposta_id=? AND resultado_id=?"
                    );
                    $stmt->bind_param("iii", $pontos, $rid, $resultado_id);
                    $stmt->execute();
                }
            }
        }
        header("Location: EditorPersonalidade.php?id=$quiz_id");
        exit;
    }
}

/* ===============================
   BUSCAR PERGUNTAS
================================ */
$perguntas = [];
$sql = "
    SELECT p.id pid, p.texto ptexto,
           r.id rid, r.texto rtexto
    FROM personalidade_perguntas p
    JOIN personalidade_respostas r ON r.pergunta_id = p.id
    WHERE p.personalidade_id = $quiz_id
    ORDER BY p.id
";
$res = $conexao->query($sql);

while ($row = $res->fetch_assoc()) {
    $pid = $row['pid'];

    if (!isset($perguntas[$pid])) {
        $perguntas[$pid] = [
            'texto' => $row['ptexto'],
            'respostas' => []
        ];
    }

    $pts = [];
    $pont = $conexao->query(
        "SELECT resultado_id, pontos
         FROM personalidade_respostas_pontuacao
         WHERE resposta_id = {$row['rid']}"
    );
    while ($p = $pont->fetch_assoc()) {
        $pts[$p['resultado_id']] = $p['pontos'];
    }

    $perguntas[$pid]['respostas'][$row['rid']] = [
        'texto' => $row['rtexto'],
        'pontos' => $pts
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Quiz de Personalidade</title>
    <link rel="stylesheet" href="../../Styles/EditorPersonalidade.css?v=3">
</head>

<body>

    <div class="container">

        <h2>Editar Quiz: <?= htmlspecialchars($quiz['titulo']) ?></h2>

        <form method="POST">
            <input type="hidden" name="salvar_quiz">
            <label>Título</label>
            <input type="text" name="titulo" value="<?= $quiz['titulo'] ?>">

            <label>Descrição</label>
            <textarea name="descricao"><?= $quiz['descricao'] ?></textarea>

            <label>Imagem</label>
            <input type="text" name="imagem" value="<?= $quiz['imagem'] ?>">

            <button>Salvar Quiz</button>
        </form>

        <form method="POST" onsubmit="return confirm('Excluir o quiz inteiro?');">
            <input type="hidden" name="excluir_quiz">
            <button class="btn-excluir">Excluir Quiz</button>
        </form>

        <hr>

        <?php foreach ($resultados as $res): ?>
            <form method="POST" class="resultado-linha">
                <span><?= $res['titulo'] ?></span>
                <input type="hidden" name="resultado_id" value="<?= $res['id'] ?>">
                <button name="excluir_resultado">Excluir Resultado</button>
            </form>
        <?php endforeach; ?>

        <hr>

        <form method="POST">
            <input type="hidden" name="salvar_perguntas">

            <?php foreach ($perguntas as $pid => $p): ?>
                <div class="pergunta">

                    <input type="text" name="pergunta[<?= $pid ?>]" value="<?= $p['texto'] ?>">

                    <form method="POST">
                        <input type="hidden" name="pergunta_id" value="<?= $pid ?>">
                        <button name="excluir_pergunta">Excluir Pergunta</button>
                    </form>

                    <?php foreach ($p['respostas'] as $rid => $r): ?>
                        <div class="opcao-personalidade">
                            <input type="text" name="resposta[<?= $pid ?>][<?= $rid ?>]" value="<?= $r['texto'] ?>">

                            <form method="POST">
                                <input type="hidden" name="resposta_id" value="<?= $rid ?>">
                            </form>

                            <?php foreach ($resultados as $res): ?>
                                <input type="number" name="pontos[<?= $pid ?>][<?= $rid ?>][<?= $res['id'] ?>]"
                                    value="<?= $r['pontos'][$res['id']] ?? 0 ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>

            <button>Salvar Perguntas</button>
        </form>

    </div>
</body>

</html>