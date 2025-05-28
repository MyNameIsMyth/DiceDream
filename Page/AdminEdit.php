<?php
require_once 'db_connect.php';
session_start();

// Получение списка категорий
$stmt = $conn->prepare("SELECT * FROM Category ORDER BY nameCategory");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение данных товара для редактирования
$editItem = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("
        SELECT i.*, c.nameCategory 
        FROM Item i 
        LEFT JOIN Category c ON i.idCategory = c.idCategory 
        WHERE i.idItem = ?
    ");
    $stmt->execute([$_GET['edit_id']]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Обработка обновления товара
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_item'])) {
    $id = $_POST['item_id'];
    $itemName = $_POST['ItemName'];
    $limitation = $_POST['Limitation'];
    $genre = $_POST['Genre'];
    $count = $_POST['Count'];
    $gameTime = $_POST['GameTime'];
    $description = $_POST['DescriptionItem'];
    $price = $_POST['Price'];
    $categoryId = $_POST['category'];

    if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
        // Если загружено новое изображение
        $imgData = file_get_contents($_FILES['img']['tmp_name']);
        $stmt = $conn->prepare("UPDATE Item SET ItemName = ?, Limitation = ?, Genre = ?, Count = ?, GameTime = ?, DescriptionItem = ?, Price = ?, img = ?, idCategory = ? WHERE idItem = ?");
        $stmt->execute([$itemName, $limitation, $genre, $count, $gameTime, $description, $price, $imgData, $categoryId, $id]);
    } else {
        // Если изображение не изменилось
        $stmt = $conn->prepare("UPDATE Item SET ItemName = ?, Limitation = ?, Genre = ?, Count = ?, GameTime = ?, DescriptionItem = ?, Price = ?, idCategory = ? WHERE idItem = ?");
        $stmt->execute([$itemName, $limitation, $genre, $count, $gameTime, $description, $price, $categoryId, $id]);
    }
    
    header("Location: AdminEdit.php");
    exit();
}

