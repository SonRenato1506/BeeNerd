<?php
session_start();

include_once("../Partial/config.php");
include_once("../Partial/header.php");

if (!isset($_SESSION['id'])) {
    die("Login necessário");
}

if ($_POST) {

    $criador = $_SESSION['id'];

    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $imagem = $_POST['imagem'];
    $categoria = $_POST['categoria'];

    $conexao->begin_transaction();

    /* QUIZ */

    $stmt = $conexao->prepare(
        "INSERT INTO personalidade
        (titulo, descricao, imagem, categoria, criador)
        VALUES (?,?,?,?,?)"
    );

    $stmt->bind_param(
        "ssssi",
        $titulo,
        $descricao,
        $imagem,
        $categoria,
        $criador
    );

    $stmt->execute();

    $quiz_id = $stmt->insert_id;


    /* RESULTADOS */

    $resultadoIds = [];

    foreach ($_POST['resultados'] as $r) {

        $stmt = $conexao->prepare(
            "INSERT INTO personalidade_resultados
            (personalidade_id, titulo)
            VALUES (?,?)"
        );

        $stmt->bind_param(
            "is",
            $quiz_id,
            $r
        );

        $stmt->execute();

        $resultadoIds[] = $stmt->insert_id;
    }


    /* PERGUNTAS */

    foreach ($_POST['perguntas'] as $p) {

        $stmt = $conexao->prepare(
            "INSERT INTO personalidade_perguntas
            (personalidade_id, texto)
            VALUES (?,?)"
        );

        $stmt->bind_param(
            "is",
            $quiz_id,
            $p['texto']
        );

        $stmt->execute();

        $pergunta_id = $stmt->insert_id;


        foreach ($p['respostas'] as $resp) {

            $stmt = $conexao->prepare(
                "INSERT INTO personalidade_respostas
                (pergunta_id, texto)
                VALUES (?,?)"
            );

            $stmt->bind_param(
                "is",
                $pergunta_id,
                $resp['texto']
            );

            $stmt->execute();

            $resposta_id = $stmt->insert_id;

            $resultado_id = $resultadoIds[$resp['resultado']];

            $stmt = $conexao->prepare(
                "INSERT INTO personalidade_respostas_pontuacao
                (resposta_id, resultado_id, pontos)
                VALUES (?,?,?)"
            );

            $stmt->bind_param(
                "iii",
                $resposta_id,
                $resultado_id,
                $resp['pontos']
            );

            $stmt->execute();
        }
    }

    $conexao->commit();

    echo "<script>alert('Quiz criado!');</script>";
}
?>

<style>

.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

input, textarea, select {

    width: 100%;
    padding: 8px;
    margin: 5px 0 10px 0;

}

button {

    padding: 8px 12px;
    margin: 5px;
    cursor: pointer;
}

.box {

    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
}

h2 {

    margin-top: 30px;
}

</style>


<div class="container">

<form method="post">

<h2>Quiz</h2>

<input name="titulo" placeholder="Título" required>

<input name="descricao" placeholder="Descrição">

<input name="imagem" placeholder="Imagem">

<select name="categoria">

<option>Anime</option>
<option>Games</option>
<option>Filmes</option>
<option>Series</option>
<option>Livros</option>
<option>Variados</option>

</select>


<h2>Resultados</h2>

<div id="resultados"></div>

<button type="button" onclick="addResultado()">
+ Resultado
</button>



<h2>Perguntas</h2>

<div id="perguntas"></div>

<button type="button" onclick="addPergunta()">
+ Pergunta
</button>


<br><br>

<button>Salvar Quiz</button>

</form>

</div>



<script>

let r = 0;
let p = 0;

function addResultado() {

    document.getElementById("resultados").innerHTML += `

    <div class="box">

    Resultado:
    <input name="resultados[]">

    </div>

    `;

}


function addPergunta() {

    let id = p++;

    document.getElementById("perguntas").innerHTML += `

    <div class="box">

    Pergunta:
    <input name="perguntas[${id}][texto]">

    <div id="resp${id}"></div>

    <button type="button"
    onclick="addResposta(${id})">

    + Resposta

    </button>

    </div>

    `;

}


function addResposta(id) {

    document.getElementById("resp"+id).innerHTML += `

    <div class="box">

    Resposta:
    <input name="perguntas[${id}][respostas][][texto]">

    Resultado index:
    <input name="perguntas[${id}][respostas][][resultado]">

    Pontos:
    <input name="perguntas[${id}][respostas][][pontos]">

    </div>

    `;

}

</script>


<?php include_once("../Partial/footer.php"); ?>