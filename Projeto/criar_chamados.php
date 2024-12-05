<?php
session_start();



if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.html'); 
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['titulo_chamado']) || !isset($_POST['mensagem_chamado'])) {
        $_SESSION['mensagem'] = "Por favor, preencha todos os campos.";
        header('Location: criar_chamado.php');
        exit;
    }

    $titulo = $_POST['titulo_chamado'];
    $mensagem = $_POST['mensagem_chamado'];
    $usuario_id = $_SESSION['usuario_id'];

    if (empty($titulo) || empty($mensagem)) {
        $_SESSION['mensagem'] = "Por favor, preencha todos os campos.";
        header('Location: criar_chamado.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO chamados (usuario_id, titulo, mensagem) VALUES (:usuario_id, :titulo, :mensagem)");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':titulo' => $titulo,
        ':mensagem' => $mensagem
    ]);

    $_SESSION['mensagem'] = "Chamado criado com sucesso!";

    if (isset($_SESSION['mensagem'])) {
        echo "<p>" . $_SESSION['mensagem'] . "</p>";
        unset($_SESSION['mensagem']); 
    }
        

    header('Location: inicio.php');
    exit;
    
}

   

?>