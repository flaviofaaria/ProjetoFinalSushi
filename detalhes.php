<?php
session_start();


if (!isset($_SESSION['user'])) {
    header('Location: index.html'); 
    exit;
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Item inválido!";
    exit;
}

$itemId = $_GET['id'];


$host = 'localhost';
$dbname = 'sushi';
$user_db = 'postgres';
$password = 'user';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user_db, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro na conexão: ' . $e->getMessage()
    ]);
    exit;
}


$sqlItem = "SELECT * FROM tb_itens WHERE id = :id";
$stmtItem = $pdo->prepare($sqlItem);
$stmtItem->execute([':id' => $itemId]);
$item = $stmtItem->fetch(PDO::FETCH_ASSOC);


if (!$item) {
    echo "Item não encontrado!";
    exit;
}


if (!empty($item['foto'])) {
    $fotoBinario = stream_get_contents($item['foto']); 
    $foto = ($fotoBinario); 
} else {
    $foto = 'default.jpg'; 
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
    $preco = $item['preco'];

  
    $sqlPedido = "INSERT INTO tb_itens_pedido (idUsuario, idItem, quantidade, preco) 
                  VALUES (:idUsuario, :idItem, :quantidade, :preco)";
    $stmtPedido = $pdo->prepare($sqlPedido);
    $stmtPedido->execute([
        ':idUsuario' => $_SESSION['user']['id'],
        ':idItem' => $item['id'],
        ':quantidade' => $quantidade,
        ':preco' => $preco
    ]);

    $mensagem = "Item adicionado ao pedido com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Item</title>
    <link rel="stylesheet" href="css/style.css">
    <style>

        .menu-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            max-height: 100vh;
            overflow-y: auto; 
            width: 100%;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #fff;
            color: white;
            font-size: 18px;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .icons {
            display: flex;
            align-items: center;
        }

        .navbar .profile-icon, .navbar .cart-icon {
            position: relative;
            width: 40px;
            height: 40px;
            background-color: #fff;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 15px; 
        }

        .navbar .cart-icon img, .navbar .profile-icon img {
            width: 25px;
            height: 25px;
        }


        .tooltip {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #f1f1f1;
            color: #333;
            padding: 10px;
            border-radius: 5px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .tooltip p {
            margin: 5px 0;
            font-size: 14px;
        }

        .tooltip .nome {
            font-weight: bold;
        }

        .tooltip .logout {
            margin-top: 10px;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
            color: #f44336;
        }


        .menu-container {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
        }

        .category {
            width: 100%;
            margin-bottom: 20px;
        }

        .category h2 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .items {
            display: flex;
            flex-wrap: wrap;
        }

        .item {
            width: 30%;
            margin-right: 20px;
            margin-bottom: 20px;
        }

        .item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .item p {
            font-size: 14px;
        }

        .item a {
            display: inline-block;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
            background-color: #f44336;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .item a:hover {
            background-color: #d32f2f;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #fff;
            color: white;
        }

        .navbar .logo a {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }

        .navbar .icons {
            display: flex;
            align-items: center;
        }

        .navbar .profile-icon, .navbar .cart-icon {
            width: 40px;
            height: 40px;
            background-color: #fff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            margin-left: 10px;
        }

        .navbar .cart-icon img, .navbar .profile-icon img {
            width: 25px;
            height: 25px;
        }

        .item-details-container {
            display: flex;
            padding: 20px;
            gap: 20px;
        }

        .item-details-container .item-image img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item-details-container .item-info {
            flex: 1;
        }

        .item-details-container .item-info h2 {
            font-size: 24px;
            color: #D64545;
        }

        .item-details-container .item-info p {
            font-size: 16px;
            color: #666;
        }

        .item-details-container .item-info form {
            margin-top: 20px;
        }

        .item-details-container .item-info input[type="number"] {
            padding: 8px;
            width: 60px;
            margin-right: 10px;
        }

        .item-details-container .item-info button {
            padding: 10px 20px;
            background-color: #D64545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .item-details-container .item-info button:hover {
            background-color: #c13737;
        }

        .message {
            background-color: #28A745;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>


    <div class="navbar">
        <div class="logo">
    <a href="cardapio.php">
        <img src="img/sushi.svg" alt="Sushi" style="width: 40px; height: auto;">
    </a>
</div>
        
        <div class="icons">
            <a href="resumo.php" class="cart-icon">
                <img src="img/carrinho.svg" alt="Carrinho">
            </a>
        

            <div class="profile-icon" id="profileIcon">
                <img src="img/user.svg" alt="User">
                <div class="tooltip" id="tooltip">
                    <p class="nome"><?php echo htmlspecialchars($user['nome']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Rua:</strong> <?php echo htmlspecialchars($user['rua']); ?></p>
                    <p><strong>Cidade:</strong> <?php echo htmlspecialchars($user['cidade']); ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($user['estado']); ?></p>
                    <p class="logout" onclick="window.location.href='logout.php'">Sair</p>
                </div>
            </div>
        </div>
    </div>


    <div class="item-details-container">
        <div class="item-image">
            <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" style="width: 100%; max-height: 400px; object-fit: cover;">
        </div>

        <div class="item-info">
            <h2><?php echo htmlspecialchars($item['nome']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($item['descricao'])); ?></p>
            <p><strong>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong></p>
            
            <?php if (isset($mensagem)): ?>
                <div class="message"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <form method="post">
                <label for="quantidade">Quantidade:</label>
                <input type="number" id="quantidade" name="quantidade" value="1" min="1" required>
                <button type="submit" class="add-to-order-button">Adicionar ao Pedido</button>
            </form>
        </div>
    </div>

</body>
</html>
