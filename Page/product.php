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

// Получаем категории
try {
    $stmt = $conn->query("SELECT * FROM Category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении категорий: " . $e->getMessage();
}

// Получаем выбранную категорию из GET параметра
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

$pageTitle = htmlspecialchars($item['ItemName']);
$additionalCss = "/Css/product.css";
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['ItemName']); ?> - Настольные игры</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link rel="stylesheet" href="../Css/catalog.css">
    <link rel="stylesheet" href="../Css/product.css">
    <link rel="stylesheet" href="../Css/footer.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo-link">
                <img src="../Media/logo.png" alt="Логотип" class="logo"/>
            </a>
            <form action="catalog.php" method="GET" class="search-form">
                <div class="search-wrapper">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Поиск настольных игр..." />
                    <button type="submit" class="search-button">
                        <img src="../Media/search.png" alt="Поиск">
                    </button>
                </div>
            </form>
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="personal.php" class="icon-button" title="Личный кабинет">
                        <img alt="Профиль" src="../Media/icon1.png" />
                    </a>
                <?php else: ?>
                    <a href="vhod.php" class="icon-button" title="Войти">
                        <img alt="Профиль" src="../Media/icon1.png" />
                    </a>
                <?php endif; ?>
                <a href="fav.php" class="icon-button" title="Избранное">
                    <img alt="Избранное" src="../Media/icon2.png" />
                </a>
                <a href="busket.php" class="icon-button" title="Корзина">
                    <img alt="Корзина" src="../Media/icon3.png" />
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $userId = $_SESSION['user_id'];
                        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM Cart WHERE idUser = ?");
                        $stmt->execute([$userId]);
                        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        if ($total > 0):
                        ?>
                            <span class="cart-count"><?php echo $total; ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <ul>
            <li><a href="../index.php" class="<?php echo !$selectedCategory ? 'active' : ''; ?>">Все</a></li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="../index.php?category=<?php echo $category['idCategory']; ?>" 
                       class="<?php echo $selectedCategory == $category['idCategory'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['nameCategory']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <main class="content">
        <div class="container">
            <section class="product-page">
                <div class="product-details">
                    <div class="product-info">
                        <!-- Изображение товара -->
                        <div class="product-image-container">
                            <?php 
                            $imagePath = getProductImage($item['img_path'], $item['ItemName']);
                            $imageUrl = '../Media/' . htmlspecialchars($imagePath);
                            ?>
                            <img src="<?php echo $imageUrl; ?>" 
                                 alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                                 class="product-image"
                                 onerror="this.onerror=null; this.src='../Media/default.jpg';">
                        </div>

                        <!-- Информация о товаре -->
                        <div class="product-details-column">
                            <h1><?php echo htmlspecialchars($item['ItemName']); ?></h1>

                            <!-- Характеристики -->
                            <div class="product-characteristics">
                                <h3>Характеристики</h3>
                                <div class="product-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Возраст:</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($item['Limitation']); ?>+</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Жанр:</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Количество игроков:</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($item['Count']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Время игры:</span>
                                        <span class="meta-value"><?php echo htmlspecialchars($item['GameTime']); ?></span>
                                    </div>
                                </div>

                                <!-- Особенности игры -->
                                <div class="game-features">
                                    <h3>Особенности игры</h3>
                                    <ul>
                                        <li>Подходит для компании от <?php echo explode('-', $item['Count'])[0]; ?> человек</li>
                                        <li>Средняя продолжительность партии: <?php echo $item['GameTime']; ?></li>
                                        <li>Жанр: <?php echo htmlspecialchars($item['Genre']); ?></li>
                                        <?php if (!empty($item['Limitation'])): ?>
                                            <li>Рекомендуемый возраст: <?php echo htmlspecialchars($item['Limitation']); ?>+</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Цена и кнопки -->
                            <div class="price-actions">
                                <div class="price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</div>
                                <div class="buttons-container">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button class="add-to-cart" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                                            Добавить в корзину
                                        </button>
                                        <button type="button" 
                                                class="favorite-button <?php echo $isFavorite ? 'active' : ''; ?>"
                                                onclick="toggleFavorite(<?php echo $item['idItem']; ?>, this)"
                                                title="<?php echo $isFavorite ? 'Удалить из избранного' : 'Добавить в избранное'; ?>">
                                            <?php echo $isFavorite ? '❤️' : '🤍'; ?>
                                        </button>
                                    <?php else: ?>
                                        <a href="vhod.php" class="add-to-cart">Войти для покупки</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Описание -->
                    <div class="product-description">
                        <h3>Описание</h3>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($item['DescriptionItem'])); ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include 'template/footer.php'; ?>

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
</body>
</html>