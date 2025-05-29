<?php
session_start();
require_once 'db_connect.php';

// Функция для получения случайного изображения из папки Media
function getProductImage($itemName) {
    $imagePath = "../Media/" . $itemName . ".png";
    
    // Список доступных изображений
    $availableImages = [
        'Бункер.png',
        'Глумхевен.png',
        'Дюна.png',
        'Зомбоцид.png',
        'Книга.png',
        'Котята.png',
        'Мемы.png',
        'Осворн.png',
        'Пёсики.png',
        'Свинтус.png',
        'Уно.png',
        'Эверделл.png',
        'Аркхэм.png',
        '500 карт.png'
    ];

    // Проверяем существует ли файл
    if (file_exists($imagePath)) {
        return $itemName . ".png";
    } else {
        // Если файл не существует, выбираем случайное изображение
        return $availableImages[array_rand($availableImages)];
    }
}

// Параметры сортировки и фильтрации
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Базовый SQL запрос
$sql = "SELECT i.*, c.nameCategory, 
        (SELECT COUNT(*) FROM Cart WHERE idItem = i.idItem) as popularity
        FROM Item i 
        LEFT JOIN Category c ON i.idCategory = c.idCategory";

// Добавляем условия фильтрации
$params = [];
$where = [];

if ($category > 0) {
    $where[] = "i.idCategory = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where[] = "(i.ItemName LIKE ? OR i.DescriptionItem LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Добавляем сортировку
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY i.Price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY i.Price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY i.ItemName ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY i.ItemName DESC";
        break;
    case 'popularity':
        $sql .= " ORDER BY popularity DESC";
        break;
    default:
        $sql .= " ORDER BY i.idItem DESC";
}

try {
    // Получаем список товаров
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем список категорий
    $stmt = $conn->prepare("SELECT * FROM Category ORDER BY nameCategory");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Если пользователь авторизован, получаем его избранные товары
    $favorites = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT idItem FROM Favorites WHERE idUser = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch(PDOException $e) {
    $error = "Ошибка при получении данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог настольных игр - DiceDream</title>
    <link rel="stylesheet" href="../Css/catalog.css">
    <style>
        .catalog-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Стили для сайдбара */
        .catalog-sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar-section {
            margin-bottom: 25px;
        }

        .sidebar-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }

        .sidebar-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            color: #495057;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            background: #f8f9fa;
            color: #007bff;
        }

        .sidebar-link.active {
            background: #e7f1ff;
            color: #007bff;
            font-weight: 500;
        }

        /* Стили для сетки товаров */
        .new-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 0;
        }

        @media (min-width: 768px) {
            .new-product-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        .new-product-card {
            position: relative;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            width: 100%;
        }

        .new-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .product-image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: #f8f9fa;
        }

        .product-image-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .favorite-button {
            position: absolute;
            top: 8px;
            right: 8px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
            transition: all 0.2s ease;
            padding: 0;
        }

        .favorite-button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .favorite-button img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            display: block;
            margin: auto;
        }

        .new-product-content {
            padding: 12px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-category {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 8px 0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin: 8px 0;
        }

        .new-basket-button {
            width: 100%;
            padding: 8px;
            background: #007bff;
            color: white;
            border: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: auto;
        }

        .new-basket-button:hover {
            background: #0056b3;
        }

        .new-basket-button img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* Стили для контролов каталога */
        .catalog-controls {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .control-label {
            font-weight: 500;
            color: #495057;
        }

        .control-select {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: white;
            color: #495057;
            font-size: 14px;
            cursor: pointer;
            min-width: 200px;
        }

        .control-select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        /* Стили для пустого результата */
        .empty-result {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .empty-result h2 {
            color: #2c3e50;
            margin-bottom: 16px;
        }

        .empty-result p {
            color: #6c757d;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
<div class="App">
    <header class="header">
        <div class="header-content">
            <a href="../index.php" class="logo-link">
                <img src="../Media/logo.png" alt="DiceDream" class="logo"/>
            </a>
            <form class="search-form" method="GET">
                <div class="search-wrapper">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Поиск настольных игр..." 
                           value="<?php echo htmlspecialchars($search); ?>" />
                    <button type="submit" class="search-button">
                        <img src="../Media/search-icon.png" alt="Поиск" class="search-icon">
                    </button>
                </div>
            </form>
            <div class="header-actions">
                <a href="personal.php" class="icon-button" title="Личный кабинет">
                    <img alt="Профиль" src="../Media/icon1.png" />
                </a>
                <a href="fav.php" class="icon-button" title="Избранное">
                    <img alt="Избранное" src="../Media/icon2.png" />
                </a>
                <a href="busket.php" class="icon-button" title="Корзина">
                    <img alt="Корзина" src="../Media/icon3.png" />
                </a>
            </div>
        </div>
    </header>

    <div class="catalog-container">
        <aside class="catalog-sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-title">Категории</h3>
                <ul class="sidebar-list">
                    <li class="sidebar-item">
                        <a href="?category=0" class="sidebar-link <?php echo !$category ? 'active' : ''; ?>">
                            Все игры
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li class="sidebar-item">
                            <a href="?category=<?php echo $cat['idCategory']; ?>" 
                               class="sidebar-link <?php echo $category == $cat['idCategory'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['nameCategory']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">Сортировка</h3>
                <ul class="sidebar-list">
                    <li class="sidebar-item">
                        <a href="?sort=popularity" class="sidebar-link <?php echo $sort === 'popularity' ? 'active' : ''; ?>">
                            По популярности
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?sort=price_asc" class="sidebar-link <?php echo $sort === 'price_asc' ? 'active' : ''; ?>">
                            Сначала дешевле
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?sort=price_desc" class="sidebar-link <?php echo $sort === 'price_desc' ? 'active' : ''; ?>">
                            Сначала дороже
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="?sort=name_asc" class="sidebar-link <?php echo $sort === 'name_asc' ? 'active' : ''; ?>">
                            По названию (А-Я)
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="catalog-main">
            <?php if (!empty($search)): ?>
                <div class="search-results">
                    <h2>Результаты поиска: "<?php echo htmlspecialchars($search); ?>"</h2>
                    <?php if (empty($items)): ?>
                        <p>По вашему запросу ничего не найдено</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="product-grid">
                <?php if (empty($items)): ?>
                    <div class="empty-result">
                        <img src="../Media/empty-results.png" alt="Ничего не найдено" class="empty-icon">
                        <h2>Товары не найдены</h2>
                        <p>Попробуйте изменить параметры поиска или фильтрации</p>
                        <a href="?category=0" class="reset-filters">Сбросить все фильтры</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="../Media/<?php echo getProductImage($item['ItemName']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                                     class="product-image">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="favorite-button <?php echo in_array($item['idItem'], $favorites) ? 'active' : ''; ?>"
                                            onclick="toggleFavorite(<?php echo $item['idItem']; ?>, this)">
                                        <?php echo in_array($item['idItem'], $favorites) ? '❤️' : '🤍'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                                <div class="product-meta">
                                    <span class="product-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                    <span class="product-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                                </div>
                                <div class="product-details">
                                    <span class="product-time"><?php echo htmlspecialchars($item['GameTime']); ?> мин</span>
                                    <span class="product-age"><?php echo htmlspecialchars($item['Limitation']); ?>+</span>
                                </div>
                                <div class="product-price">
                                    <span class="price"><?php echo number_format($item['Price'], 0, ',', ' '); ?> ₽</span>
                                </div>
                            </div>
                            <button class="add-to-cart-button" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                                В корзину
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../Media/logo.png" alt="DiceDream" />
            </div>
            <div class="footer-sections">
                <div class="footer-section">
                    <h4>Страницы</h4>
                    <ul>
                        <li><a href="../index.php">Главная</a></li>
                        <li><a href="catalog.php">Каталог</a></li>
                        <li><a href="busket.php">Корзина</a></li>
                        <li><a href="fav.php">Избранное</a></li>
                        <li><a href="personal.php">Профиль</a></li>
                        <li><a href="#">Доставка</a></li>
                        <li><a href="#">Заказы</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Услуги</h4>
                    <ul>
                        <li><a href="#">Доставка</a></li>
                        <li><a href="#">Служба поддержки</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Документация</h4>
                    <ul>
                        <li><a href="#">Условия доставки</a></li>
                        <li><a href="#">Условия хранения</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-qr">
                <div class="qr-code">
                    <img src="../Media/qr-code.png" alt="QR-код" />
                </div>
            </div>
        </div>
    </footer>
</div>
<script>
    function toggleCategory(categoryId) {
        var categoryElement = document.getElementById(categoryId);
        if (categoryElement.style.display === "none") {
            categoryElement.style.display = "block";
        } else {
            categoryElement.style.display = "none";
        }
    }

    function updateParams(param, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(param, value);
        window.location.href = url.toString();
    }

    function toggleFavorite(itemId, button) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = 'vhod.php';
            return;
        <?php endif; ?>

        fetch('toggle_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'idItem=' + itemId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.isFavorite) {
                    button.textContent = '❤️';
                    button.classList.add('active');
                } else {
                    button.textContent = '🤍';
                    button.classList.remove('active');
                }
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при обновлении избранного');
        });
    }

    function addToCart(itemId, button) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = 'vhod.php';
            return;
        <?php endif; ?>

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'idItem=' + itemId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.classList.add('added');
                button.textContent = 'В корзине';
                
                // Обновляем счетчик в корзине
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    let count = parseInt(cartCount.textContent || '0');
                    cartCount.textContent = count + 1;
                    cartCount.style.display = 'block';
                } else {
                    const cartIcon = document.querySelector('a[href="busket.php"]');
                    const newCount = document.createElement('span');
                    newCount.className = 'cart-count';
                    newCount.textContent = '1';
                    cartIcon.appendChild(newCount);
                }

                setTimeout(() => {
                    button.classList.remove('added');
                    button.textContent = 'В корзину';
                }, 2000);
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при добавлении в корзину');
        });
    }
</script>
</body>
</html>
