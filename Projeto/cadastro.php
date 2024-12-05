<?php
$host = 'localhost';
$dbname = 'postgres';
$user = 'postgres';
$password = 'P@ssw0rdBD';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT); 
    $tipo_usuario = 'usuario'; 

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES (:nome, :email, :senha, :tipo_usuario)");
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha,
            ':tipo_usuario' => $tipo_usuario
        ]);


        header('Location: index.html');
        exit;
    } catch (PDOException $e) {
        echo "Erro ao cadastrar usuário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <style>
        :root {
            --white: #ffffff;
            --black: #000000;
            --gray: #f0f0f0;
            --dark-gray: #333333;
        }


        .tema-claro {
            --bg: var(--white);
            --fontColor: var(--white);
            --btnbg: green;
            --btnFontColor: var(--white);
            --form-bg: var(--black);
            --input-bg: var(--dark-gray);
            --input-border: var(--black);
            background-color: var(--bg); 
        }

        .tema-escuro {
            --bg: var(--black);
            --fontColor: var(--black);
            --btnbg: green;
            --btnFontColor: var(--black);
            --form-bg: var(--white);
            --input-bg: var(--gray);
            --input-border: var(--black);
            background-color: var(--bg); 
        }


        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: var(--form-bg);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .login-container p {
            color: var(--fontColor);
        }

        .login-container h1 {
            color: var(--fontColor);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form .form-group {
            display: flex;
            flex-direction: column;
        }

        form .form-group label {
            color: var(--fontColor);
            font-weight: bold;
            margin-bottom: 5px;
        }

        form .form-group input,
        form .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: var(--input-bg);
            color: var(--black);
            font-size: 14px
        }

        form .form-group input:focus,
        form .form-group select:focus {
            outline: none;
            border-color: var(--btnbg);
        }


        .btn-submit {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: var(--btnbg);
            color: var(--btnFontColor);
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: var(--dark-gray);
            color: var(--white);
        }


        .btn {
            position: fixed;
            top: 20px;
            right: 20px;
            height: 50px;
            width: 50px;
            border-radius: 50%;
            border: none;
            background-color: var(--dark-gray);
            color: var(--white);
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: var(--black);
        }

        .btn:focus {
            outline: none;
        }
    </style>
</head>
<body class="tema-claro">
    <div>
        <button class="btn">Escuro</button>
    </div>
    <div class="login-container">
        <h1>Cadastro</h1>
        <form action="cadastro.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn-submit">Cadastrar</button>
        </form>
        <p>Já tem uma conta? <a href="index.html">Faça login</a></p>
    </div>
    <script src="app.js"></script>
</body>
</html>
