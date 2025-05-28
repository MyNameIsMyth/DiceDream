<?php
session_start();
require_once 'db_connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: vhod.php');
    exit();
}

// Получаем товары из корзины пользователя
try {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT c.*, i.ItemName, i.Price, i.img 
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
    <title>Корзина</title>
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>

<div class="App">
    <header class="header">
        <a href="../index.php">
            <img src="../Media/logo.png" alt="Логотип" class="logo"/>
        </a>
        <input class="search-input" placeholder="Название товара" type="text" />
        <div class="button-container">
            <a href="personal.php" class="icon-button">
                <img alt="user" src="../Media/user-icon.png" />
            </a>
            <a href="fav.php" class="icon-button">
                <img alt="love" src="../Media/love-icon.png" />
            </a>
            <a href="busket.php" class="icon-button">
                <img alt="store" src="../Media/store-icon.png" />
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
    <div class="basket-container">


    <div class="container">
        <h1>Корзина</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <a href="../index.php" class="btn">Перейти к покупкам</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['idItem']; ?>">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($item['img']); ?>" 
                             alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                             class="cart-item-image">
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['ItemName']); ?></h3>
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
    <div class="footer-logo">
        <img src="..\Media\logo.png" alt="логотип"/>
    </div>
    <div class="footer-content">
        <div class="footer-section">
            <h4>Страницы</h4>
            <ul>
                <li><a href="/">Главная</a></li>
                <li><a href="/catalog">Каталог</a></li>
                <li><a href="/basket">Корзина</a></li>
                <li><a href="/favourites">Избранное</a></li>
                <li><a href="/personal">Профиль</a></li>
                <li><a href="/delivery">Доставка</a></li>
                <li><a href="/purchases">Покупки</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Услуги</h4>
            <ul>
                <li><a href="/delivery">Доставка</a></li>
                <li><a href="/support">Служба поддержки</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Документация</h4>
            <ul>
                <li><a href="/delivery-terms">Условия доставки</a></li>
                <li><a href="/storage-terms">Условия хранения</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-qr">
        <div class="qr-code">
            <img src="..\Media\qr.png" alt="QR Код"/>
        </div>
    </div>
</footer>
</div>
<script src="script.js"></script>

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
                body: `item_id=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Перезагружаем страницу для обновления данных
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Произошла ошибка при обновлении корзины', 'error');
            });
        }

        // Функция удаления товара
        function removeItem(itemId) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Перезагружаем страницу для обновления данных
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Произошла ошибка при удалении товара', 'error');
            });
        }
    });
    </script>
</body>
</html>