<?php
session_start();
require_once 'db_connect.php';
require_once 'utils.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: vhod.php');
    exit();
}

// Получаем товары из корзины пользователя
try {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT c.*, i.ItemName, i.Price, i.img_path, i.Genre, i.Count 
        FROM Cart c 
        JOIN Item i ON c.idItem = i.idItem 
        WHERE c.idUser = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Подсчитываем общую стоимость
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $totalPrice += $item['Price'] * $item['quantity'];
    }
} catch(PDOException $e) {
    $error = "Ошибка при получении данных корзины: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - DiceDream</title>
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
        <h1 class="page-title">Корзина</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <img src="../Media/empty-cart.png" alt="Корзина пуста" class="empty-icon">
                <h2>Ваша корзина пуста</h2>
                <p>Добавьте товары в корзину, чтобы оформить заказ</p>
                <a href="catalog.php" class="primary-button">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['idItem']; ?>">
                        <img src="<?php echo getProductImage($item['ItemName'], $item['img_path']); ?>" 
                             alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                             onerror="this.src='../Media/logo.png'"
                             class="cart-item-image">
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                            <div class="cart-item-meta">
                                <span class="cart-item-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                <span class="cart-item-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                            </div>
                            <p class="cart-item-price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</p>
                            <div class="quantity-controls">
                                <button class="quantity-btn minus" data-action="decrease">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                                <button class="quantity-btn plus" data-action="increase">+</button>
                            </div>
                            <button class="remove-item">Удалить</button>
                        </div>
                        <div class="cart-item-total">
                            <?php echo number_format($item['Price'] * $item['quantity'], 0, '', ' '); ?>₽
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="total-price">
                    Итого: <span><?php echo number_format($totalPrice, 0, '', ' '); ?>₽</span>
                </div>
                <button class="checkout-btn">Оформить заказ</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка изменения количества
        document.querySelectorAll('.quantity-controls').forEach(control => {
            const input = control.querySelector('.quantity-input');
            const itemId = control.closest('.cart-item').dataset.itemId;

            control.addEventListener('click', function(e) {
                if (e.target.classList.contains('quantity-btn')) {
                    const action = e.target.dataset.action;
                    let value = parseInt(input.value);

                    if (action === 'increase' && value < 99) {
                        value++;
                    } else if (action === 'decrease' && value > 1) {
                        value--;
                    }

                    updateQuantity(itemId, value);
                }
            });

            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (value < 1) value = 1;
                if (value > 99) value = 99;
                this.value = value;
                updateQuantity(itemId, value);
            });
        });

        // Обработка удаления товара
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.closest('.cart-item').dataset.itemId;
                removeItem(itemId);
            });
        });

        // Функция обновления количества
        function updateQuantity(itemId, quantity) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `idItem=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification(data.message || 'Произошла ошибка', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showNotification('Произошла ошибка при обновлении корзины', 'error');
            });
        }

        // Функция удаления товара
        function removeItem(itemId) {
            if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `idItem=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showNotification(data.message || 'Произошла ошибка', 'error');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showNotification('Произошла ошибка при удалении товара', 'error');
                });
            }
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
    </script>
</body>
</html>