<?php
session_start();

include_once("../Partial/config.php");
include_once("../Partial/header.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Quiz não encontrado.");
}

$quiz_id = (int) $_GET['id'];


/* ===============================
   BUSCAR QUIZ + CRIADOR
================================ */
$sqlQuiz = "
SELECT q.*, u.nome AS criador_nome
FROM quizzes_rank q
JOIN usuarios u ON u.id = q.criador
WHERE q.id = $quiz_id
LIMIT 1
";

$resultQuiz = $conexao->query($sqlQuiz);

if (!$resultQuiz || $resultQuiz->num_rows === 0) {
    die("Quiz não encontrado.");
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
   BUSCAR ITENS
================================ */
$sqlItens = "
    SELECT posicao, nome, dica
    FROM quiz_rank_itens
    WHERE quiz_id = $quiz_id
    ORDER BY posicao ASC
";

$resultItens = $conexao->query($sqlItens);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quiz['titulo']) ?></title>

    <style>
        body {
            padding-top: 100px;
            font-family: Arial, Helvetica, sans-serif;
            background: #111;
            color: #fff;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }

        h1 { margin-bottom: 5px; }
        p { color: #aaa; }

        input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin: 15px 0;
            border: none;
            border-radius: 6px;
        }

        #desistir {
            width: 100%;
            padding: 10px;
            background: #ff4444;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 10px;
        }

        #desistir:hover { background: red; }

        .linha {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #333;
            padding: 8px 0;
        }

        .posicao { width: 50px; color: #888; }
        .nome { flex: 1; text-align: center; }
        .dica { width: 220px; text-align: right; color: #666; }

        .acertou {
            color: #00ff88;
            font-weight: bold;
        }

        .erro {
            color: red;
            font-weight: bold;
        }

        .topo-fixo {
            position: sticky;
            top: 90px;
            background: #111;
            z-index: 10;
            padding-bottom: 10px;
        }

        #lista {
            max-height: 55vh;
            overflow-y: auto;
            margin-top: 10px;
            padding-right: 5px;
        }

        /* ================= VITÓRIA ================= */

        #vitoria {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .vitoria-box {
            background: #1b1b1b;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            width: 320px;
            position: relative;
        }

        #fechar-vitoria {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            color: #888;
        }

        #fechar-vitoria:hover { color: white; }

        .vitoria-box button {
            margin-top: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            background: #00ff88;
            font-weight: bold;
            cursor: pointer;
        }

        /* ================= TOAST ================= */

        #toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #00ff88;
            color: black;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            display: none;
            z-index: 1000;
        }

    </style>
</head>

<body>

<div id="toast"></div>

<div class="container">

    <div class="topo-fixo">
        <h1><?= htmlspecialchars($quiz['titulo']) ?></h1>
        <p><?= htmlspecialchars($quiz['descricao']) ?></p>

        <input type="text" id="resposta" placeholder="Digite um nome..." autocomplete="off" autofocus>

    </div>
    
    <div id="lista">
        <?php while ($item = $resultItens->fetch_assoc()): ?>
            <div class="linha" data-nome="<?= htmlspecialchars($item['nome']) ?>">
                <div class="posicao"><?= $item['posicao'] ?></div>
                <div class="nome">---</div>
                <div class="dica"><?= htmlspecialchars($item['dica']) ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <button id="desistir">Desistir</button>
    </div>

<!-- MODAL VITÓRIA -->
<div id="vitoria">
    <div class="vitoria-box">
        <span id="fechar-vitoria">✖</span>
        <h2>🎉 Parabéns!</h2>
        <p>Você completou o quiz!</p>

        <a href="../Home/Quizzes.php?tipo=rank">
            <button>Voltar</button>
        </a>
    </div>
</div>

<?php if ($podeEditar): ?>

<a href="../Edit/editorQuizRank.php?id=<?= $quiz_id ?>">
    <button id="editor">Edite esse Quiz</button>
</a>

<?php endif; ?>

<script>
    const input = document.getElementById('resposta');
    const linhas = document.querySelectorAll('.linha');
    const modalVitoria = document.getElementById('vitoria');
    const fecharVitoria = document.getElementById('fechar-vitoria');
    const desistirBtn = document.getElementById('desistir');
    const toast = document.getElementById('toast');

    function normalizar(txt) {
        return txt.toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    }

    function mostrarToast(texto) {
        toast.innerText = texto.toUpperCase();
        toast.style.display = "block";

        setTimeout(() => {
            toast.style.display = "none";
        }, 1000);
    }

    function checarVitoria() {
        const restantes = [...linhas].filter(l =>
            l.querySelector('.nome').innerHTML === '---'
        );

        if (restantes.length === 0) {
            modalVitoria.style.display = "flex";
        }
    }

    input.addEventListener('keyup', () => {

        const valor = normalizar(input.value.trim());
        if (valor.length < 2) return;

        const palavrasDigitadas = valor.split(" ");

        linhas.forEach(linha => {

            const campoNome = linha.querySelector('.nome');
            if (campoNome.innerHTML !== '---') return;

            const nomeCompleto = normalizar(linha.dataset.nome);
            const palavrasNome = nomeCompleto.split(" ");

            const acertou = palavrasDigitadas.some(p =>
                palavrasNome.includes(p)
            );

            if (acertou) {
                campoNome.innerHTML = linha.dataset.nome;
                campoNome.classList.add('acertou');

                mostrarToast(linha.dataset.nome);

                input.value = '';

                checarVitoria();
            }
        });
    });

    desistirBtn.onclick = () => {
        linhas.forEach(linha => {
            const campoNome = linha.querySelector('.nome');

            if (campoNome.innerHTML === '---') {
                campoNome.innerHTML = linha.dataset.nome;
                campoNome.classList.add('erro');
            }
        });
    };

    fecharVitoria.onclick = () => {
        modalVitoria.style.display = "none";
    };
</script>

</body>
</html>
