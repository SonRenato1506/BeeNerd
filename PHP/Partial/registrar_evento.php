<?php
session_start();
include_once("config.php");

// força resposta JSON sempre
header('Content-Type: application/json');

// valida login
if (!isset($_SESSION['id'])) {
    echo json_encode(["erro" => "Usuário não logado"]);
    exit;
}

$usuario_id = $_SESSION['id'];
$tipo = $_POST['tipo'] ?? null;
$referencia_id = $_POST['referencia_id'] ?? null;

// valida dados obrigatórios
if (!$tipo) {
    echo json_encode(["erro" => "Tipo de evento não informado"]);
    exit;
}

/* -------------------------
1. REGISTRA EVENTO
------------------------- */

$stmt = $conexao->prepare("
    INSERT INTO eventos_usuario (usuario_id, tipo, referencia_id)
    VALUES (?, ?, ?)
");

$stmt->bind_param("isi", $usuario_id, $tipo, $referencia_id);
$stmt->execute();

/* -------------------------
2. GANHA XP
------------------------- */

// busca XP do evento
$stmt = $conexao->prepare("SELECT xp FROM eventos_xp WHERE tipo = ?");
$stmt->bind_param("s", $tipo);
$stmt->execute();
$result = $stmt->get_result();

$xp_ganho = 0;

if ($row = $result->fetch_assoc()) {
    $xp_ganho = (int)$row['xp'];
}

// busca dados do usuário
$stmt = $conexao->prepare("SELECT xp, level FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$xp_total = $user['xp'] + $xp_ganho;
$level = $user['level'];

$subiu_level = false;

// fórmula de level
function xpNecessario($level){
    return 100 * ($level * $level);
}

// loop de level up
while ($xp_total >= xpNecessario($level)) {
    $xp_total -= xpNecessario($level);
    $level++;
    $subiu_level = true;
}

// atualiza usuário
$stmt = $conexao->prepare("
    UPDATE usuarios SET xp = ?, level = ? WHERE id = ?
");
$stmt->bind_param("iii", $xp_total, $level, $usuario_id);
$stmt->execute();

/* -------------------------
3. CONQUISTAS
------------------------- */

$stmt = $conexao->prepare("
    SELECT * FROM conquistas WHERE tipo_evento = ?
");
$stmt->bind_param("s", $tipo);
$stmt->execute();
$conquistas = $stmt->get_result();

$nova_conquista = null;

while ($c = $conquistas->fetch_assoc()) {

    // conta eventos
    $stmt2 = $conexao->prepare("
        SELECT COUNT(*) as total
        FROM eventos_usuario
        WHERE usuario_id = ? AND tipo = ?
    ");
    $stmt2->bind_param("is", $usuario_id, $tipo);
    $stmt2->execute();
    $total = $stmt2->get_result()->fetch_assoc()['total'];

    if ($total >= $c['meta']) {

        // verifica se já tem
        $stmt3 = $conexao->prepare("
            SELECT id FROM usuarios_conquistas
            WHERE usuario_id = ? AND conquista_id = ?
        ");
        $stmt3->bind_param("ii", $usuario_id, $c['id']);
        $stmt3->execute();

        if ($stmt3->get_result()->num_rows == 0) {

            // insere conquista
            $stmt4 = $conexao->prepare("
                INSERT INTO usuarios_conquistas (usuario_id, conquista_id)
                VALUES (?, ?)
            ");
            $stmt4->bind_param("ii", $usuario_id, $c['id']);
            $stmt4->execute();

            $nova_conquista = [
                "id" => $c['id'],
                "nome" => $c['nome'],
                "descricao" => $c['descricao'],
                "icone" => $c['icone']
            ];

            break;
        }
    }
}

/* -------------------------
RESPOSTA FINAL
------------------------- */

echo json_encode([
    "xp_ganho" => $xp_ganho,
    "level" => $level,
    "subiu_level" => $subiu_level,
    "nova_conquista" => $nova_conquista
]);

exit;