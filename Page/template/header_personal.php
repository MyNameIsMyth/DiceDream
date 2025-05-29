<?php
require_once __DIR__ . '/../db_connect.php';
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
                <a href="personal.php" class="icon-button" title="Личный кабинет">
                    <img alt="Профиль" src="../Media/icon1.png" />
                </a>
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
</body>
</html>