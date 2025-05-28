<?php
require_once 'db_connect.php';
session_start();


// Обработка удаления пользователя
if (isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    
    // Удаляем связанные записи
    $stmt = $conn->prepare("DELETE FROM Cart WHERE idUser = ?");
    $stmt->execute([$id]);
    
    $stmt = $conn->prepare("DELETE FROM Rate WHERE idUser = ?");
    $stmt->execute([$id]);
    
    $stmt = $conn->prepare("DELETE FROM OrderItem WHERE idUser = ?");
    $stmt->execute([$id]);
    
    // Удаляем пользователя
    $stmt = $conn->prepare("DELETE FROM User WHERE iduser = ?");
    $stmt->execute([$id]);
    
    header("Location: AdminUser.php");
    exit();
}

// Получение списка пользователей
$stmt = $conn->prepare("SELECT * FROM User ORDER BY iduser DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора пользователей</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .admin-user-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-user-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }
        .admin-user-nav-item {
            margin: 0;
        }
        .admin-user-nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .admin-user-nav-link:hover {
            background-color: #34495e;
        }
        .admin-user-data-container {
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-user-table-header {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .admin-user-header-item {
            text-align: center;
            color: #2c3e50;
        }
        .admin-user-table-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            padding: 12px;
            border-bottom: 1px solid #eee;
            align-items: center;
            transition: background-color 0.2s;
        }
        .admin-user-table-row:hover {
            background-color: #f8f9fa;
        }
        .admin-user-column {
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-user-role-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            width: 100%;
            max-width: 150px;
        }
        .admin-user-role-select:hover {
            border-color: #2c3e50;
        }
        .admin-user-delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .admin-user-delete-btn:hover {
            background-color: #c0392b;
        }
        .admin-user-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div>
    <header class="admin-user-header">
        <nav class="admin-user-nav">
            <ul class="admin-user-nav-list">
                <li class="admin-user-nav-item">
                    <a href="AdminItem.php" class="admin-user-nav-link">Управление товаром</a>
                </li>
                <li class="admin-user-nav-item">
                    <a href="AdminEdit.php" class="admin-user-nav-link">Редактирование товара</a>
                </li>
                <li class="admin-user-nav-item">
                    <a href="AdminUser.php" class="admin-user-nav-link">Пользователи</a>
                </li>
                <li class="admin-user-nav-item">
                    <a href="vhod.php" class="admin-user-nav-link">Выход</a>
                </li>
            </ul>
        </nav>
    </header>
    <div class="admin-user-data-container">
        <h1 class="admin-user-title">Управление пользователями</h1>
        <div class="admin-user-table-header">
            <div class="admin-user-header-item">Id</div>
            <div class="admin-user-header-item">Логин</div>
            <div class="admin-user-header-item">Email</div>
            <div class="admin-user-header-item">Действия</div>
        </div>
        <?php foreach ($users as $user): ?>
        <div class="admin-user-table-row">
            <div class="admin-user-column"><?php echo htmlspecialchars($user['iduser']); ?></div>
            <div class="admin-user-column"><?php echo htmlspecialchars($user['Login']); ?></div>
            <div class="admin-user-column"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="admin-user-column">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['iduser']; ?>">
                    <button type="submit" name="delete_user" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')" class="admin-user-delete-btn">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>