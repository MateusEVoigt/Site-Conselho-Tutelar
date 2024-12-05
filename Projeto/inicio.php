<?php
session_start();


if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.html');
    exit;
}


require_once 'db.php';


$stmt = $pdo->prepare("SELECT tipo_usuario FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: index.html');
    exit;
}
    
$tipo_usuario = $usuario['tipo_usuario']; 

$stmt_conteudo_conselho = $pdo->prepare("SELECT titulo, texto FROM conteudos WHERE tipo_conteudo = :tipo_conteudo");
$stmt_conteudo_conselho->execute([':tipo_conteudo' => 'conselho_tutelar']);
$conselho_tutelar = $stmt_conteudo_conselho->fetch(PDO::FETCH_ASSOC);


$stmt_conteudo_eca = $pdo->prepare("SELECT titulo, texto FROM conteudos WHERE tipo_conteudo = :tipo_conteudo");
$stmt_conteudo_eca->execute([':tipo_conteudo' => 'eca']);
$eca = $stmt_conteudo_eca->fetch(PDO::FETCH_ASSOC);

if (!$conselho_tutelar) {
    $conselho_tutelar = ['titulo' => 'Conselho Tutelar', 'texto' => 'Informações não disponíveis.']; 
}

if (!$eca) {
    $eca = ['titulo' => 'Estatuto da Criança e do Adolescente (ECA)', 'texto' => 'Informações não disponíveis.']; 
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tipo_usuario === 'administrador') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];


    if (empty($titulo) || empty($descricao) || empty($data_inicio) || empty($data_fim)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO eventos (titulo, descricao, data_inicio, data_fim, ativo) 
                               VALUES (:titulo, :descricao, :data_inicio, :data_fim, TRUE)");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':data_inicio' => $data_inicio,
            ':data_fim' => $data_fim
        ]);
        $sucesso = "Evento cadastrado com sucesso!";
    }
}


$stmt_eventos = $pdo->prepare("SELECT id, titulo, descricao, data_inicio, data_fim FROM eventos WHERE ativo = TRUE AND data_fim >= NOW() ORDER BY data_inicio ASC");
$stmt_eventos->execute();
$eventos = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);


    

$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT tipo_usuario FROM usuarios WHERE id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$tipo_usuario = $usuario['tipo_usuario'];
    

if ($tipo_usuario === 'usuario') {
    $stmt_chamados = $pdo->prepare("SELECT c.id, c.numero_chamado, c.titulo, c.status, c.data_criacao, c.resposta
                                    FROM chamados c WHERE c.usuario_id = :usuario_id ORDER BY c.data_criacao DESC");
    $stmt_chamados->execute([':usuario_id' => $_SESSION['usuario_id']]);
    $chamados = $stmt_chamados->fetchAll(PDO::FETCH_ASSOC);

} else {
    $stmt_chamados = $pdo->prepare("SELECT c.id, c.numero_chamado, c.titulo, c.status, c.data_criacao, c.mensagem, c.resposta, u.nome 
                            FROM chamados c
                            JOIN usuarios u ON c.usuario_id = u.id
                            ORDER BY c.data_criacao DESC");
    $stmt_chamados->execute();
    $chamados = $stmt_chamados->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_usuario']) && isset($_POST['novo_tipo']) && $tipo_usuario === 'administrador') {
    $email_usuario = $_POST['email_usuario'];
    $novo_tipo = $_POST['novo_tipo'];

    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email_usuario]);
        $usuario_alvo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario_alvo) {
            $stmt = $pdo->prepare("UPDATE usuarios SET tipo_usuario = :novo_tipo WHERE email = :email");
            $stmt->execute([':novo_tipo' => $novo_tipo, ':email' => $email_usuario]);

            $_SESSION['mensagem'] = "Tipo de usuário atualizado com sucesso!";
        } else {
            $_SESSION['erro'] = "Usuário com o e-mail fornecido não encontrado.";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao atualizar o tipo de usuário: " . $e->getMessage();
    }

    header('Location: inicio.php');
    exit;
}



