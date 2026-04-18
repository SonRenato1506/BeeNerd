<?php
include_once("config.php");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

/* ✅ VALIDAÇÃO SEGURA */
if (!$data || !isset($data['quiz_id'], $data['pontuacao'], $data['total'])) {
    echo json_encode(['ranking' => []]);
    exit;
}

if (!isset($_SESSION['id'])) {
    echo json_encode(['ranking' => []]);
    exit;
}

$quiz_id = (int) $data['quiz_id'];
$usuario_id = (int) $_SESSION['id'];
$pontuacao = (int) $data['pontuacao'];
$total = (int) $data['total'];

/* ✅ SALVA SOMENTE PRIMEIRA TENTATIVA */
$sql = "
    INSERT IGNORE INTO quiz_resultados
    (quiz_id, usuario_id, pontuacao, total_perguntas)
    VALUES (?, ?, ?, ?)
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("iiii", $quiz_id, $usuario_id, $pontuacao, $total);
$stmt->execute();

/* ✅ TOP 3 GLOBAL */
$sqlRanking = "
    SELECT u.nome, r.pontuacao, r.total_perguntas
    FROM quiz_resultados r
    JOIN usuarios u ON u.id = r.usuario_id
    WHERE r.quiz_id = ?
    ORDER BY r.pontuacao DESC
    LIMIT 3
";

$stmtRanking = $conexao->prepare($sqlRanking);
$stmtRanking->bind_param("i", $quiz_id);
$stmtRanking->execute();

$result = $stmtRanking->get_result();

$ranking = [];

while ($row = $result->fetch_assoc()) {
    $ranking[] = [
        'nome' => $row['nome'],
        'pontuacao' => $row['pontuacao'],
        'total' => $row['total_perguntas']
    ];
}

echo json_encode(['ranking' => $ranking]);
