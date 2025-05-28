<?php
session_start();
require_once 'db_connect.php';
//ГДЗ по математике за третий класс
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    
    // Validate input
    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают!";
    } else {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM User WHERE Login = ? OR email = ?");
        $stmt->execute([$login, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Пользователь с таким логином или email уже существует!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $conn->prepare("INSERT INTO User (Login, email, pass, roles) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$login, $email, $hashed_password]);
                
                $_SESSION['success'] = "Регистрация успешна! Теперь вы можете войти.";
                header("Location: vhod.php");
                exit();
            } catch(PDOException $e) {
                $error = "Ошибка при регистрации: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="..\Css\reg.css">
</head>
<body>
<div class="container">
    <div class="rectangle">
        <div class="image-container">
            <img src="..\Media\vhod.png" alt="Логотип сайта"/>
        </div>
        <div class="form-container">
            <h1 class="login-title">Регистрация</h1>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="Имя пользователя" required/>
                </div>
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Электронная почта" required/>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Пароль" required/>
                </div>
                <div class="input-group">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Подтверждение пароля" required/>
                </div>
                <div class="input-group checkbox">
                    <label>
                        <input type="checkbox" required/> Я принимаю условия Пользовательского соглашения
                    </label>
                </div>
                <button class="btn" type="submit">Зарегистрироваться</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>