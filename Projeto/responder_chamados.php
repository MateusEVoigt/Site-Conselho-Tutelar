<?php
session_start();

header('Content-Type: application/json'); 

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_chamado']) && isset($_POST['resposta'])) {
    $idChamado = $_POST['id_chamado'];
    $resposta = $_POST['resposta'];

    $stmt = $pdo->prepare("UPDATE chamados SET resposta = :resposta, data_resposta = NOW(), status = 'Respondido' WHERE id = :id");
    $stmt->execute([
        ':resposta' => $resposta,
        ':id' => $idChamado
    ]);

    echo json_encode(['success' => true]); 
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar a resposta']);
}

