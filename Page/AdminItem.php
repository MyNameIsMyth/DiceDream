<?php
require_once 'db_connect.php';
session_start();



// Получение списка категорий
$stmt = $conn->prepare("SELECT * FROM Category ORDER BY nameCategory");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка добавления нового товара
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $itemName = $_POST['ItemName'];
    $limitation = $_POST['Limitation'];
    $genre = $_POST['Genre'];
    $count = $_POST['Count'];
    $gameTime = $_POST['GameTime'];
    $description = $_POST['DescriptionItem'];
    $price = $_POST['Price'];
    $categoryId = $_POST['category'];

    // Обработка загрузки изображения
    if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
        $imgData = file_get_contents($_FILES['img']['tmp_name']);
        
        $stmt = $conn->prepare("INSERT INTO Item (ItemName, Limitation, Genre, Count, GameTime, DescriptionItem, Price, img, idCategory) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$itemName, $limitation, $genre, $count, $gameTime, $description, $price, $imgData, $categoryId]);
    } else {
        $stmt = $conn->prepare("INSERT INTO Item (ItemName, Limitation, Genre, Count, GameTime, DescriptionItem, Price, idCategory) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$itemName, $limitation, $genre, $count, $gameTime, $description, $price, $categoryId]);
    }
    
    header("Location: AdminItem.php");
    exit();
}

// Обработка удаления товара
if (isset($_POST['delete_item'])) {
    $id = $_POST['item_id'];
    
    // Удаляем связанные записи в Cart
    $stmt = $conn->prepare("DELETE FROM Cart WHERE idItem = ?");
    $stmt->execute([$id]);
    
    // Удаляем товар
    $stmt = $conn->prepare("DELETE FROM Item WHERE idItem = ?");
    $stmt->execute([$id]);
    
    header("Location: AdminItem.php");
    exit();
}

// Получение списка товаров с категориями
$stmt = $conn->prepare("
    SELECT i.*, c.nameCategory 
    FROM Item i 
    LEFT JOIN Category c ON i.idCategory = c.idCategory 
    ORDER BY i.idItem DESC
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора товаров</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .admin-item-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-item-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }
        .admin-item-nav-item {
            margin: 0;
        }
        .admin-item-nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .admin-item-nav-link:hover {
            background-color: #34495e;
        }
        .admin-item-content-container {
            display: flex;
            gap: 20px;
            margin: 20px auto;
            padding: 20px;
            max-width: 1400px;
        }
        .admin-item-data-container {
            flex: 2;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-item-side-container {
            flex: 1;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-item-table-header {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .admin-item-table-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            padding: 12px;
            border-bottom: 1px solid #eee;
            align-items: center;
            transition: background-color 0.2s;
        }
        .admin-item-table-row:hover {
            background-color: #f8f9fa;
        }
        .admin-item-column {
            text-align: center;
        }
        .admin-item-input-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        .admin-item-input-field {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .admin-item-input-field:focus {
            border-color: #2c3e50;
            outline: none;
        }
        .admin-item-textarea-field {
            min-height: 100px;
            resize: vertical;
        }
        .admin-item-upload-button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block;
            margin: 10px 0;
        }
        .admin-item-upload-button:hover {
            background-color: #2980b9;
        }
        .admin-item-save-button {
            background-color: #2ecc71;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            font-size: 16px;
        }
        .admin-item-save-button:hover {
            background-color: #27ae60;
        }
        .admin-item-side-container-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
        .admin-item-image-preview {
            margin: 15px 0;
            padding: 15px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .admin-item-preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        button[name="delete_item"] {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button[name="delete_item"]:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div>
    <header class="admin-item-header">
        <nav class="admin-item-nav">
            <ul class="admin-item-nav-list">
                <li class="admin-item-nav-item">
                    <a href="AdminItem.php" class="admin-item-nav-link"><button>Управление товаром</button></a>
                </li>
                <li class="admin-item-nav-item">
                    <a href="AdminEdit.php" class="admin-item-nav-link"><button>Редактирование товара</button></a>
                </li>
                <li class="admin-item-nav-item">
                    <a href="AdminUser.php" class="admin-item-nav-link">Пользователи</a>
                </li>
                <li class="admin-item-nav-item">
                    <a href="vhod.php" class="admin-item-nav-link">Выход</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="admin-item-content-container">
        <div class="admin-item-data-container">
            <div class="admin-item-table-header">
                <div class="admin-item-header-item">Id</div>
                <div class="admin-item-header-item">Название товара</div>
                <div class="admin-item-header-item">Цена</div>
                <div class="admin-item-header-item">Категория</div>
                <div class="admin-item-header-item">Удаление</div>
            </div>
            <?php foreach ($items as $item): ?>
            <div class="admin-item-table-row">
                <div class="admin-item-column"><?php echo htmlspecialchars($item['idItem']); ?></div>
                <div class="admin-item-column"><?php echo htmlspecialchars($item['ItemName']); ?></div>
                <div class="admin-item-column"><?php echo htmlspecialchars($item['Price']); ?> ₽</div>
                <div class="admin-item-column"><?php echo htmlspecialchars($item['nameCategory'] ?? 'Без категории'); ?></div>
                <div class="admin-item-column">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" value="<?php echo $item['idItem']; ?>">
                        <button type="submit" name="delete_item" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-item-side-container">
            <h2 class="admin-item-side-container-title">Добавить товар</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="admin-item-image-preview">
                    <img id="preview" src="#" alt="Предпросмотр" style="display: none; max-width: 200px;">
                    <span id="no-image">Изображение не выбрано</span>
                </div>
                <input
                    type="file"
                    id="admin-item-image-upload"
                    name="img"
                    accept="image/*"
                    style="display: none;"
                    onchange="previewImage(this);"
                    required
                />
                <label for="admin-item-image-upload" class="admin-item-upload-button">
                    Выбрать изображение
                </label>
                <div class="admin-item-input-group">
                    <input type="text" name="ItemName" placeholder="Название" class="admin-item-input-field" required />
                    <input type="text" name="Limitation" placeholder="Возрастное ограничение" class="admin-item-input-field" required />
                    <input type="text" name="Genre" placeholder="Жанр" class="admin-item-input-field" required />
                    <input type="text" name="Count" placeholder="Количество игроков" class="admin-item-input-field" required />
                    <input type="text" name="GameTime" placeholder="Время игры" class="admin-item-input-field" required />
                    <textarea name="DescriptionItem" placeholder="Описание" class="admin-item-input-field admin-item-textarea-field" required></textarea>
                    <input type="number" name="Price" placeholder="Цена" class="admin-item-input-field" required />
                    <select name="category" class="admin-item-input-field" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['idCategory']; ?>">
                                <?php echo htmlspecialchars($category['nameCategory']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="add_item" class="admin-item-save-button">Сохранить</button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const noImage = document.getElementById('no-image');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            noImage.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>