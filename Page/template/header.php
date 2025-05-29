<?php
require_once __DIR__ . '/../db_connect.php';

// Get categories for navigation
try {
    $stmt = $conn->query("SELECT * FROM Category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении категорий: " . $e->getMessage();
}

// Get selected category
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Настольные игры'; ?></title>
    <link rel="stylesheet" href="/Css/style.css">
    <?php if (isset($additionalCss)): ?>
        <link rel="stylesheet" href="<?php echo $additionalCss; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="/index.php">
            <img src="/Media/logo.png" alt="Логотип" class="logo"/>
        </a>
        <div class="search-container">
            <div class="search-wrapper">
                <input class="search-input" placeholder="Поиск по названию, описанию или категории" type="text"/>
                <button type="submit" class="search-button">
                    <img src="/Media/search.png" alt="Поиск">
                </button>
                <div class="search-results"></div>
            </div>
        </div>
        <div class="button-container">
            <a href="/Page/vhod.php" class="icon-button">
                <img alt="Пользователь" src="/Media/icon1.png"/>
            </a>
            <a href="/Page/fav.php" class="icon-button">
                <img alt="Любимые" src="/Media/icon2.png"/>
            </a>
            <a href="/Page/busket.php" class="icon-button">
                <img alt="Корзина" src="/Media/icon3.png"/>
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
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <ul>
            <li><a href="/index.php" class="<?php echo !$selectedCategory ? 'active' : ''; ?>">Все</a></li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="/index.php?category=<?php echo $category['idCategory']; ?>" 
                       class="<?php echo $selectedCategory == $category['idCategory'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['nameCategory']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav> 