// Получение списка всех товаров с категориями
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
    <title>Редактирование товара</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .admin-edit-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-edit-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }
        .admin-edit-nav-item {
            margin: 0;
        }
        .admin-edit-nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .admin-edit-nav-link:hover {
            background-color: #34495e;
        }
        .admin-edit-container-wrapper {
            display: flex;
            gap: 20px;
            margin: 20px auto;
            padding: 20px;
            max-width: 1400px;
        }
        .admin-edit-data-container {
            flex: 2;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-edit-side-container {
            flex: 1;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-edit-table-header {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .admin-edit-table-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            padding: 12px;
            border-bottom: 1px solid #eee;
            align-items: center;
            transition: background-color 0.2s;
        }
        .admin-edit-table-row:hover {
            background-color: #f8f9fa;
        }
        .admin-edit-column {
            text-align: center;
        }
        .admin-edit-input-group {
            margin-bottom: 20px;
        }
        .admin-edit-input-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        .admin-edit-input-group input,
        .admin-edit-input-group textarea,
        .admin-edit-input-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .admin-edit-input-group input:focus,
        .admin-edit-input-group textarea:focus,
        .admin-edit-input-group select:focus {
            border-color: #2c3e50;
            outline: none;
        }
        .admin-edit-upload-button {
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
        .admin-edit-upload-button:hover {
            background-color: #2980b9;
        }
        .admin-edit-save-button {
            background-color: #2ecc71;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }
        .admin-edit-save-button:hover {
            background-color: #27ae60;
        }
        .admin-edit-edit-button {
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .admin-edit-edit-button:hover {
            background-color: #2980b9;
        }
        .admin-edit-image-preview {
            margin: 15px 0;
            padding: 15px;
            border: 2px dashed #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .admin-edit-image-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div>
    <header class="admin-edit-header">
        <nav class="admin-edit-nav">
            <ul class="admin-edit-nav-list">
                <li class="admin-edit-nav-item">
                    <a href="AdminItem.php" class="admin-edit-nav-link">Управление товаром</a>
                </li>
                <li class="admin-edit-nav-item">
                    <a href="AdminEdit.php" class="admin-edit-nav-link">Редактирование товара</a>
                </li>
                <li class="admin-edit-nav-item">
                    <a href="AdminUser.php" class="admin-edit-nav-link">Пользователи</a>
                </li>
                <li class="admin-edit-nav-item">
                    <a href="vhod.php" class="admin-edit-nav-link">Выход</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="admin-edit-container-wrapper">
        <div class="admin-edit-data-container">
            <div class="admin-edit-table-header">
                <div class="admin-edit-column-id">Id товара</div>
                <div class="admin-edit-column-name">Имя товара</div>
                <div class="admin-edit-column-price">Цена</div>
                <div class="admin-edit-column-quantity">Ограничение</div>
                <div class="admin-edit-column-actions">Редактирование</div>
            </div>

            <?php foreach ($items as $item): ?>
            <div class="admin-edit-table-row">
                <div class="admin-edit-column-id"><?php echo htmlspecialchars($item['idItem']); ?></div>
                <div class="admin-edit-column-name"><?php echo htmlspecialchars($item['ItemName']); ?></div>
                <div class="admin-edit-column-price"><?php echo htmlspecialchars($item['Price']); ?> ₽</div>
                <div class="admin-edit-column-quantity"><?php echo htmlspecialchars($item['Limitation']); ?></div>
                <div class="admin-edit-column-actions">
                    <a href="?edit_id=<?php echo $item['idItem']; ?>" class="admin-edit-edit-button">Редактировать</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="admin-edit-side-container">
            <h2>Редактирование товара</h2>
            <?php if ($editItem): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo $editItem['idItem']; ?>">
                
                <div class="admin-edit-image-preview">
                    <?php if ($editItem['img']): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($editItem['img']); ?>" alt="Текущее изображение" style="max-width: 200px;">
                    <?php else: ?>
                        <span>Изображение отсутствует</span>
                    <?php endif; ?>
                </div>

                <label class="admin-edit-upload-button">
                    Изменить изображение
                    <input type="file" name="img" accept="image/*" style="display: none;">
                </label>

                <div class="admin-edit-input-group">
                    <label>Название</label>
                    <input type="text" name="ItemName" value="<?php echo htmlspecialchars($editItem['ItemName']); ?>" required>
                </div>

                <div class="admin-edit-input-group">
                    <label>Возраст</label>
                    <input type="text" name="Limitation" value="<?php echo htmlspecialchars($editItem['Limitation']); ?>" required>
                </div>

                <div class="admin-edit-input-group">
                    <label>Жанр</label>
                    <input type="text" name="Genre" value="<?php echo htmlspecialchars($editItem['Genre']); ?>" required>
                </div>

                <div class="admin-edit-input-group">
                    <label>Количество игроков</label>
                    <input type="text" name="Count" value="<?php echo htmlspecialchars($editItem['Count']); ?>" required>
                </div>

                <div class="admin-edit-input-group">
                    <label>Время игры</label>
                    <input type="text" name="GameTime" value="<?php echo htmlspecialchars($editItem['GameTime']); ?>" required>
                </div>

                <div class="admin-edit-input-group">
                    <label>Описание</label>
                    <textarea name="DescriptionItem" required><?php echo htmlspecialchars($editItem['DescriptionItem']); ?></textarea>
                </div>

                <div class="admin-edit-input-group">
                    <label>Категория</label>
                    <select name="category" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['idCategory']; ?>" 
                                <?php echo ($editItem['idCategory'] == $category['idCategory']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['nameCategory']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-edit-input-group">
                    <label>Цена</label>
                    <input type="number" name="Price" value="<?php echo htmlspecialchars($editItem['Price']); ?>" required>
                </div>

                <button type="submit" name="update_item" class="admin-edit-save-button">Сохранить изменения</button>
            </form>
            <?php else: ?>
            <p>Выберите товар для редактирования</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>