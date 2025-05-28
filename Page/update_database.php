<?php
require_once 'db_connect.php';

try {
    // 1. Добавляем поле idCategory в таблицу Item
    $conn->exec("ALTER TABLE Item ADD COLUMN idCategory INT");
    $conn->exec("ALTER TABLE Item ADD FOREIGN KEY (idCategory) REFERENCES Category(idCategory)");
    
    // 2. Добавляем категории
    $categories = [
        'Вечериночные',
        'Для взрослых',
        'Для всей семьи',
        'Для детей',
        'Классические',
        'Планы',
        'Стратегические',
        'Хиты продаж',
        'Живые карточные игры',
        'Хардкорные',
        'Ролевые',
        'Семейные'
    ];

    // Добавляем категории
    $stmt = $conn->prepare("INSERT INTO Category (nameCategory) VALUES (?)");
    foreach ($categories as $category) {
        $stmt->execute([$category]);
    }

    // 3. Обновляем категории для существующих товаров
    $categoryUpdates = [
        'Бункер' => 'Вечериночные',
        'Gloomhaven Мрачная Гавань' => 'Стратегические',
        '500 злобных карт' => 'Вечериночные',
        'Ужас Аркхэма. Карточная игра' => 'Живые карточные игры',
        'Descent: Сказания тьмы' => 'Хардкорные',
        'Дюна: Война за Арракис' => 'Хардкорные',
        'Зомбоцид. Вторая редакция' => 'Хардкорные',
        'Чёрная книга' => 'Стратегические',
        'Взрывные котята' => 'Вечериночные',
        'MemeClub' => 'Вечериночные',
        'Oathsworn: Верные клятве' => 'Хардкорные',
        'Подземелья и пёсики' => 'Ролевые',
        'Свинтус' => 'Вечериночные',
        'Уно' => 'Вечериночные',
        'Эверделл' => 'Семейные'
    ];

    // Обновляем idCategory для каждого товара
    foreach ($categoryUpdates as $itemName => $categoryName) {
        $stmt = $conn->prepare("
            UPDATE Item i 
            JOIN Category c ON c.nameCategory = ? 
            SET i.idCategory = c.idCategory 
            WHERE i.ItemName = ?
        ");
        $stmt->execute([$categoryName, $itemName]);
    }

    echo "База данных успешно обновлена";
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 