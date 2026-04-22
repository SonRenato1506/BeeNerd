<?php
include_once("../Partial/config.php");
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: FazerLogin.php");
    exit;
}

$user_id = $_SESSION['id'];
$mensagem = "";

/* =========================
   LOGOUT
========================= */
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: FazerLogin.php");
    exit;
}

/* =========================
   EXCLUIR CONTA
========================= */
if (isset($_POST['delete'])) {

    $sqlDelete = "DELETE FROM usuarios WHERE id = ?";
    $stmtDelete = $conexao->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $user_id);

    if ($stmtDelete->execute()) {
        session_destroy();
        header("Location: CriarConta.php");
        exit;
    } else {
        $mensagem = "❌ Erro ao excluir conta!";
    }
}

/* =========================
   ATUALIZA PERFIL
========================= */
if (isset($_POST['update'])) {

    $novoNome = trim($_POST['nome'] ?? '');
    $novaFoto = trim($_POST['foto'] ?? '');

    if (empty($novoNome)) {
        $mensagem = "❌ Nome não pode ficar vazio!";
    } else {

        $sql = "UPDATE usuarios SET nome = ?, foto = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssi", $novoNome, $novaFoto, $user_id);

        if ($stmt->execute()) {

            $_SESSION['nome'] = $novoNome;
            $mensagem = "✅ Perfil atualizado com sucesso!";

        } else {
            $mensagem = "❌ Erro ao atualizar!";
        }
    }
}

/* =========================
   BUSCA USUÁRIO (XP + LEVEL)
========================= */
$sqlUser = "
SELECT nome, email, foto, xp, level
FROM usuarios
WHERE id = ?
";

$stmtUser = $conexao->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$usuario = $resultUser->fetch_assoc();

/* =========================
   CONQUISTAS DO USUÁRIO
========================= */
$sqlConquistas = "
SELECT c.nome, c.descricao, c.icone
FROM usuarios_conquistas uc
JOIN conquistas c ON c.id = uc.conquista_id
WHERE uc.usuario_id = ?
";

$stmtC = $conexao->prepare($sqlConquistas);
$stmtC->bind_param("i", $user_id);
$stmtC->execute();
$resultC = $stmtC->get_result();

$conquistas = [];

while ($row = $resultC->fetch_assoc()) {
    $conquistas[] = $row;
}

/* =========================
   XP PARA PRÓXIMO NÍVEL
========================= */
function xpNecessario($level){
    return 100 * ($level * $level);
}

$xpAtual = $usuario['xp'];
$levelAtual = $usuario['level'];

$xpProximo = xpNecessario($levelAtual);
$progresso = $xpProximo > 0 ? ($xpAtual / $xpProximo) * 100 : 0;

include_once("../Partial/header.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="../../Styles/user.css?v=3">
</head>

<body>

<main class="container">

    <div class="perfil-box">

        <h1>🎮 Meu Perfil</h1>

        <?php if (!empty($mensagem)) : ?>
            <p class="mensagem"><?= $mensagem ?></p>
        <?php endif; ?>

        <div class="foto-perfil">
            <img src="../../Imagens/<?= !empty($usuario['foto']) ? $usuario['foto'] : 'https://via.placeholder.com/150' ?>">
        </div>

        <!-- =========================
             LEVEL + XP
        ========================== -->
        <div class="nivel-box">

            <h2>🔥 Nível <?= $levelAtual ?></h2>

            <div class="barra-xp">
                <div class="xp-fill" style="width: <?= $progresso ?>%"></div>
            </div>

            <p><?= $xpAtual ?> / <?= $xpProximo ?> XP</p>

        </div>

        <form method="POST">

            <label>Nome</label>
            <input type="text" name="nome"
                   value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label>Email</label>
            <input type="email"
                   value="<?= htmlspecialchars($usuario['email']) ?>" disabled>

            <label>Foto (link)</label>
            <input type="text" name="foto"
                   value="<?= htmlspecialchars($usuario['foto'] ?? '') ?>"
                   placeholder="Cole a URL da imagem">

            <button name="update" class="btn salvar">Salvar Alterações</button>

            <div class="actions">

                <button name="logout" class="btn logout">Sair da Conta</button>

                <button name="delete" class="btn delete"
                        onclick="return confirm('Tem certeza que deseja excluir sua conta?');">
                    Excluir Conta
                </button>

            </div>

        </form>

        <!-- =========================
             CONQUISTAS
        ========================== -->
        <div class="conquistas-box">

            <h2>🏆 Conquistas</h2>

            <?php if (count($conquistas) === 0): ?>

                <p>Nenhuma conquista ainda 😢</p>

            <?php else: ?>

                <div class="conquistas-grid">

                    <?php foreach ($conquistas as $c): ?>
                        <div class="conquista">
                            <div class="icone"><?= $c['icone'] ?></div>
                            <strong><?= htmlspecialchars($c['nome']) ?></strong>
                            <p><?= htmlspecialchars($c['descricao']) ?></p>
                        </div>
                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</main>

</body>
</html>
