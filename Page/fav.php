<?php
session_start();
require_once 'db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: vhod.php');
    exit();
}

// Получаем избранные товары пользователя
try {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT i.*, f.idFavorite 
        FROM Item i 
        JOIN Favorites f ON i.idItem = f.idItem 
        WHERE f.idUser = ?
    ");
    $stmt->execute([$userId]);
    $favoriteItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное</title>
    <link rel="stylesheet" href="..\Css\fav.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .App {
            padding: 20px;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        input[type="text"] {
            width: 500px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .button-container a,
        .button-container button {
            margin-left: 10px;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        nav li {
            display: inline-block;
            margin-right: 10px;
        }
        .content {
            width: 100%;
            margin-top: 20px;
        }
        .new-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 35px;
            width: 100%;
        }
        .new-product-card {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .new-product-content h3 {
            margin-bottom: 5px;
        }
        .new-basket-button {
            cursor: pointer;
        }
        .advertisement img {
            max-width: 100%;
        }
        footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 50px;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        .favorite-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
        .favorite-button img {
            width: 24px;
            height: 24px;
        }
        .product-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .empty-favorites {
            text-align: center;
            padding: 40px;
            font-size: 18px;
        }
        .empty-favorites a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="App">
    <header class="header">
        <a href="../index.php">
            <img src="../Media/logo.png" alt="Логотип" class="logo"/>
        </a>
        <input style="width: 500px;" placeholder="Название товара" type="text" />
        <div class="button-container">
            <a href="personal.php" class="icon-button">
                <img alt="user" src="../Media/icon1.png" />
            </a>
            <a href="fav.php" class="icon-button">
                <img alt="love" src="../Media/icon2.png" />
            </a>
            <a href="busket.php" class="icon-button">
                <img alt="store" src="../Media/icon3.png" />
            </a>
        </div>
    </header>
    <nav class="nav">
        <ul>
            <li><a href="#veterinary">Ветеринарные</a></li>
            <li><a href="#adults">Для взрослых</a></li>
            <li><a href="#family">Для всей семьи</a></li>
            <li><a href="#children">Для детей</a></li>
            <li><a href="#classic">Классические</a></li>
            <li><a href="#plans">Планы</a></li>
            <li><a href="#strategic">Стратегические</a></li>
            <li><a href="#sales">Хаты продаж</a></li>
        </ul>
    </nav>
    <div class="content">
        <main class="new-product-grid">
            <?php if (empty($favoriteItems)): ?>
                <div class="empty-favorites">
                    <p>В избранном пока нет товаров</p>
                    <a href="catalog.php">Перейти в каталог</a>
                </div>
            <?php else: ?>
                <?php foreach ($favoriteItems as $item): ?>
                    <div class="new-product-card" data-item-id="<?php echo $item['idItem']; ?>">
                        <img src="../Media/<?php echo htmlspecialchars($item['ItemName']); ?>.png" 
                             alt="<?php echo htmlspecialchars($item['ItemName']); ?>" />
                        <div class="new-product-content">
                            <h3><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                            <p><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</p>
                        </div>
                        <div class="product-actions">
                            <button class="favorite-button" onclick="toggleFavorite(<?php echo $item['idItem']; ?>)">
                                <img src="../Media/icon2.png" alt="В избранном" />
                            </button>
                            <button class="new-basket-button" onclick="addToCart(<?php echo $item['idItem']; ?>)">
                                <img src="../Media/icon3.png" alt="В корзину" />
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    <footer class="footer">
        <div class="footer-logo">
            <img src="../Media/logo.png" alt="logofooter" />
        </div>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Страницы</h4>
                <ul>
                    <li><a href="#">Главная</a></li>
                    <li><a href="#">Каталог</a></li>
                    <li><a href="#">Корзина</a></li>
                    <li><a href="#">Избранное</a></li>
                    <li><a href="#">Профиль</a></li>
                    <li><a href="#">Доставка</a></li>
                    <li><a href="#">Покупки</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Услуги</h4>
                <ul>
                    <li><a href="#">Доставка</a></li>
                    <li><a href="#">Служба поддержки</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Документация</h4>
                <ul>
                    <li><a href="#">Условия доставки</a></li>
                    <li><a href="#">Условия хранения</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-qr">
            <div class="qr-code">
                <img src="../Media/qr-code.png" alt="QR Code" />
            </div>
        </div>
    </footer>
</div>

<script>
function toggleFavorite(itemId) {
    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Удаляем карточку товара из отображения
            const card = document.querySelector(`.new-product-card[data-item-id="${itemId}"]`);
            card.remove();
            
            // Если больше нет товаров, показываем сообщение
            if (document.querySelectorAll('.new-product-card').length === 0) {
                const main = document.querySelector('.new-product-grid');
                main.innerHTML = `
                    <div class="empty-favorites">
                        <p>В избранном пока нет товаров</p>
                        <a href="catalog.php">Перейти в каталог</a>
                    </div>
                `;
            }
        }
    })
    .catch(error => console.error('Ошибка:', error));
}

function addToCart(itemId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Товар добавлен в корзину');
        } else {
            alert(data.message || 'Произошла ошибка');
        }
    })
    .catch(error => console.error('Ошибка:', error));
}
</script>
</body>
</html>