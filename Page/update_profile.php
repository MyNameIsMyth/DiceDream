<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: vhod.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $login = trim($_POST['login']);
    $email = trim($_POST['email']);
    $adress = trim($_POST['adress']);
    $newPassword = trim($_POST['new_password']);

    try {
        // Начинаем транзакцию
        $conn->beginTransaction();

        // Проверяем, не занят ли email другим пользователем
        $stmt = $conn->prepare("SELECT iduser FROM User WHERE email = ? AND iduser != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Этот email уже используется другим пользователем";
            header('Location: personal.php');
            exit();
        }

        // Проверяем, не занят ли логин другим пользователем
        $stmt = $conn->prepare("SELECT iduser FROM User WHERE Login = ? AND iduser != ?");
        $stmt->execute([$login, $userId]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Этот логин уже используется другим пользователем";
            header('Location: personal.php');
            exit();
        }

        // Базовый SQL запрос для обновления данных
        $sql = "UPDATE User SET Login = ?, email = ?, adress = ?";
        $params = [$login, $email, $adress];

        // Если указан новый пароль, добавляем его в запрос
        if (!empty($newPassword)) {
            $sql .= ", pass = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE iduser = ?";
        $params[] = $userId;

        // Выполняем обновление
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Завершаем транзакцию
        $conn->commit();

        $_SESSION['success'] = "Профиль успешно обновлен";
    } catch (PDOException $e) {
        // Откатываем транзакцию в случае ошибки
        $conn->rollBack();
        $_SESSION['error'] = "Ошибка при обновлении профиля: " . $e->getMessage();
    }
}

// Перенаправляем обратно на страницу профиля
header('Location: personal.php');
exit(); 