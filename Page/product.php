<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Продукт</title>
    <link rel="stylesheet" href="..\Css\product.css">
</head>
<body>
<div class="page-wrapper">
    <header class="header">
        <a href="../index.php">
            <img src="../Media/logo.png" alt="Логотип" class="logo" />
        </a>
        <input class="search-input" placeholder='Название товара' type='text' />
        <div class="button-container">
            <a href="personal.php" class="icon-button">
                <img alt='user' src="../Media/user-icon.png" />
            </a>
            <a href="fav.php" class="icon-button">
                <img alt='love' src="../Media/love-icon.png" />
            </a>
            <a href="busket.php" class="icon-button">
                <img alt='store' src="../Media/store-icon.png" />
            </a>
        </div>
    </header>
    
    <nav class="nav">
        <ul>
            <li><a href="#veterinary">Ветеринарные</a></li>
            <li><a href="#adults">Для взрослых</a></li>
            <li><a href="#family">Для всей семьи</a></li>
            <li><a href="#children">Для детей</a></li>
            <li><a href="#classic">Классические</a></li>
            <li><a href="#plans">Планы</a></li>
            <li><a href="#strategic">Стратегические</a></li>
            <li><a href="#sales">Хаты продаж</a></li>
        </ul>
    </nav>

    <main class="content">
        <section class="product-page">
            <div class="container">
                <h1>Бункер</h1>
                <div class="product-details">
                    <div class="product-info">
                        <img src="./images/product-images/bunker.png" alt="Бункер" class="product-image" />
                        <div class="product-details-column">
                            <div class="age-category">18+</div>
                            <div class="game-type">
                                <img src="./images/icons/book.png" alt="Вечеринка" class="icon" /> Вечериночные игры
                            </div>
                            <div class="game-details">
                                <img src="./images/icons/people.png" alt="Игроки" class="icon" /> Игроков: 4–16
                            </div>
                            <div class="game-details">
                                <img src="./images/icons/clock.png" alt="Время" class="icon" /> Время партии: 30–60 минут
                            </div>
                            <div class="price">Цена: 2 990₽</div>
                            <button class="add-to-cart">Добавить в корзину</button>
                        </div>
                    </div>
                </div>
                
                <div class="description">
                    <h2>Описание:</h2>
                    <p>Настольная игра "Бункер": увлекательная игра, позволяющая игрокам развивать коммуникативные навыки и командную работу. Каждая партия уникальна и зависит от ваших решений и взаимодействий с другими участниками.</p>
                </div>

                <div class="reviews">
                    <h2>Отзывы:</h2>
                    <div class="write-review">
                        <textarea placeholder="Напишите отзыв..."></textarea>
                        <button>Отправить отзыв</button>
                    </div>

                    <div class="review">
                        <div class="review-author-container">
                            <img src="./images/avatars/Gregory.jpg" alt="Григорий" class="review-avatar" />
                            <span class="review-author">Григорий Петротенко</span>
                        </div>
                        <div class="review-date">01 января 2025 г., 14:35</div>
                        <div class="star-rating">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star">☆</span>
                        </div>
                        <div class="review-text">Это потрясающая игра! Она позволяет раскрыть потенциал коммуникации и стратегического мышления каждого игрока.</div>
                    </div>

                    <div class="review">
                        <div class="review-author-container">
                            <img src="./images/avatars/Aline.jpg" alt="Алина" class="review-avatar" />
                            <span class="review-author">Алина Рафиловна</span>
                        </div>
                        <div class="review-date">01 января 2025 г., 14:35</div>
                        <div class="star-rating">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star">☆</span>
                            <span class="star">☆</span>
                        </div>
                        <div class="review-text">Игра интересна, но требует больше деталей и ясности в правилах.</div>
                    </div>

                    <div class="review">
                        <div class="review-author-container">
                            <img src="./images/avatars/Igor.jpg" alt="Игорь" class="review-avatar" />
                            <span class="review-author">Игорь Муслимов</span>
                        </div>
                        <div class="review-date">01 января 2025 г., 14:35</div>
                        <div class="star-rating">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                        </div>
                        <div class="review-text">Игра заставляет думать быстро и общаться эффективно, идеально подойдет для дружеских встреч!</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-logo">
            <img src="./images/logo.png" alt="логотип" />
        </div>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Страницы</h4>
                <ul>
                    <li><a href="#home">Главная</a></li>
                    <li><a href="#catalogue">Каталог</a></li>
                    <li><a href="#cart">Корзина</a></li>
                    <li><a href="#favorites">Избранное</a></li>
                    <li><a href="#profile">Профиль</a></li>
                    <li><a href="#delivery">Доставка</a></li>
                    <li><a href="#orders">Заказы</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Услуги</h4>
                <ul>
                    <li><a href="#delivery-service">Доставка</a></li>
                    <li><a href="#support">Служба поддержки</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Документы</h4>
                <ul>
                    <li><a href="#delivery-terms">Условия доставки</a></li>
                    <li><a href="#storage-conditions">Условия хранения</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-qr">
            <img src="./images/qr-code.png" alt="QR-код" />
        </div>
    </footer>
</div>
</body>
</html>