<?php
session_start();
require_once 'db_connect.php';

// Функция для получения корректного пути к изображению
function getProductImage($imgPath, $itemName) {
    if (!empty($imgPath)) {
        // Получаем только имя файла из полного пути
        $fileName = basename($imgPath);
        // Проверяем существование файла в локальной директории Media
        if (file_exists('../Media/' . $fileName)) {
            return $fileName;
        }
    }
    
    // Если путь пустой или файл не существует, пробуем найти по имени товара
    $defaultFileName = mb_strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    if (file_exists('../Media/' . $defaultFileName)) {
        return $defaultFileName;
    }
    
    // Если ничего не найдено, возвращаем изображение по умолчанию
    return 'default.jpg';
}

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details with img_path
$stmt = $conn->prepare("SELECT i.*, c.nameCategory FROM Item i LEFT JOIN Category c ON i.idCategory = c.idCategory WHERE i.idItem = ?");
$stmt->execute([$productId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// If product not found, redirect to catalog
if (!$item) {
    header('Location: catalog.php');
    exit;
}

// Check if item is in favorites
$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT 1 FROM Favorites WHERE idUser = ? AND idItem = ?");
    $stmt->execute([$_SESSION['user_id'], $productId]);
    $isFavorite = $stmt->fetchColumn() !== false;
}

$pageTitle = htmlspecialchars($item['ItemName']);
$additionalCss = "/Css/product.css";
include 'template/header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['ItemName']); ?></title>
    <link rel="stylesheet" href="/Css/product.css">
</head>
<body>
<main class="content">
    <div class="container">
        <section class="product-page">
            <div class="product-details">
                <div class="product-info">
                    <div class="product-image-container">
                        <?php 
                        $imagePath = getProductImage($item['img_path'], $item['ItemName']);
                        $imageUrl = '../Media/' . htmlspecialchars($imagePath);
                        ?>
                        <img src="<?php echo $imageUrl; ?>" 
                             alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                             class="product-image"
                             onerror="this.onerror=null; this.src='../Media/default.jpg';">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="favorite-button <?php echo $isFavorite ? 'active' : ''; ?>"
                                    onclick="toggleFavorite(<?php echo $item['idItem']; ?>, this)"
                                    title="<?php echo $isFavorite ? 'Удалить из избранного' : 'Добавить в избранное'; ?>">
                                <?php echo $isFavorite ? '❤️' : '🤍'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-details-column">
                        <h1><?php echo htmlspecialchars($item['ItemName']); ?></h1>
                        
                        <div class="product-meta">
                            <div class="age-category"><?php echo htmlspecialchars($item['Limitation']); ?>+</div>
                            
                            <div class="game-type">
                                <span>Жанр: <?php echo htmlspecialchars($item['Genre']); ?></span>
                            </div>
                            
                            <div class="game-details">
                                <span>Количество игроков: <?php echo htmlspecialchars($item['Count']); ?></span>
                            </div>
                            
                            <div class="game-details">
                                <span>Время игры: <?php echo htmlspecialchars($item['GameTime']); ?></span>
                            </div>
                            
                            <div class="price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</div>
                            
                            <button class="add-to-cart" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                                Добавить в корзину
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="product-description">
                    <h2>Описание</h2>
                    <div class="description-content">
                        <div class="main-description">
                            <p><?php echo nl2br(htmlspecialchars($item['DescriptionItem'])); ?></p>
                        </div>
                        
                        <div class="additional-info">
                            <h3>Характеристики</h3>
                            <ul class="game-features">
                                <li><strong>Возрастное ограничение:</strong> <?php echo htmlspecialchars($item['Limitation']); ?>+</li>
                                <li><strong>Жанр:</strong> <?php echo htmlspecialchars($item['Genre']); ?></li>
                                <li><strong>Количество игроков:</strong> <?php echo htmlspecialchars($item['Count']); ?></li>
                                <li><strong>Время игры:</strong> <?php echo htmlspecialchars($item['GameTime']); ?></li>
                            </ul>
                            
                            <div class="gameplay-details">
                                <h3>Особенности игры</h3>
                                <ul>
                                    <li>Подходит для компании от <?php echo explode('-', $item['Count'])[0]; ?> человек</li>
                                    <li>Средняя продолжительность партии: <?php echo $item['GameTime']; ?></li>
                                    <li>Жанр: <?php echo htmlspecialchars($item['Genre']); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
function toggleFavorite(itemId, button) {
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        alert('Для добавления в избранное необходимо авторизоваться');
        return;
    }

    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'idItem=' + itemId
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            button.classList.toggle('active');
            button.innerHTML = button.classList.contains('active') ? '❤️' : '🤍';
            button.title = button.classList.contains('active') ? 'Удалить из избранного' : 'Добавить в избранное';
            
            // Показываем уведомление
            const notification = document.createElement('div');
            notification.className = 'notification ' + (data.isFavorite ? 'success' : 'info');
            notification.textContent = data.message;
            document.body.appendChild(notification);
            
            // Удаляем уведомление через 3 секунды
            setTimeout(() => {
                notification.remove();
            }, 3000);
        } else {
            alert(data.message || 'Произошла ошибка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при обновлении избранного');
    });
}

function addToCart(itemId, button) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'idItem=' + itemId
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            button.classList.add('added');
            button.textContent = 'В корзине';
            setTimeout(() => {
                button.classList.remove('added');
                button.textContent = 'Добавить в корзину';
            }, 2000);
        } else {
            alert(data.message || 'Произошла ошибка');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при добавлении в корзину');
    });
}
</script>

<?php include 'template/footer.php'; ?>
</body>
</html>