<?php
session_start();
require_once 'db_connect.php';

$pageTitle = 'Покупки';
include 'template/header.php';
?>

<style>
    .new-product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
    .new-product-card {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    .new-product-content h3 {
        margin-bottom: 5px;
    }
    .new-basket-button {
        cursor: pointer;
    }
</style>

<h1 style="padding: 10px; margin-left: 12px;">Покупки</h1>
<main class="new-product-grid">
    <div class="new-product-card">
        <img src="https://avatars.mds.yandex.net/i?id=872dc79fb43f6d5d72c2024dff7bf222-5910939-images-thumbs&n=13" alt="Товар 1" />
        <div class="new-product-content">
            <h3>Дюна: Война за Арракис</h3>
            <p>Цена: 14 990P</p>
        </div>
        <button class="new-basket-button">
            <img src="../Media/icon3.png" alt="Корзина" />
        </button>
    </div>
    <div class="new-product-card">
        <img src="https://avatars.mds.yandex.net/i?id=872dc79fb43f6d5d72c2024dff7bf222-5910939-images-thumbs&n=13" alt="Товар 2" />
        <div class="new-product-content">
            <h3>Ужас Аркхама. Карточная игра</h3>
            <p>Цена: 2 990P</p>
        </div>
        <button class="new-basket-button">
            <img src="../Media/icon3.png" alt="Корзина" />
        </button>
    </div>
    <!-- Далее остальные карточки товаров -->
</main>

<?php include 'template/footer.php'; ?>