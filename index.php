<?php
session_start();
require_once 'Page/db_connect.php';

// Функция для получения корректного пути к изображению
function getProductImage($imgPath, $itemName) {
    if (!empty($imgPath)) {
        // Получаем только имя файла из полного пути
        $fileName = basename($imgPath);
        // Проверяем существование файла в локальной директории Media
        if (file_exists('Media/' . $fileName)) {
            return $fileName;
        }
    }
    
    // Если путь пустой или файл не существует, пробуем найти по имени товара
    $defaultFileName = mb_strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    if (file_exists('Media/' . $defaultFileName)) {
        return $defaultFileName;
    }
    
    // Если ничего не найдено, возвращаем изображение по умолчанию
    return 'default.jpg';
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

// Модифицируем запрос для получения товаров с учетом категории
$whereClause = $selectedCategory ? " WHERE c.idCategory = :category" : "";

// Получаем последние добавленные товары для секции "Новинки"
try {
    $stmt = $conn->prepare("
        SELECT i.*, c.nameCategory, c.idCategory as categoryId, i.img_path
        FROM Item i 
        INNER JOIN Category c ON i.idCategory = c.idCategory" 
        . $whereClause . 
        " ORDER BY i.idItem DESC LIMIT 6"
    );
    if ($selectedCategory) {
        $stmt->bindParam(':category', $selectedCategory);
    }
    $stmt->execute();
    $newItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении новинок: " . $e->getMessage();
}

// Топ продаж
$stmt = $conn->prepare("
    SELECT i.*, c.nameCategory, c.idCategory as categoryId, i.img_path
    FROM Item i 
    INNER JOIN Category c ON i.idCategory = c.idCategory"
    . $whereClause . 
    " LIMIT 6"
);
if ($selectedCategory) {
    $stmt->bindParam(':category', $selectedCategory);
}
$stmt->execute();
$topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Случайные товары для секции "Специально для вас"
$stmt = $conn->prepare("
    SELECT i.*, c.nameCategory, c.idCategory as categoryId, i.img_path
    FROM Item i 
    INNER JOIN Category c ON i.idCategory = c.idCategory"
    . $whereClause . 
    " ORDER BY RAND() LIMIT 6"
);
if ($selectedCategory) {
    $stmt->bindParam(':category', $selectedCategory);
}
$stmt->execute();
$specialItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - Настольные игры</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/catalog.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo-link">
                <img src="Media/logo.png" alt="Логотип" class="logo"/>
            </a>
            <form action="Page/catalog.php" method="GET" class="search-form">
                <div class="search-wrapper">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Поиск настольных игр..." />
                    <button type="submit" class="search-button">
                        <img src="Media/search-icon.png" alt="Поиск" class="search-icon">
                    </button>
                </div>
            </form>
            <div class="header-actions">
                <a href="Page/personal.php" class="icon-button" title="Личный кабинет">
                    <img alt="Профиль" src="Media/icon1.png" />
                </a>
                <a href="Page/fav.php" class="icon-button" title="Избранное">
                    <img alt="Избранное" src="Media/icon2.png" />
                </a>
                <a href="Page/busket.php" class="icon-button" title="Корзина">
                    <img alt="Корзина" src="Media/icon3.png" />
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

    <!-- Slider -->
    <div class="slider-container">
        <div class="slider">
            <div class="slider-track">
                <div class="slide">
                    <img src="Media/slide1.png" alt="Слайд 1" class="slider-image"/>
                </div>
                <div class="slide">
                    <img src="Media/slide2.jpg" alt="Слайд 2" class="slider-image"/>
                </div>
                <div class="slide">
                    <img src="Media/slide3.jpg" alt="Слайд 3" class="slider-image"/>
                </div>
            </div>
            <button class="slider-button prev-button">&#10094;</button>
            <button class="slider-button next-button">&#10095;</button>
            <div class="slider-dots"></div>
        </div>
    </div>

    <style>
        .slider-container {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .slider {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .slider-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide {
            flex: 0 0 100%;
            width: 100%;
        }

        .slider-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }

        .slider-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.7);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            z-index: 2;
        }

        .slider-button:hover {
            background: rgba(255, 255, 255, 0.9);
        }

        .prev-button {
            left: 20px;
        }

        .next-button {
            right: 20px;
        }

        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 2;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dot.active {
            background: white;
        }
    </style>

    <div class="container">
        <!-- Секция Новинки -->
        <section class="section">
            <div class="section-header">
                <h2>Новинки</h2>
                <a href="./Page/catalog.php" class="more-link">Ещё <img src="../Media/arrow.png" alt="Стрелка" class="arrow-icon"/></a>
            </div>
            <div class="products-grid">
                <?php foreach ($newItems as $item): ?>
                    <div class="product-card">
                        <a href="Page/product.php?id=<?php echo $item['idItem']; ?>" class="product-link">
                            <div class="product-image-container">
                                <?php 
                                $imagePath = getProductImage($item['img_path'], $item['ItemName']);
                                $imageUrl = htmlspecialchars('Media/' . $imagePath);
                                ?>
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                                     class="product-image"
                                     onerror="this.onerror=null; this.src='Media/default.jpg';">
                                <?php if (isset($item['Limitation'])): ?>
                                    <div class="age-limit"><?php echo htmlspecialchars($item['Limitation']); ?>+</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                                <div class="product-meta">
                                    <?php if (isset($item['Genre'])): ?>
                                        <span class="product-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($item['Count'])): ?>
                                        <span class="product-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                                    <?php endif; ?>
                                </div>
                                <p class="product-price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</p>
                            </div>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="add-to-cart-button" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                                В корзину
                            </button>
                        <?php else: ?>
                            <a href="Page/vhod.php" class="add-to-cart-button">Войти для покупки</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Секция Топ продаж -->
        <section class="section">
            <div class="section-header">
                <h2>Топ продаж</h2>
                <a href="Page/catalog.php" class="more-link">Ещё <img src="Media/arrow.png" alt="Стрелка" class="arrow-icon"/></a>
            </div>
            <div class="products-grid">
                <?php foreach ($topItems as $item): ?>
                    <div class="product-card">
                        <a href="Page/product.php?id=<?php echo $item['idItem']; ?>" class="product-link">
                            <div class="product-image-container">
                                <img src="Media/<?php echo getProductImage($item['img_path'], $item['ItemName']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                                     class="product-image">
                                <div class="age-limit"><?php echo htmlspecialchars($item['Limitation']); ?>+</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                                <div class="product-meta">
                                    <span class="product-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                    <span class="product-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                                </div>
                                <p class="product-price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</p>
                            </div>
                        </a>
                        <button class="add-to-cart-button" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                            В корзину
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Секция Специально для вас -->
        <section class="section">
            <div class="section-header">
                <h2>Специально для вас</h2>
                <a href="Page/catalog.php" class="more-link">Ещё <img src="Media/arrow.png" alt="Стрелка" class="arrow-icon"/></a>
            </div>
            <div class="products-grid">
                <?php foreach ($specialItems as $item): ?>
                    <div class="product-card">
                        <a href="Page/product.php?id=<?php echo $item['idItem']; ?>" class="product-link">
                            <div class="product-image-container">
                                <img src="Media/<?php echo getProductImage($item['img_path'], $item['ItemName']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['ItemName']); ?>" 
                                     class="product-image">
                                <div class="age-limit"><?php echo htmlspecialchars($item['Limitation']); ?>+</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo htmlspecialchars($item['ItemName']); ?></h3>
                                <div class="product-meta">
                                    <span class="product-genre"><?php echo htmlspecialchars($item['Genre']); ?></span>
                                    <span class="product-players"><?php echo htmlspecialchars($item['Count']); ?> игроков</span>
                                </div>
                                <p class="product-price"><?php echo number_format($item['Price'], 0, '', ' '); ?>₽</p>
                            </div>
                        </a>
                        <button class="add-to-cart-button" onclick="addToCart(<?php echo $item['idItem']; ?>, this)">
                            В корзину
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- About section -->
        <section class="about-section">
            <h1>О нас</h1>
            <hr/>
            <div class="about-content">
                <img src="Media/about.jpg" alt="О нас" class="about-image"/>
                <p>
                Добро пожаловать в наш интернет-магазин настольных игр! Мы — команда увлеченных энтузиастов, которые верят в объединяющую силу настольных игр. В нашем ассортименте вы найдете разнообразные игры для всех возрастов — от классических, знакомых многим, до современных хитов, завоевавших популярность по всему миру. Мы тщательно отбираем каждую игру, чтобы предложить вам только самые увлекательные варианты.
                </p>
            </div>
        </section>

        <!-- Преимущества магазина -->
        <div class="container-carder">
            <div class="card-item">
                <img src="Media/sistema.png" alt="Накопительная скидка"/>
                <p>Система накопительной скидки</p>
            </div>
            <div class="card-item">
                <img src="Media/big-assortment.png" alt="Ассортимент"/>
                <p>Большой ассортимент настольных игр</p>
            </div>
            <div class="card-item">
                <img src="Media/good-quality.png" alt="Качество"/>
                <p>Хорошее качество товара</p>
            </div>
            <div class="card-item">
                <img src="Media/bonus-system.png" alt="Бонусы"/>
                <p>Система бонусов</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <a href="index.php">
                    <img src="Media/logo.png" alt="DiceDream" />
                </a>
            </div>
            <div class="footer-sections">
                <div class="footer-section">
                    <h4>Навигация</h4>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="Page/catalog.php">Каталог</a></li>
                        <li><a href="Page/busket.php">Корзина</a></li>
                        <li><a href="Page/fav.php">Избранное</a></li>
                        <li><a href="Page/personal.php">Личный кабинет</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Покупателям</h4>
                    <ul>
                        <li><a href="#">Доставка и оплата</a></li>
                        <li><a href="#">Возврат товара</a></li>
                        <li><a href="#">Бонусная программа</a></li>
                        <li><a href="#">Подарочные сертификаты</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Информация</h4>
                    <ul>
                        <li><a href="#">О компании</a></li>
                        <li><a href="#">Контакты</a></li>
                        <li><a href="#">Условия использования</a></li>
                        <li><a href="#">Политика конфиденциальности</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-qr">
                <div class="qr-code">
                    <img src="Media/qr-code.png" alt="QR-код для скачивания приложения" />
                    <p class="qr-text">Скачайте наше приложение</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.search-input');
        const searchResults = document.querySelector('.search-results');
        let searchTimeout;

        // Функция для экранирования HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Обработка ввода в поле поиска
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('active');
                return;
            }

            // Показываем индикатор загрузки
            searchResults.innerHTML = '<div class="no-results">Поиск...</div>';
            searchResults.classList.add('active');

            // Задержка перед отправкой запроса для оптимизации
            searchTimeout = setTimeout(() => {
                fetch(`Page/search.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Search response:', data); // Для отладки
                        
                        if (data.success) {
                            if (data.results && data.results.length > 0) {
                                searchResults.innerHTML = data.results.map(item => `
                                    <div class="search-item" onclick="window.location.href='product.php?id=${escapeHtml(item.idItem)}'">
                                        ${item.img ? 
                                            `<img src="data:image/jpeg;base64,${item.img}" 
                                                  alt="${escapeHtml(item.ItemName)}" 
                                                  class="search-item-image">` : 
                                            '<div class="search-item-image-placeholder"></div>'}
                                        <div class="search-item-details">
                                            <div class="search-item-name">${escapeHtml(item.ItemName)}</div>
                                            ${item.nameCategory ? 
                                                `<div class="search-item-category">${escapeHtml(item.nameCategory)}</div>` : 
                                                ''}
                                            <div class="search-item-price">${new Intl.NumberFormat('ru-RU').format(item.Price)}₽</div>
                                        </div>
                                    </div>
                                `).join('');
                            } else {
                                searchResults.innerHTML = '<div class="no-results">Ничего не найдено</div>';
                            }
                        } else {
                            console.error('Search error:', data.message); // Для отладки
                            searchResults.innerHTML = `<div class="no-results">Произошла ошибка при поиске${data.message ? ': ' + escapeHtml(data.message) : ''}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error); // Для отладки
                        searchResults.innerHTML = '<div class="no-results">Произошла ошибка при поиске</div>';
                    });
            }, 300);
        });

        // Закрытие результатов поиска при клике вне
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });

        // Открытие результатов поиска при фокусе, если есть текст
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchResults.classList.add('active');
            }
        });
    });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.querySelector('.slider');
            const track = slider.querySelector('.slider-track');
            const slides = slider.querySelectorAll('.slide');
            const prevButton = slider.querySelector('.prev-button');
            const nextButton = slider.querySelector('.next-button');
            const dotsContainer = slider.querySelector('.slider-dots');
            
            let currentSlide = 0;
            const slideCount = slides.length;

            // Create dots
            slides.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (index === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
            });

            const dots = dotsContainer.querySelectorAll('.dot');

            function updateDots() {
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentSlide);
                });
            }

            function goToSlide(index) {
                currentSlide = index;
                track.style.transform = `translateX(-${currentSlide * 100}%)`;
                updateDots();
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % slideCount;
                goToSlide(currentSlide);
            }

            function prevSlide() {
                currentSlide = (currentSlide - 1 + slideCount) % slideCount;
                goToSlide(currentSlide);
            }

            // Event listeners
            prevButton.addEventListener('click', prevSlide);
            nextButton.addEventListener('click', nextSlide);

            // Auto-advance slides
            let slideInterval = setInterval(nextSlide, 5000);

            // Pause auto-advance on hover
            slider.addEventListener('mouseenter', () => {
                clearInterval(slideInterval);
            });

            slider.addEventListener('mouseleave', () => {
                slideInterval = setInterval(nextSlide, 5000);
            });

            // Touch support
            let touchStartX = 0;
            let touchEndX = 0;

            slider.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
            });

            slider.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                if (touchStartX - touchEndX > 50) {
                    nextSlide();
                } else if (touchEndX - touchStartX > 50) {
                    prevSlide();
                }
            });
        });
    </script>

    <script>
    function addToCart(itemId, button) {
        // Disable button and show loading state
        button.disabled = true;
        button.textContent = 'Добавление...';

        fetch('Page/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'idItem=' + itemId,
            credentials: 'same-origin'
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
                
                // Update cart counter
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    let count = parseInt(cartCount.textContent || '0');
                    cartCount.textContent = count + 1;
                    cartCount.style.display = 'block';
                } else {
                    const cartIcon = document.querySelector('a[href="Page/busket.php"]');
                    const newCount = document.createElement('span');
                    newCount.className = 'cart-count';
                    newCount.textContent = '1';
                    cartIcon.appendChild(newCount);
                }

                // Reset button state after delay
                setTimeout(() => {
                    button.classList.remove('added');
                    button.disabled = false;
                    button.textContent = 'В корзину';
                }, 2000);
            } else {
                button.classList.add('error');
                button.textContent = data.message || 'Ошибка';
                
                // Reset button state after delay
                setTimeout(() => {
                    button.classList.remove('error');
                    button.disabled = false;
                    button.textContent = 'В корзину';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.classList.add('error');
            button.textContent = 'Ошибка';
            
            // Reset button state after delay
            setTimeout(() => {
                button.classList.remove('error');
                button.disabled = false;
                button.textContent = 'В корзину';
            }, 2000);
        });
    }
    </script>
</body>
</html>