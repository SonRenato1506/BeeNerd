<?php
session_start();
include_once('../Partial/config.php');
include_once("../Partial/header.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz_id = (int) $_GET['id'];

$sqlQuiz = "
SELECT q.*, u.nome AS criador_nome
FROM quizzes q
JOIN usuarios u ON u.id = q.criador
WHERE q.id = $quiz_id
LIMIT 1
";
$resultQuiz = $conexao->query($sqlQuiz);

if (!$resultQuiz || $resultQuiz->num_rows === 0) {
    echo "Quiz não encontrado.";
    exit;
}

$quiz = $resultQuiz->fetch_assoc();

$podeEditar = false;

if (isset($_SESSION['id'])) {
    if (
        $_SESSION['id'] == $quiz['criador']
        || (isset($_SESSION['adm']) && $_SESSION['adm'] == 1)
    ) {
        $podeEditar = true;
    }
}

/* PERGUNTAS */
$sqlPerguntas = "
    SELECT 
        p.id   AS pergunta_id,
        p.texto AS pergunta_texto,
        r.texto AS resposta_texto,
        r.correta
    FROM perguntas p
    JOIN respostas r ON r.pergunta_id = p.id
    WHERE p.quizz_id = $quiz_id
    ORDER BY p.id
";

$resultPerguntas = $conexao->query($sqlPerguntas);

$perguntas = [];

if ($resultPerguntas && $resultPerguntas->num_rows > 0) {
    while ($row = $resultPerguntas->fetch_assoc()) {
        $pid = $row['pergunta_id'];

        if (!isset($perguntas[$pid])) {
            $perguntas[$pid] = [
                'texto' => $row['pergunta_texto'],
                'respostas' => []
            ];
        }

        $perguntas[$pid]['respostas'][] = [
            'texto' => $row['resposta_texto'],
            'correta' => $row['correta']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['titulo']) ?></title>
    <link rel="stylesheet" href="../../Styles/quiz.css?v=5">
</head>

<body>

<main class="conteudo">
    <article class="quiz">
        <img class="quiz_img" src="../<?= htmlspecialchars($quiz['imagem']) ?>">

        <div class="barra">
            <div id="barra-progresso"></div>
        </div>

        <p><?= htmlspecialchars($quiz['descricao']) ?></p>

        <div id="quiz-container"></div>
    </article>
</main>

<?php if ($podeEditar): ?>
<a href="../Edit/editorQuiz.php?id=<?= $quiz['id'] ?>">
    <button id="editor">Edite esse Quiz</button>
</a>
<?php endif; ?>

<script>

// ===========================
// 🔊 SONS
// ===========================
const somAcerto = new Audio("../../Audios/acerto.mp3");
const somErro = new Audio("../../Audios/erro.mp3");

// ===========================
// 🔔 FILA DE NOTIFICAÇÕES
// ===========================
let notif;
let filaNotificacoes = [];
let exibindo = false;

document.addEventListener("DOMContentLoaded", () => {

    notif = document.createElement("div");
    notif.id = "notificacao";
    notif.className = "notificacao";

    notif.innerHTML = `
        <div class="notif-icon">⭐</div>
        <div>
            <strong id="notif-titulo"></strong>
            <p id="notif-desc"></p>
        </div>
    `;

    document.body.appendChild(notif);
});

function adicionarNotificacao({ titulo, descricao, icone = "⭐", duracao = 2500 }) {

    filaNotificacoes.push({ titulo, descricao, icone, duracao });

    if (!exibindo) {
        processarFila();
    }
}

function processarFila() {

    if (filaNotificacoes.length === 0) {
        exibindo = false;
        return;
    }

    exibindo = true;

    const { titulo, descricao, icone, duracao } = filaNotificacoes.shift();

    notif.querySelector("#notif-titulo").textContent = titulo;
    notif.querySelector("#notif-desc").textContent = descricao;
    notif.querySelector(".notif-icon").textContent = icone;

    notif.classList.add("mostrar");

    setTimeout(() => {
        notif.classList.remove("mostrar");

        setTimeout(() => {
            processarFila();
        }, 300);

    }, duracao);
}

// ===========================
// ⭐ XP + EVENTOS
// ===========================
async function registrarEvento(tipo, referenciaID = null){

    try{
        const response = await fetch("../Partial/registrar_evento.php",{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:`tipo=${tipo}&referencia_id=${referenciaID}`
        });

        const data = await response.json();

        // 🔥 LEVEL UP
        if(data.subiu_level){
            adicionarNotificacao({
                titulo: "LEVEL UP!",
                descricao: "Nível " + data.level,
                icone: "🔥",
                duracao: 3000
            });
        }

        // ⭐ XP
        if(data.xp_ganho > 0){
            adicionarNotificacao({
                titulo: "XP",
                descricao: "+" + data.xp_ganho + " XP",
                icone: "⭐"
            });
        }

        // 🏆 CONQUISTA
        if(data.nova_conquista){

            adicionarNotificacao({
                titulo: data.nova_conquista.nome,
                descricao: data.nova_conquista.descricao,
                icone: "✨"
            });
        }

    }catch(e){
        console.error(e);
    }
}

// ===========================
// 🧠 QUIZ
// ===========================
function embaralhar(array) {
    return array.sort(() => Math.random() - 0.5);
}

const perguntas = <?= json_encode(array_values($perguntas)) ?>;

let indice = 0;
let pontuacao = 0;

const container = document.getElementById("quiz-container");

function mostrarPergunta() {

    document.getElementById("barra-progresso").style.width =
        (indice / perguntas.length) * 100 + "%";

    container.innerHTML = "";

    const pergunta = perguntas[indice];
    const respostas = embaralhar([...pergunta.respostas]);

    const h2 = document.createElement("h2");
    h2.textContent = pergunta.texto;
    container.appendChild(h2);

    respostas.forEach(resposta => {

        const btn = document.createElement("button");
        btn.textContent = resposta.texto;

        btn.onclick = () => {

            document.querySelectorAll("#quiz-container button")
                .forEach(b => b.disabled = true);

            if (resposta.correta == 1) {
                somAcerto.play();
                btn.classList.add("correta");
                pontuacao++;
            } else {
                somErro.play();
                btn.classList.add("errada");
            }

            setTimeout(() => {
                indice++;
                indice < perguntas.length ? mostrarPergunta() : mostrarResultado();
            }, 700);
        };

        container.appendChild(btn);
    });
}

// ===========================
// 🏆 RESULTADO
// ===========================
function mostrarResultado() {

    container.innerHTML = `
        <div class="vitoria">
            <h2>🏆 ${pontuacao}/${perguntas.length}</h2>
            <div id="ranking"></div>
            <div class="botoes">
                <button onclick="location.reload()">Refazer</button>
                <button onclick="history.back()">Voltar</button>
            </div>
        </div>
    `;

    salvarResultado();

    setTimeout(() => {
        registrarEvento("completar_quiz", <?= $quiz_id ?>);
    }, 300);
}

// ===========================
// 💾 SALVAR
// ===========================
function salvarResultado() {
    fetch("../Partial/salvar_resultado.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            quiz_id: <?= $quiz_id ?>,
            pontuacao,
            total: perguntas.length
        })
    })
    .then(res => res.json())
    .then(data => mostrarRanking(data.ranking));
}

// ===========================
// 🏅 RANKING
// ===========================
function mostrarRanking(ranking) {
    if (!ranking.length) return;

    let html = "<h3>🏆 Top 3</h3>";

    ranking.forEach((p, i) => {
        html += `<p>${i+1}º ${p.nome} — ${p.pontuacao}/${p.total}</p>`;
    });

    document.getElementById("ranking").innerHTML = html;
}

// START
perguntas.length > 0 ? mostrarPergunta() : container.innerHTML = "<h2>Sem perguntas</h2>";

</script>

</body>
</html>