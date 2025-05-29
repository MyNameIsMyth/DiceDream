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

$pageTitle = 'Личный кабинет';
include 'template/header_personal.php';
?>

<link rel="stylesheet" href="/Css/footer.css">
<link rel="stylesheet" href="/Css/catalog.css">

<style>
    .personal-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .profile-header {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 60px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .profile-avatar:hover {
        background: #e0e0e0;
    }

    .profile-info {
        flex-grow: 1;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .profile-email {
        color: #666;
        margin-bottom: 15px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .card-icon {
        width: 40px;
        height: 40px;
        background: #e7f1ff;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-icon img {
        width: 24px;
        height: 24px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }

    .card-content {
        color: #666;
    }

    .action-button {
        display: inline-block;
        background: #CACCC1;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        margin-top: 15px;
        transition: background-color 0.3s;
        outline: none !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
        border: none !important;
    }

    .action-button:hover {
        background: #b8baa9;
        transform: translateY(-2px);
    }

    .action-button:focus {
        outline: none !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
    }

    .action-button:active {
        outline: none !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
    }

    .logout-button {
        background: #dc3545;
        border: none;
        color: white;
        padding: 14px 28px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .logout-button:hover {
        background: #c82333;
    }

    .admin-button {
        background-color: #dc3545 !important;
        color: white !important;
        transition: all 0.3s ease;
    }

    .admin-button:hover {
        background-color: #c82333 !important;
        transform: translateY(-2px);
    }

    /* Модальное окно */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: white;
        margin: 15% auto;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        position: relative;
    }

    .close {
        position: absolute;
        right: 20px;
        top: 15px;
        font-size: 28px;
        cursor: pointer;
        color: #666;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .form-group input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }

    .save-button {
        background: #28a745;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s;
    }

    .save-button:hover {
        background: #218838;
    }

    
</style>

<div class="personal-container">
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

    <!-- Профиль -->
    <div class="profile-header">
        <div class="profile-avatar" onclick="openEditModal()">
            <?php echo strtoupper(substr($user['Login'], 0, 1)); ?>
        </div>
        <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user['Login']); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
            <button class="action-button" onclick="openEditModal()">Редактировать профиль</button>
        </div>
    </div>

    <!-- Карточки -->
    <div class="profile-grid">
        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <img src="../Media/icon3.png" alt="Заказы"/>
                </div>
                <div class="card-title">Мои заказы</div>
            </div>
            <div class="card-content">
                Просмотр истории заказов и их статусов
            </div>
            <a href="orders.php" class="action-button">Перейти к заказам</a>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <img src="../Media/icon2.png" alt="Избранное"/>
                </div>
                <div class="card-title">Избранное</div>
            </div>
            <div class="card-content">
                Сохраненные товары и желаемые покупки
            </div>
            <a href="fav.php" class="action-button">Смотреть избранное</a>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <img src="../Media/icon1.png" alt="Настройки"/>
                </div>
                <div class="card-title">Настройки</div>
            </div>
            <div class="card-content">
                Управление аккаунтом и уведомлениями
            </div>
            <button onclick="openEditModal()" class="action-button">Изменить настройки</button>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="profile-card">
            <div class="card-header">
                <div class="card-icon">
                    <img src="../Media/icon1.png" alt="Админ панель"/>
                </div>
                <div class="card-title">Админ панель</div>
            </div>
            <div class="card-content">
                Управление товарами, пользователями и заказами
            </div>
            <a href="AdminItem.php" class="action-button admin-button">Войти в админ панель</a>
        </div>
        <?php endif; ?>
    </div>

    <form method="POST" style="text-align: center;">
        <button type="submit" name="logout" class="logout-button">Выйти из аккаунта</button>
    </form>
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

<!-- Footer -->
<footer class="footer">
    <div class="footer-logo">
        <img src="/Media/logo.png" alt="логотип"/>
    </div>
    <div class="footer-content">
        <div class="footer-section">
            <h4>Страницы</h4>
            <ul>
                <li><a href="/">Главная</a></li>
                <li><a href="/Page/catalog.php">Каталог</a></li>
                <li><a href="/Page/busket.php">Корзина</a></li>
                <li><a href="/Page/fav.php">Избранное</a></li>
                <li><a href="/Page/personal.php">Профиль</a></li>
                <li><a href="/Page/delivery.php">Доставка</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Услуги</h4>
            <ul>
                <li><a href="/Page/delivery.php">Доставка</a></li>
                <li><a href="/Page/support.php">Служба поддержки</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Документация</h4>
            <ul>
                <li><a href="/Page/delivery-terms.php">Условия доставки</a></li>
                <li><a href="/Page/storage-terms.php">Условия хранения</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-qr">
        <div class="qr-code">
            <img src="/Media/qr.png" alt="QR Код"/>
        </div>
    </div>
</footer>

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

        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Пожалуйста, введите корректный email адрес');
            return;
        }
    });
</script>

</body>
</html>
