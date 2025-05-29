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
    <title>Избранное - DiceDream</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/catalog.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo-link">
                <img src="../Media/logo.png" alt="Логотип" class="logo"/>
            </a>
            <form action="catalog.php" method="GET" class="search-form">
                <div class="search-wrapper">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Поиск настольных игр..." />
                    <button type="submit" class="search-button">
                        <img src="../Media/search-icon.png" alt="Поиск" class="search-icon">
                    </button>
                </div>
            </form>
            <div class="header-actions">
                <a href="personal.php" class="icon-button" title="Личный кабинет">
                    <img alt="Профиль" src="../Media/icon1.png" />
                </a>
                <a href="fav.php" class="icon-button" title="Избранное">
                    <img alt="Избранное" src="../Media/icon2.png" />
                </a>
                <a href="busket.php" class="icon-button" title="Корзина">
                    <img alt="Корзина" src="../Media/icon3.png" />
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM Cart WHERE idUser = ?");
                        $stmt->execute([$userId]);
                        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        if ($total > 0):
                        ?>
                            <span class="cart-count"><?php echo $total; ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Избранное</h1>
        
        <?php if (empty($favoriteItems)): ?>
            <div class="empty-favorites">
                <img src="../Media/empty-favorites.png" alt="Нет избранных товаров" class="empty-icon">
                <h2>В избранном пока нет товаров</h2>
                <p>Добавляйте понравившиеся товары в избранное, чтобы не потерять их</p>
                <a href="catalog.php" class="primary-button">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($favoriteItems as $item): ?>
                    <div class="product-card" data-item-id="<?php echo $item['idItem']; ?>">
                        <div class="product-image-container">
                            <img src="../Media/<?php echo htmlspecialchars($item['ItemName']); ?>.png" 
                                 alt="<?php echo htmlspecialchars($item['ItemName']); ?>"
                                 class="product-image" />
                            <button class="favorite-button active" onclick="toggleFavorite(<?php echo $item['idItem']; ?>)">
                                ❤️
                            </button>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                            <div class="product-meta">
                                <span class="product-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                <span class="product-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                            </div>
                            <div class="product-price">
                                <span class="price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</span>
                            </div>
                        </div>
                        <button class="add-to-cart-button" onclick="addToCart(<?php echo $item['idItem']; ?>)">
                            В корзину
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <a href="../index.php">
                    <img src="../Media/logo.png" alt="DiceDream" />
                </a>
            </div>
            <div class="footer-sections">
                <div class="footer-section">
                    <h4>Навигация</h4>
                    <ul>
                        <li><a href="../index.php">Главная</a></li>
                        <li><a href="catalog.php">Каталог</a></li>
                        <li><a href="busket.php">Корзина</a></li>
                        <li><a href="fav.php">Избранное</a></li>
                        <li><a href="personal.php">Личный кабинет</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Покупателям</h4>
                    <ul>
                        <li><a href="#">Доставка и оплата</a></li>
                        <li><a href="#">Возврат товара</a></li>
                        <li><a href="#">Бонусная программа</a></li>
                        <li><a href="#">Подарочные сертификаты</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Информация</h4>
                    <ul>
                        <li><a href="#">О компании</a></li>
                        <li><a href="#">Контакты</a></li>
                        <li><a href="#">Условия использования</a></li>
                        <li><a href="#">Политика конфиденциальности</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-qr">
                <div class="qr-code">
                    <img src="../Media/qr-code.png" alt="QR-код для скачивания приложения" />
                    <p class="qr-text">Скачайте наше приложение</p>
                </div>
            </div>
        </div>
    </footer>

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
                const card = document.querySelector(`.product-card[data-item-id="${itemId}"]`);
                card.remove();
                
                if (document.querySelectorAll('.product-card').length === 0) {
                    document.querySelector('.container').innerHTML = `
                        <div class="empty-favorites">
                            <img src="../Media/empty-favorites.png" alt="Нет избранных товаров" class="empty-icon">
                            <h2>В избранном пока нет товаров</h2>
                            <p>Добавляйте понравившиеся товары в избранное, чтобы не потерять их</p>
                            <a href="catalog.php" class="primary-button">Перейти в каталог</a>
                        </div>
                    `;
                }
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при обновлении избранного');
        });
    }

    function addToCart(itemId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `item_id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const button = event.target;
                button.classList.add('added');
                button.textContent = 'В корзине';
                setTimeout(() => {
                    button.classList.remove('added');
                    button.textContent = 'В корзину';
                }, 2000);
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при добавлении в корзину');
        });
    }
    </script>
</body>
</html>