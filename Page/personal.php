<?php
require_once 'db_connect.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: vhod.php");
    exit();
}

// Получение данных пользователя
$stmt = $conn->prepare("SELECT * FROM User WHERE iduser = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Обработка выхода
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: vhod.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="..\Css\product.css">
    <style>
        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #666;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .save-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .save-button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            margin-top: 5px;
            font-size: 14px;
        }

        /* Добавим стили для сообщений */
        .alert {
            padding: 15px;
            margin: 20px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<div class="App">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <header class="header">
        <a href="../index.php">
            <img src="./images/Используются везде/logo.png" alt="Логотип" class="logo"/>
        </a>
        <input style="width: 500px;" placeholder="Название товара" type="text" />
        <div class="button-container">
            <a href="/personal" class="icon-button">
                <img alt="user" src="./images/Используются везде/user-icon.png" />
            </a>
            <a href="/favourites" class="icon-button">
                <img alt="love" src="./images/Используются везде/love-icon.png" />
            </a>
            <a href="/basket" class="icon-button">
                <img alt="store" src="./images/Используются везде/store-icon.png" />
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

    <div style="margin-top: 20px; font-size: 24px; font-weight: bold;">Личный кабинет</div>

    <div class="content-container">
        <div class="rectangle" style="
            width: 710px;
            height: 540px;
            background-color: #F9FBEF;
            margin-top: 90px;
            margin-left: 240px;
            position: relative;
        ">
            <div class="avatar-upload">
                <input type="file" id="avatar-input" accept="image/*" style="display: none;"/>
                <label for="avatar-input" class="avatar-label">
                    + <!-- Если аватарка не загружена -->
                </label>
            </div>

            <div class="small-rectangles-container">
                <div class="small-rectangle"></div>
                <div class="small-rectangle"></div>
            </div>

            <div class="buttons-below-container">
                <button class="large-button">Выбрать способ оплаты</button>
                <button class="large-button">Ваши устройства</button>
            </div>
        </div>

        <div class="buttons-right-container">
            <button class="profile-button" onclick="openEditModal()">Редактировать профиль</button>
            <a href="/favourites" class="profile-button">Избранное</a>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
            <a href="/user" class="profile-button">Админ</a>
            <?php endif; ?>
            <a href="/buy" class="profile-button">Покупки</a>
            <form method="POST" style="width: 100%;">
                <button type="submit" name="logout" class="profile-button logout-button">Выйти</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно для редактирования профиля -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Редактирование профиля</h2>
            <form id="editProfileForm" method="POST" action="update_profile.php">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['Login'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="adress">Адрес</label>
                    <input type="text" id="adress" name="adress" value="<?php echo htmlspecialchars($user['adress'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="new_password">Новый пароль (оставьте пустым, если не хотите менять)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтвердите новый пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <button type="submit" class="save-button">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <footer class="footer" style="margin-top: 50px;">
        <div class="footer-logo">
            <img src="./images/Используются везде/logo.png" alt="logofooter" />
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
                <img src="./images/Используются везде/qr-code.png" alt="QR Code" />
            </div>
        </div>
    </footer>
</div>

<script>
    // Функции для работы с модальным окном
    function openEditModal() {
        document.getElementById('editProfileModal').style.display = 'block';
    }

    function closeEditModal() {
        document.getElementById('editProfileModal').style.display = 'none';
    }

    // Закрытие модального окна при клике вне его
    window.onclick = function(event) {
        const modal = document.getElementById('editProfileModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Валидация формы перед отправкой
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Пароли не совпадают!');
            return;
        }

        const phone = document.getElementById('phone').value;
        const phoneRegex = /^\+?[0-9]{10,15}$/;
        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Пожалуйста, введите корректный номер телефона');
            return;
        }

        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Пожалуйста, введите корректный email адрес');
            return;
        }
    });
</script>

<style>
.logout-button {
    background-color: #dc3545;
    color: white;
    border: none;
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.logout-button:hover {
    background-color: #c82333;
}

.profile-button {
    display: block;
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    text-align: center;
    text-decoration: none;
    color: #000;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.3s;
}

.profile-button:hover {
    background-color: #e9ecef;
}
</style>
</body>
</html>