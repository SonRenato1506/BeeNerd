<?php
session_start();
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
   QUIZ PRINCIPAL + CRIADOR
================================ */
$sqlQuiz = "
SELECT p.*, u.nome AS criador_nome
FROM personalidade p
JOIN usuarios u ON u.id = p.criador
WHERE p.id = $quiz_id
LIMIT 1
";

$resultQuiz = $conexao->query($sqlQuiz);

if (!$resultQuiz || $resultQuiz->num_rows === 0) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz = $resultQuiz->fetch_assoc();


/* ===============================
   PERMISSÃO EDITAR
================================ */
$podeEditar = false;

if (isset($_SESSION['id'])) {

    if (
        $_SESSION['id'] == $quiz['criador']
        || (isset($_SESSION['adm']) && $_SESSION['adm'] == 1)
    ) {
        $podeEditar = true;
    }

}


/* ===============================
   RESULTADOS POSSÍVEIS
================================ */
$sqlResultados = "
    SELECT * FROM personalidade_resultados
    WHERE personalidade_id = $quiz_id
";
$resultResultados = $conexao->query($sqlResultados);

$resultados = [];

if ($resultResultados && $resultResultados->num_rows > 0) {
    while ($r = $resultResultados->fetch_assoc()) {
        $resultados[$r['id']] = $r;
    }
}


/* ===============================
   PERGUNTAS E RESPOSTAS
================================ */
$sqlPerguntas = "
    SELECT 
        p.id    AS pergunta_id,
        p.texto AS pergunta_texto,
        r.id    AS resposta_id,
        r.texto AS resposta_texto
    FROM personalidade_perguntas p
    JOIN personalidade_respostas r ON r.pergunta_id = p.id
    WHERE p.personalidade_id = $quiz_id
    ORDER BY p.id
";

$resultPerguntas = $conexao->query($sqlPerguntas);


/* ===============================
   ORGANIZAÇÃO
================================ */
$perguntas = [];

if ($resultPerguntas && $resultPerguntas->num_rows > 0) {

    while ($row = $resultPerguntas->fetch_assoc()) {

        $pid = $row['pergunta_id'];

        if (!isset($perguntas[$pid])) {
            $perguntas[$pid] = [
                'id' => $pid,
                'texto' => $row['pergunta_texto'],
                'respostas' => []
            ];
        }

        $sqlPont = "
            SELECT resultado_id, pontos
            FROM personalidade_respostas_pontuacao
            WHERE resposta_id = {$row['resposta_id']}
        ";

        $pontData = $conexao->query($sqlPont);

        $pontos = [];

        if ($pontData && $pontData->num_rows > 0) {

            while ($p = $pontData->fetch_assoc()) {
                $pontos[$p['resultado_id']] = (int) $p['pontos'];
            }

        }

        $perguntas[$pid]['respostas'][] = [
            'id' => $row['resposta_id'],
            'texto' => $row['resposta_texto'],
            'pontos' => $pontos
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['titulo']) ?> - DnNerds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../Styles/quiz.css?v=1">

</head>

<body>

    <main class="conteudo">

        <article class="quiz">

            <img class="quiz_img" src="../<?= htmlspecialchars($quiz['imagem'] ?: '../../Imagens/quizdefault.jpg') ?>">

            <p><?= htmlspecialchars($quiz['descricao']) ?></p>

            <div id="quiz-container"></div>

        </article>

    </main>


    <?php if ($podeEditar): ?>

        <a href="../Edit/editorPersonalidade.php?id=<?= $quiz['id'] ?>">
            <button id="editor">
                Edite esse Quiz
            </button>
        </a>

    <?php endif; ?>


    <script>

        const perguntas = <?= json_encode(array_values($perguntas)) ?>;
        const resultados = <?= json_encode($resultados) ?>;

        let indice = 0;
        let pontos = {};

        const container = document.getElementById("quiz-container");

        const cores = ["ps-blue", "ps-pink", "ps-red", "ps-green"];


        function mostrarPergunta() {

            container.innerHTML = "";

            const pergunta = perguntas[indice];

            const h2 = document.createElement("h2");

            h2.textContent = pergunta.texto;

            container.appendChild(h2);


            pergunta.respostas.forEach((resposta, i) => {

                const btn = document.createElement("button");

                btn.textContent = resposta.texto;

                btn.classList.add(cores[i % cores.length]);


                btn.onclick = () => {

                    container.querySelectorAll("button")
                        .forEach(b => b.disabled = true);


                    for (const resultado_id in resposta.pontos) {

                        pontos[resultado_id] =
                            (pontos[resultado_id] || 0)
                            + resposta.pontos[resultado_id];

                    }


                    setTimeout(() => {

                        indice++;

                        indice < perguntas.length
                            ? mostrarPergunta()
                            : mostrarResultado();

                    }, 600);

                };

                container.appendChild(btn);

            });

        }


        function mostrarResultado() {

            let melhorResultado = null;
            let maiorPontuacao = -Infinity;


            for (const id in pontos) {

                if (pontos[id] > maiorPontuacao) {

                    maiorPontuacao = pontos[id];

                    melhorResultado = id;

                }

            }


            const r = resultados[melhorResultado];


            container.innerHTML = `

<h2>${r.titulo}</h2>

<img
src="../${r.imagem}"
class="quiz_img"
style="max-width:300px;border-radius:20px;"
>

<p>${r.descricao}</p>

<button onclick="location.reload()">
Refazer
</button>

<button onclick="history.back()">
Voltar
</button>

`;

        }


        perguntas.length > 0
            ? mostrarPergunta()
            : container.innerHTML = "<h2>Sem perguntas</h2>";

    </script>

</body>

</html>