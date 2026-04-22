<?php
session_start();
include_once('../Partial/config.php');

if (empty($_GET['palavrachave'])) {
    die("Notícia inválida.");
}

$chave = $_GET['palavrachave'];

/* ===============================
   BUSCAR NOTÍCIA + CRIADOR
================================ */
$stmt = $conexao->prepare(
    "SELECT n.*, u.nome AS criador_nome
     FROM noticias n
     JOIN usuarios u ON u.id = n.criador
     WHERE n.palavrachave = ?
     LIMIT 1"
);

$stmt->bind_param("s", $chave);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Notícia não encontrada.");
}

$noticia = $result->fetch_assoc();
$categoria = $noticia['categoria'];


/* ===============================
   RELACIONADAS
================================ */
$stmtRel = $conexao->prepare(
    "SELECT * FROM noticias 
     WHERE categoria = ? AND palavrachave != ?
     ORDER BY data_publicacao DESC
     LIMIT 6"
);

$stmtRel->bind_param("ss", $categoria, $chave);
$stmtRel->execute();
$relacionadas = $stmtRel->get_result();


/* ===============================
   INSERIR COMENTÁRIO
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {

    if (!isset($_SESSION['id'])) {
        die("❌ Você precisa estar logado para comentar.");
    }

    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {

        $stmtComent = $conexao->prepare(
            "INSERT INTO comentarios (noticia_id, usuario_id, comentario)
             VALUES (?, ?, ?)"
        );

        $stmtComent->bind_param(
            "iis",
            $noticia['id'],
            $_SESSION['id'],
            $comentario
        );

        $stmtComent->execute();
    }

    header("Location: noticia.php?palavrachave=" . urlencode($chave));
    exit;
}

include_once("../Partial/header.php");


/* ===============================
   BUSCAR COMENTÁRIOS
================================ */
$stmtComents = $conexao->prepare(
    "SELECT c.*, u.nome, u.foto
     FROM comentarios c
     JOIN usuarios u ON u.id = c.usuario_id
     WHERE c.noticia_id = ?
     ORDER BY c.data_comentario DESC"
);

$stmtComents->bind_param("i", $noticia['id']);
$stmtComents->execute();
$comentarios = $stmtComents->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <title>
        <?= htmlspecialchars($noticia['titulo']) ?> - DnNerds
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../Styles/Noticia.css?v=18">

</head>

<body data-noticia-id="<?= $noticia['id'] ?>">

<div id="notificacao" class="notificacao">
    <div class="notif-icon">🏆</div>
    <div class="notif-text">
        <strong id="notif-titulo">Título</strong>
        <p id="notif-desc">Descrição</p>
    </div>
</div>

<main class="conteudo">

<div class="coluna-principal">

<article class="noticia-detalhe">

<img
    src="<?= htmlspecialchars(
        filter_var($noticia['imagem'] ?: 'default.jpg', FILTER_VALIDATE_URL)
            ? $noticia['imagem']
            : '../../Imagens/' . ($noticia['imagem'] ?: 'default.jpg')
    ) ?>"
    alt="<?= htmlspecialchars($noticia['titulo']) ?>"
>

<h1>
<?= htmlspecialchars($noticia['titulo']) ?>
</h1>


<p class="autor">
Criado por
<?= htmlspecialchars($noticia['criador_nome']) ?>
</p>


<p>
<?= nl2br(htmlspecialchars($noticia['texto'])) ?>
</p>


<time>
Publicado em:
<?= date("d/m/Y", strtotime($noticia['data_publicacao'])) ?>
</time>

</article>


<section class="comentarios">

<h2>💬 Comentários</h2>

<?php if (isset($_SESSION['id'])): ?>

<form method="POST" class="comentario-form">

<textarea
name="comentario"
placeholder="Escreva seu comentário..."
required
></textarea>

<button type="submit">
Comentar
</button>

</form>

<?php else: ?>

<p>
👉 <a href="../User/FazerLogin.php">Faça login</a>
para comentar.
</p>

<?php endif; ?>


<div class="lista-comentarios">

<?php if ($comentarios->num_rows > 0): ?>

<?php while ($coment = $comentarios->fetch_assoc()): ?>

<div class="comentario-item">

<img src="<?= htmlspecialchars(
    filter_var(!empty($coment['foto']) ? $coment['foto'] : '', FILTER_VALIDATE_URL)
        ? $coment['foto']
        : '../../Imagens/' . (!empty($coment['foto']) ? $coment['foto'] : 'user.png')
) ?>" alt="">

<div class="comentario-conteudo">

<strong>
<?= htmlspecialchars($coment['nome']) ?>
</strong>

