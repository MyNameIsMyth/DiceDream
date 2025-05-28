<?php
session_start();
require_once 'db_connect.php';

// Проверяем, является ли пользователь администратором
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: vhod.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $itemName = $_POST['itemName'];
    $limitation = $_POST['limitation'];
    $genre = $_POST['genre'];
    $count = $_POST['count'];
    $gameTime = $_POST['gameTime'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Обработка загруженного изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        
        try {
            $stmt = $conn->prepare("INSERT INTO Item (ItemName, Limitation, Genre, Count, GameTime, DescriptionItem, Price, img) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$itemName, $limitation, $genre, $count, $gameTime, $description, $price, $image]);
            
            $_SESSION['success'] = "Товар успешно добавлен!";
            header('Location: AdminItem.php');
            exit();
        } catch(PDOException $e) {
            $error = "Ошибка при добавлении товара: " . $e->getMessage();
        }
    } else {
        $error = "Ошибка при загрузке изображения";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление товара</title>
    <link rel="stylesheet" href="../Css/upload_item.css">
</head>
<body>
    <div class="container">
        <h1>Добавление нового товара</h1>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="itemName">Название товара:</label>
                <input type="text" id="itemName" name="itemName" required>
            </div>
            
            <div class="form-group">
                <label for="limitation">Возрастное ограничение:</label>
                <input type="text" id="limitation" name="limitation" required>
            </div>
            
            <div class="form-group">
                <label for="genre">Жанр:</label>
                <input type="text" id="genre" name="genre" required>
            </div>
            
            <div class="form-group">
                <label for="count">Количество игроков:</label>
                <input type="text" id="count" name="count" required>
            </div>
            
            <div class="form-group">
                <label for="gameTime">Время игры:</label>
                <input type="text" id="gameTime" name="gameTime" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="number" id="price" name="price" required>
            </div>
            
            <div class="form-group">
                <label for="image">Изображение товара:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn">Добавить товар</button>
        </form>
    </div>
</body>
</html> 