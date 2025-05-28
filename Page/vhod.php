<?php
session_start();
require_once 'db_connect.php';

// Проверяем, если пользователь уже вошел в систему
if (isset($_SESSION['user_id'])) {
    header("Location: personal.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM User WHERE Login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['pass'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['iduser'];
            $_SESSION['username'] = $user['Login'];
            $_SESSION['role'] = $user['roles'];
            
            // Redirect based on role
            if ($user['roles'] === 'admin') {
                header("Location: AdminItem.php");
            } else {
                header("Location: personal.php"); // Changed from catalog.php to personal.php
            }
            exit();
        } else {
            $error = "Неверный логин или пароль!";
        }
    } catch(PDOException $e) {
        $error = "Ошибка при входе: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="..\Css\vhod.css">
</head>
<body>
<div class="container">
    <div class="rectangle">
        <div class="image-container">
            <!-- Используем относительный путь -->
            <img src="../Media/vhod.png" alt="Логотип входа"/>
        </div>
        <div class="form-container">
            <h2>Вход</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="Имя пользователя"/>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Пароль"/>
                </div>
                <button class="btn" type="submit">Войти</button>
            </form>
            <div class="registration-link">
                <p>Нет аккаунта? <a href="../Page/reg.php">Регистрация</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>