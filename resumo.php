<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

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

$sqlPedido = "SELECT ip.id, i.nome AS item_nome, ip.quantidade, i.preco, (ip.quantidade * i.preco) AS preco_total
              FROM tb_itens_pedido ip
              JOIN tb_itens i ON ip.idItem = i.id
              WHERE ip.idUsuario = :idUsuario AND ip.finalizado = FALSE";
$stmtPedido = $pdo->prepare($sqlPedido);
$stmtPedido->execute([':idUsuario' => $_SESSION['user']['id']]);
$itensPedido = $stmtPedido->fetchAll(PDO::FETCH_ASSOC);


$pedidoConfirmado = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['alterar_quantidade']) && isset($_POST['item_id']) && isset($_POST['quantidade'])) {
        $itemId = $_POST['item_id'];
        $quantidade = $_POST['quantidade'];
        
        if ($quantidade < 1) {
            $quantidade = 1; 
        }

        $sqlAtualizaQuantidade = "UPDATE tb_itens_pedido 
                                  SET quantidade = :quantidade 
                                  WHERE id = :itemId AND idUsuario = :idUsuario AND finalizado = FALSE";
        $stmtAtualizaQuantidade = $pdo->prepare($sqlAtualizaQuantidade);
        $stmtAtualizaQuantidade->execute([ 
            ':quantidade' => $quantidade,
            ':itemId' => $itemId,
            ':idUsuario' => $_SESSION['user']['id']
        ]);
        
        header('Location: resumo.php'); 
        exit;
    }


    if (isset($_POST['remover_item']) && isset($_POST['item_id'])) {
        $itemId = $_POST['item_id'];
        
        $sqlRemoveItem = "DELETE FROM tb_itens_pedido WHERE id = :itemId AND idUsuario = :idUsuario AND finalizado = FALSE";
        $stmtRemoveItem = $pdo->prepare($sqlRemoveItem);
        $stmtRemoveItem->execute([ 
            ':itemId' => $itemId,
            ':idUsuario' => $_SESSION['user']['id']
        ]);
        
        header('Location: resumo.php'); 
        exit;
    }


    if (isset($_POST['confirmar_pedido'])) {
 
        $sqlFinalizarPedido = "UPDATE tb_itens_pedido 
                               SET finalizado = TRUE 
                               WHERE idUsuario = :idUsuario AND finalizado = FALSE";
        $stmtFinalizarPedido = $pdo->prepare($sqlFinalizarPedido);
        $stmtFinalizarPedido->execute([':idUsuario' => $_SESSION['user']['id']]);


        $pedidoConfirmado = true;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo do Pedido</title>
    <link rel="stylesheet" href="css/style.css">

    <style>

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup button:hover {
            background-color: #45a049;
        }


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
            background-color: #333;
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
            background-color: #D64545;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>

    <script>

        function redirecionar() {
            setTimeout(function() {
                window.location.href = "cardapio.php";
            }, 3000); 
        }


        <?php if ($pedidoConfirmado): ?>
            window.onload = function() {
                document.getElementById('popup').style.display = 'flex';
                redirecionar(); 
            };
        <?php endif; ?>
    </script>

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
                    <p class="nome"><?php echo htmlspecialchars($_SESSION['user']['nome']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
                    <p class="logout" onclick="window.location.href='logout.php'">Sair</p>
                </div>
            </div>
        </div>
    </div>
    <h1>Resumo do Pedido</h1>

<?php if (empty($itensPedido)): ?>
    <p>Você não tem itens no seu pedido.</p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f4f4f4; text-align: left;">
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Item</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Quantidade</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Preço Unitário</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Preço Total</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Alterar</th>
                <th style="padding: 10px; border-bottom: 1px solid #ddd;">Remover</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itensPedido as $item): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 10px;"><?php echo htmlspecialchars($item['item_nome']); ?></td>
                    <td style="padding: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" style="width: 50px; text-align: center;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="alterar_quantidade" style="padding: 5px 10px; background-color: #007BFF; color: #fff; border: none; border-radius: 3px; cursor: pointer;">Alterar</button>
                        </form>
                    </td>
                    <td style="padding: 10px;">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                    <td style="padding: 10px;">R$ <?php echo number_format($item['preco_total'], 2, ',', '.'); ?></td>
                    <td style="padding: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="remover_item" style="padding: 5px 10px; background-color: #DC3545; color: #fff; border: none; border-radius: 3px; cursor: pointer;">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 18px;">
        <strong>Total do Pedido: R$ 
            <?php 
                $totalPedido = array_sum(array_column($itensPedido, 'preco_total'));
                echo number_format($totalPedido, 2, ',', '.');
            ?>
        </strong>
    </p>

    <form method="POST" style="margin-top: 20px;">
        <button type="submit" name="confirmar_pedido" style="padding: 10px 20px; background-color: #28A745; color: #fff; border: none; border-radius: 3px; cursor: pointer;">Confirmar Pedido</button>
    </form>
<?php endif; ?>


    <div id="popup" class="popup">
        <div class="popup-content">
            <h2>Pedido confirmado com sucesso!</h2>
            <button onclick="window.location.href='cardapio.php'">Fechar</button>
        </div>
    </div>
    
</body>
</html>
