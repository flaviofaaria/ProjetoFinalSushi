<?php
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
$data_nascimento = $_POST['data_nascimento'];

try {
    $sql = "SELECT senha FROM tb_usuario WHERE email = :email AND data_nascimento = :data_nascimento";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':data_nascimento' => $data_nascimento
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $senhaDecodificada = base64_decode($result['senha']);
        
        echo json_encode([
            'status' => 'success',
            'senha' => $senhaDecodificada
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dados incorretos!'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao recuperar senha: ' . $e->getMessage()
    ]);
}
?>