function getTemaClasse() {
    return isset($_SESSION['tema-claro']) ? 'tema-claro-texto' : 'tema-escuro-texto';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.html");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link rel="stylesheet" href="main.css">
</head>
<body class="tema-claro"> 
    <div>
        <button class="btn" id="theme-toggle">Escuro</button> 
    </div>
    
    <div class="top-image-container">
        <img src="images/ascurra.png" alt="Ascurra" class="top-image">
        <img src="images/Conselho Tutelar.png" alt="Conselho Tutelar" class="top-image">
    </div>

    <div class="logout-container">
        <h1>Bem-vindo ao Sistema</h1>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
        <form method="POST" style="display: inline;">
        <button type="submit" name="logout" class="btn-logout">Sair</button>
        </form>
    </div>

        <div class="conteudos">
            <div id="menu-sobre" class="conteudo-menu">
                <h2>Sobre</h2>
                <p>Informações sobre o Conselho Tutelar e o ECA.</p>
            </div>
            <div id="menu-conselho-tutelar" class="conteudo-menu">
                <h2><?php echo htmlspecialchars($conselho_tutelar['titulo']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($conselho_tutelar['texto'])); ?></p>
            </div>
            <div id="menu-eca" class="conteudo-menu">
                <h2><?php echo htmlspecialchars($eca['titulo']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($eca['texto'])); ?></p>
            </div>


                <div id="menu-campanhas" class="conteudo-menu">
                    <?php if (count($eventos) > 0): ?>
                        <ul>
                            <?php foreach ($eventos as $evento): ?>
                                <li>
                                    <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                    <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($evento['descricao'])); ?></p>
                                    <p><strong>Data de Início:</strong> <?php echo htmlspecialchars($evento['data_inicio']); ?></p>
                                    <p><strong>Data de Término:</strong> <?php echo htmlspecialchars($evento['data_fim']); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Não há eventos disponíveis no momento.</p>
                    <?php endif; ?>
                </div>
                

                <div id="menu-cadastrar-evento" class="conteudo-menu">
                    <?php if ($tipo_usuario === 'administrador'): ?>
                        <h2 id="form_cadastro_evento">Cadastrar Novo Evento</h2>

                        <?php if (isset($erro)): ?>
                            <p style="color: red;"><?php echo $erro; ?></p>
                        <?php endif; ?>

                        <?php if (isset($sucesso)): ?>
                            <p style="color: green;"><?php echo $sucesso; ?></p>
                        <?php endif; ?>

                        <form action="inicio.php" method="POST">
                            <label for="titulo">Título:</label>
                            <input type="text" id="titulo" name="titulo" required>
                            <br><br>

                            <label for="descricao">Descrição:</label>
                            <textarea id="descricao" name="descricao" required></textarea>
                            <br><br>

                            <label for="data_inicio">Data de Início:</label>
                            <input type="datetime-local" id="data_inicio" name="data_inicio" required>
                            <br><br>

                            <label for="data_fim">Data de Fim:</label>
                            <input type="datetime-local" id="data_fim" name="data_fim" required>
                            <br><br>

                            <button type="submit">Cadastrar Evento</button>
                        </form>
                    <?php endif; ?>
                </div>
            



            <div id="menu-ligar" class="conteudo-menu">
                <h2>Ligue para nós:</h2>
                <p><a href="tel:+557070707070" class="btn-ligar">Ligar para o Conselho Tutelar</a></p>
            </div>


            <div id="menu-chat" class="conteudo-menu">
                <h2>Chamados</h2>
                <p>Crie um novo chamado para o Conselho Tutelar.</p>
                <form method="POST" action="criar_chamados.php">
                    <div class="form-group">
                        <label for="titulo_chamado">Título:</label>
                        <input type="text" name="titulo_chamado" id="titulo_chamado" required placeholder="Digite o título do chamado">
                    </div>
                    <div class="form-group">
                        <label for="mensagem_chamado">Mensagem:</label>
                        <textarea name="mensagem_chamado" id="mensagem_chamado" required placeholder="Digite a sua mensagem"></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Criar Chamado</button>
                </form>
            </div>

            
            <div id="menu-chamados" class="hidden">
                <h2>Meus Chamados</h2>

                <?php if ($tipo_usuario === 'usuario'): ?>
                    <?php if ($chamados): ?>
                        <div class="chamados-lista">
                            <?php foreach ($chamados as $chamado): ?>
                                <div class="chamado" id="chamado_<?php echo $chamado['id']; ?>">
                                    <div class="detalhes">
                                        <p><strong>Chamado:</strong> <?php echo htmlspecialchars($chamado['titulo']); ?></p>
                                        <p><strong>Descrição:</strong> 
                                            <?php 
                                                if (isset($chamado['mensagem'])) {
                                                    echo htmlspecialchars($chamado['mensagem']);
                                                } else {
                                                    echo "Descrição não disponível.";
                                                }
                                            ?>
                                        </p>
                                        <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($chamado['status']); ?></span></p>
                                    </div>
                                    <div class="resposta-container">
                                        <?php if (!empty($chamado['resposta'])): ?>
                                            <div class="resposta">
                                                <strong>Resposta:</strong>
                                                <p><?php echo htmlspecialchars($chamado['resposta']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Você ainda não abriu nenhum chamado.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <?php if ($chamados): ?>
                        <div class="chamados-lista">
                            <?php foreach ($chamados as $chamado): ?>
                                <div class="chamado" id="chamado_<?php echo $chamado['id']; ?>">
                                    <div class="detalhes">
                                        <p><strong>Chamado:</strong> <?php echo htmlspecialchars($chamado['titulo']); ?></p>
                                         <p><strong>Usuário:</strong> <?php echo htmlspecialchars($chamado['nome']); ?></p>
                                        <p><strong>Descrição:</strong> 
                                            <?php 
                                                if (isset($chamado['mensagem'])) {
                                                    echo htmlspecialchars($chamado['mensagem']);
                                                } else {
                                                    echo "Descrição não disponível.";
                                                }
                                            ?>
                                        </p>
                                        <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($chamado['status']); ?></span></p>
                                    </div>
                                    <div class="resposta-container">
                                        <?php if (!empty($chamado['resposta'])): ?>
                                            <div class="resposta">
                                                <strong>Resposta:</strong>
                                                <p><?php echo htmlspecialchars($chamado['resposta']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="acao">
                                        <?php if ($chamado['status'] !== 'Respondido'): ?>
                                            <button class="btn-responder" data-id="<?php echo $chamado['id']; ?>">Responder</button>
                                            
                                            <div id="resposta_<?php echo $chamado['id']; ?>" style="display: none;">
                                                <textarea id="resposta-text-<?php echo $chamado['id']; ?>" rows="4" cols="50" placeholder="Digite sua resposta..."></textarea><br>
                                                <button class="btn-enviar-resposta" data-id="<?php echo $chamado['id']; ?>">Enviar</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Não há chamados registrados.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>




            <div id="menu-comentarios" class="conteudo-menu">
                <h2>Comentários e Avaliações</h2>
                <p>Veja o que outros usuários estão dizendo sobre o Conselho Tutelar.</p>
            </div>

            <?php if ($tipo_usuario === 'administrador'): ?>
                <div id="menu-gerenciar" class="conteudo-menu">
                    <h2>Gerenciar Usuários</h2>
                    <form action="inicio.php" method="POST">
                        <div class="form-group">
                            <label for="email_usuario">E-mail do Usuário:</label>
                            <input type="email" name="email_usuario" id="email_usuario" required placeholder="Digite o e-mail do usuário">
                        </div>
                        <div class="form-group">
                            <label for="novo_tipo">Novo Tipo:</label>
                            <select name="novo_tipo" id="novo_tipo" required>
                                <option value="administrador">Administrador</option>
                                <option value="usuario">Usuário Comum</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit">Alterar Tipo</button>
                    </form>
                </div>
            <?php endif; ?>
            </div>
   



    <div class="container">
        <div class="menu-lateral">

            <button data-target="menu-sobre" id="btn-sobre">Sobre ></button>
            <div id="submenu-sobre" class="submenu">
                <button class="submenu-item" data-target="menu-conselho-tutelar">Conselho Tutelar</button>
                <button class="submenu-item" data-target="menu-eca">ECA</button>
            </div>

            <button data-target="menu-campanhas" id="btn-campanhas">Campanhas e Eventos</button>
            <div id="submenu-campanhas" class="submenu">
                <?php if ($tipo_usuario === 'administrador'): ?>
                    <button class="submenu-item" data-target="menu-cadastrar-evento">Cadastrar Evento</button>
                <?php endif; ?>
            </div>


            <button data-target="menu-ligar">Ligar</button>
            <button data-target="menu-chat">Chamados</button>
            <button data-target="menu-comentarios">Comentários e Avaliações</button>
            <?php if ($tipo_usuario === 'administrador'): ?>
                <button data-target="menu-gerenciar">Gerenciar Usuários</button>
            <?php endif; ?>
        </div>
    </div>                

    <script src="app.js"></script>
</body>
</html>