<p>
<?= nl2br(htmlspecialchars($coment['comentario'])) ?>
</p>

<span>
<?= date("d/m/Y H:i", strtotime($coment['data_comentario'])) ?>
</span>

</div>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>Nenhum comentário ainda.</p>

<?php endif; ?>

</div>

</section>

</div>



<aside class="noticias-relacionadas">

<h2>
Mais em
<?= htmlspecialchars($categoria) ?>
</h2>

<div class="relacionadas-grid">

<?php if ($relacionadas->num_rows > 0): ?>

<?php while ($row = $relacionadas->fetch_assoc()): ?>

<a
href="noticia.php?palavrachave=<?= urlencode($row['palavrachave']) ?>"
class="relacionada-item"
>

<div class="caixa-relacionada">

<img
    src="<?= htmlspecialchars(
        filter_var($row['imagem'] ?: 'default.jpg', FILTER_VALIDATE_URL)
            ? $row['imagem']
            : '../../Imagens/' . ($row['imagem'] ?: 'default.jpg')
    ) ?>"
    alt="<?= htmlspecialchars($row['titulo']) ?>"
>

<p>
<?= htmlspecialchars($row['titulo']) ?>
</p>

</div>

</a>

<?php endwhile; ?>

<?php else: ?>

<p>Nenhuma notícia relacionada.</p>

<?php endif; ?>

</div>

</aside>

</main>



<?php
if (isset($_SESSION['id'])) {

    $ehCriador =
        $_SESSION['id'] == $noticia['criador'];

    $ehAdmin =
        isset($_SESSION['adm']) &&
        $_SESSION['adm'] == 1;

    if ($ehCriador || $ehAdmin):
?>

<a href="../Edit/editorNoticia.php?id=<?= $noticia['id'] ?>">
<button id="editor">
Editar Notícia
</button>
</a>

<?php
    endif;
}
?>

<!-- NOTIFICAÇÃO -->
<div id="notificacao" class="notificacao">
    <div class="notif-icon">🏆</div>
    <div class="notif-text">
        <strong id="notif-titulo">Título</strong>
        <p id="notif-desc">Descrição</p>
    </div>
</div>

<style>
.notificacao {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #1e1e2f;
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
    transform: translateX(120%);
    opacity: 0;
    transition: all 0.5s ease;
    z-index: 9999;
}

.notificacao.show {
    transform: translateX(0);
    opacity: 1;
}

.notif-icon {
    font-size: 28px;
}
</style>

<script>

// ===========================
// NOTIFICAÇÃO
// ===========================
function mostrarNotificacao(titulo, descricao, icone = "⭐") {

    const notif = document.getElementById("notificacao");
    const tituloEl = document.getElementById("notif-titulo");
    const descEl = document.getElementById("notif-desc");
    const iconEl = notif.querySelector(".notif-icon");

    if(!notif){
        console.error("Elemento #notificacao não encontrado");
        return;
    }

    tituloEl.textContent = titulo;
    descEl.textContent = descricao;
    iconEl.textContent = icone;

    notif.classList.add("show");

    setTimeout(() => {
        notif.classList.remove("show");
    }, 4000);
}

// ===========================
// REGISTRAR EVENTO (XP)
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

        console.log(`+${data.xp_ganho} XP`);

        // LEVEL UP
        if(data.subiu_level){
            mostrarNotificacao(
                "LEVEL UP!",
                "Você atingiu o nível " + data.level,
                "🔥"
            );
        }

        // XP
        if(data.xp_ganho > 0){
            mostrarNotificacao(
                "XP Ganho",
                "+" + data.xp_ganho + " XP",
                "⭐"
            );
        }

        // CONQUISTA
        if(data.nova_conquista){
            mostrarNotificacao(
                data.nova_conquista.nome,
                data.nova_conquista.descricao,
                data.nova_conquista.icone
            );
        }

    }catch(e){
        console.error("Erro:", e);
    }
}

// ===========================
// DISPARAR AUTOMATICAMENTE
// ===========================
document.addEventListener("DOMContentLoaded", function(){

    console.log("Sistema carregado ✅");

    const noticiaID = document.body.dataset.noticiaId;

    // evita farm de XP infinito
    let vistas = JSON.parse(localStorage.getItem("noticiasVistas")) || [];

    if(noticiaID && !vistas.includes(noticiaID)){

        registrarEvento("ler_noticia", noticiaID);

        vistas.push(noticiaID);
        localStorage.setItem("noticiasVistas", JSON.stringify(vistas));

    }

});

</script>

</body>

</html>

<?php include_once("../Partial/footer.php"); ?>