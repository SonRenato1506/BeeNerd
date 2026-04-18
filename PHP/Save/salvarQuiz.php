<?php
session_start();
include_once("../Partial;/config.php");

if (!isset($_SESSION['id'])) {
    die("Usuário não logado.");
}

$criador = $_SESSION['id'];

$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$categoria = $_POST['categoria'];
$imagem = $_POST['imagem'];


/* =========================
   SALVAR QUIZ
========================= */

$sqlQuiz = "
INSERT INTO quizzes
(titulo, descricao, categoria, imagem, criador)
VALUES (?, ?, ?, ?, ?)
";

$stmt = $conexao->prepare($sqlQuiz);

$stmt->bind_param(
    "ssssi",
    $titulo,
    $descricao,
    $categoria,
    $imagem,
    $criador
);

$stmt->execute();

$quiz_id = $stmt->insert_id;


/* =========================
   SALVAR PERGUNTAS
========================= */

foreach ($_POST['perguntas'] as $p) {

    $sqlPerg = "
    INSERT INTO perguntas
    (quizz_id, texto)
    VALUES (?, ?)
    ";

    $stmtP = $conexao->prepare($sqlPerg);

    $stmtP->bind_param(
        "is",
        $quiz_id,
        $p['texto']
    );

    $stmtP->execute();

    $pergunta_id = $stmtP->insert_id;


    /* =========================
       SALVAR RESPOSTAS
    ========================= */

    foreach ($p['respostas'] as $i => $r) {

        $correta = ($p['correta'] == $i) ? 1 : 0;

        $sqlResp = "
        INSERT INTO respostas
        (pergunta_id, texto, correta)
        VALUES (?, ?, ?)
        ";

        $stmtR = $conexao->prepare($sqlResp);

        $stmtR->bind_param(
            "isi",
            $pergunta_id,
            $r['texto'],
            $correta
        );

        $stmtR->execute();
    }
}


echo "<script>
alert('Quiz criado com sucesso!');
location.href='../Home/Quizzes.php';
</script>";