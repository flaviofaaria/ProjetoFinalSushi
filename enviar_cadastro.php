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
    die("Erro na conexão: " . $e->getMessage());
}

$nome = $_POST['nome'];
$email = $_POST['email'];
$data_nascimento = $_POST['data_nascimento'];
$telefone = $_POST['telefone'];
$senha = base64_encode($_POST['senha']);
$cep = $_POST['cep'];
$rua = $_POST['rua'];
$numero = $_POST['numero'];
$bairro = $_POST['bairro'];
$complemento = $_POST['complemento'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];


$sqlCheckEmail = "SELECT COUNT(*) FROM tb_usuario WHERE email = :email";
$stmtCheckEmail = $pdo->prepare($sqlCheckEmail);
$stmtCheckEmail->execute([':email' => $email]);
$emailCount = $stmtCheckEmail->fetchColumn();

if ($emailCount > 0) {

    echo json_encode([
        'status' => 'error',
        'message' => 'E-mail já cadastrado!'
    ]);
    exit;
}

try {

    $sql = "INSERT INTO tb_usuario (nome, email, data_nascimento, telefone, senha, cep, rua, numero, bairro, complemento, cidade, estado) 
            VALUES (:nome, :email, :data_nascimento, :telefone, :senha, :cep, :rua, :numero, :bairro, :complemento, :cidade, :estado)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':data_nascimento' => $data_nascimento,
        ':telefone' => $telefone,
        ':senha' => $senha,
        ':cep' => $cep,
        ':rua' => $rua,
        ':numero' => $numero,
        ':bairro' => $bairro,
        ':complemento' => $complemento,
        ':cidade' => $cidade,
        ':estado' => $estado,
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Cadastro realizado com sucesso!'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao cadastrar: ' . $e->getMessage()
    ]);
}
?>
