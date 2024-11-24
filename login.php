<?php
session_start();

$host = 'localhost';
$dbname = 'sushi';
$user = 'postgres';
$password = 'user';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro na conexão: ' . $e->getMessage()
    ]);
    exit;
}

$email = $_POST['email'];
$senha = $_POST['senha'];

try {
    $sql = "SELECT * FROM tb_usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $senhaBase64 = base64_encode($senha);

        if ($senhaBase64 === $result['senha']) {
            $_SESSION['user'] = [
                'id' => $result['id'],
                'nome' => $result['nome'],
                'email' => $result['email'],
                'rua' => $result['rua'],
                'numero' => $result['numero'],
                'bairro' => $result['bairro'],
                'cidade' => $result['cidade'],
                'estado' => $result['estado'],
            ];

            echo json_encode([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Senha incorreta!'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'E-mail não encontrado!'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao realizar login: ' . $e->getMessage()
    ]);
}
?>
