<?php
require_once __DIR__ . '../Partial/config.php';
include_once("../Partial.php/header.php");

/* ===============================
   VALIDAÇÃO DO QUIZ
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Quiz inválido.");
}

$quiz_id = (int) $_GET['id'];

/* ===============================
   BUSCAR QUIZ
================================ */
$sqlQuiz = "SELECT titulo FROM quizzes_rank WHERE id = $quiz_id LIMIT 1";
$resultQuiz = $conexao->query($sqlQuiz);

if ($resultQuiz->num_rows === 0) {
    die("Quiz não encontrado.");
}

$quiz = $resultQuiz->fetch_assoc();

/* ===============================
   SALVAR ALTERAÇÕES
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Atualizar itens existentes
    if (isset($_POST['item'])) {
        foreach ($_POST['item'] as $item_id => $dados) {
            $posicao = (int) $dados['posicao'];
            $nome = $dados['nome'];
            $dica = $dados['dica'];

            $stmt = $conexao->prepare("
                UPDATE quiz_rank_itens 
                SET posicao=?, nome=?, dica=?
                WHERE id=? AND quiz_id=?
            ");
            $stmt->bind_param("issii", $posicao, $nome, $dica, $item_id, $quiz_id);
            $stmt->execute();
        }
    }

    // Remover itens
    if (!empty($_POST['remover'])) {
        foreach ($_POST['remover'] as $item_id) {
            $stmt = $conexao->prepare("
                DELETE FROM quiz_rank_itens 
                WHERE id=? AND quiz_id=?
            ");
            $stmt->bind_param("ii", $item_id, $quiz_id);
            $stmt->execute();
        }
    }

    // Adicionar novo item
    // Adicionar novos itens (múltiplos)
    if (!empty($_POST['novo']['nome'])) {

        foreach ($_POST['novo']['nome'] as $index => $nome) {

            if (trim($nome) === '') {
                continue; // ignora campos vazios
            }

            $posicao = (int) $_POST['novo']['posicao'][$index];
            $dica = $_POST['novo']['dica'][$index];

            $stmt = $conexao->prepare("
            INSERT INTO quiz_rank_itens (quiz_id, posicao, nome, dica)
            VALUES (?, ?, ?, ?)
        ");
            $stmt->bind_param("iiss", $quiz_id, $posicao, $nome, $dica);
            $stmt->execute();
        }
    }


    header("Location: EditorQuizRank.php?id=$quiz_id");
    exit;
}

/* ===============================
   BUSCAR ITENS
================================ */
$sqlItens = "
    SELECT id, posicao, nome, dica
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
    <title>Editor Rank - <?= htmlspecialchars($quiz['titulo']) ?></title>

    <style>
        body {
            padding-top: 100px;
            background: #111;
            color: #fff;
            font-family: Arial;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid #333;
            padding: 8px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 6px;
        }

        th {
            text-align: left;
            color: #aaa;
        }

        .btn {
            padding: 6px 12px;
            cursor: pointer;
        }

        .btn-remover {
            background: #a00;
            color: #fff;
            border: none;
        }

        .btn-salvar {
            background: #0a5;
            color: #fff;
            border: none;
            margin-top: 15px;
        }

        .novo {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Editor de Ranking</h1>
        <p><?= htmlspecialchars($quiz['titulo']) ?></p>

        <form method="POST">
            <table>
                <tr>
                    <th>Posição</th>
                    <th>Nome</th>
                    <th>Dica</th>
                    <th>Remover</th>
                </tr>

                <?php while ($i = $resultItens->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="number" name="item[<?= $i['id'] ?>][posicao]" value="<?= $i['posicao'] ?>">
                        </td>
                        <td>
                            <input type="text" name="item[<?= $i['id'] ?>][nome]"
                                value="<?= htmlspecialchars($i['nome']) ?>">
                        </td>
                        <td>
                            <input type="text" name="item[<?= $i['id'] ?>][dica]"
                                value="<?= htmlspecialchars($i['dica']) ?>">
                        </td>
                        <td style="text-align:center">
                            <input type="checkbox" name="remover[]" value="<?= $i['id'] ?>">
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <button type="submit" class="btn btn-salvar">Salvar Alterações</button>

            <div class="novo">
                <h3>Adicionar novos itens</h3>

                <table>
                    <tr>
                        <th>Posição</th>
                        <th>Nome</th>
                        <th>Dica</th>
                    </tr>

                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <tr>
                            <td>
                                <input type="number" name="novo[posicao][]" placeholder="Ex: 1">
                            </td>
                            <td>
                                <input type="text" name="novo[nome][]" placeholder="Nome do item">
                            </td>
                            <td>
                                <input type="text" name="novo[dica][]" placeholder="Dica (opcional)">
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>

                <p style="color:#aaa; font-size:14px">
                    Preencha apenas os campos desejados. Campos vazios serão ignorados.
                </p>
            </div>

        </form>
    </div>

</body>

</html>