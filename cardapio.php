<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.html');
    exit;
}

$user = $_SESSION['user'];


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


$sqlCategorias = "SELECT * FROM tb_categoria";
$stmtCategorias = $pdo->prepare($sqlCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

if (empty($categorias)) {
    echo "Nenhuma categoria encontrada no banco de dados.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .navbar {
            background: linear-gradient(to right, #fff, #fff);
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .icons {
            display: flex;
            gap: 15px;
        }

        .profile-icon, .cart-icon {
            position: relative;
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .profile-icon img, .cart-icon img {
            width: 25px;
        }

        .tooltip {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: #fff;
            padding: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 10;
        }

        .tooltip p {
            font-size: 14px;
            margin: 5px 0;
        }

        .tooltip .logout {
            color: #D64545;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
        }

       
        .menu-container {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .category h2 {
            font-size: 20px;
            color: #D64545;
            margin-bottom: 15px;
        }

        .item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            text-align: center;
            transition: box-shadow 0.3s ease;
        }

        .item:hover {
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        }

        .item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .item h3 {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        .item p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }

        .item a {
            display: inline-block;
            background: #D64545;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 3px;
            margin: 10px 0;
            transition: background 0.3s ease;
        }

        .item a:hover {
            background: #c13737;
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
        


   
    <div class="menu-container">
    <?php foreach ($categorias as $categoria): ?>
        
        <div class="category">
            <h2><?php echo htmlspecialchars($categoria['nome']); ?></h2>
            <div class="items">
                <?php
                
                $sqlItens = "SELECT * FROM tb_itens WHERE idCategoria = :idCategoria";
                $stmtItens = $pdo->prepare($sqlItens);
                $stmtItens->execute([':idCategoria' => $categoria['id']]);
                $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                

                if (empty($itens)) {
                    echo "<p>Não há itens disponíveis nesta categoria.</p>";
                } else {
                    foreach ($itens as $item):
                        
                    

if (!empty($item['foto'])) {
    $fotoBinario = stream_get_contents($item['foto']);
    $foto = ($fotoBinario); 
} else {
    $foto = 'default.jpg'; 
}

                        
                        
                ?>
                    <div class="item">
                    <img src="<?php echo $foto; ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
        <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
        <p><?php echo htmlspecialchars($item['descricao']); ?></p>
        <p><strong>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong></p>
        <a href="detalhes.php?id=<?php echo $item['id']; ?>">Ver Detalhes</a>
                    </div>
                <?php
                    endforeach;
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <script>
    const profileIcon = document.getElementById('profileIcon');
    const tooltip = document.getElementById('tooltip');
    let hideTimeout;

    profileIcon.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeout);
        tooltip.style.display = 'block';
    });

    tooltip.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeout); 
        tooltip.style.display = 'block';
    });

    profileIcon.addEventListener('mouseleave', function() {
        hideTimeout = setTimeout(() => {
            tooltip.style.display = 'none';
        }, 300); 
    });

    tooltip.addEventListener('mouseleave', function() {
        hideTimeout = setTimeout(() => {
            tooltip.style.display = 'none';
        }, 300); 
    });
</script>

</body>
</html>
