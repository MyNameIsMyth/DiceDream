<?php
require_once 'db_connect.php';

try {
    // Проверяем существующие категории
    $stmt = $conn->query("SELECT * FROM Category");
    $existingCategories = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // Получаем только названия категорий

    // Список всех необходимых категорий
    $requiredCategories = [
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

    echo "Существующие категории:\n";
    print_r($existingCategories);

    // Добавляем отсутствующие категории
    foreach ($requiredCategories as $category) {
        if (!in_array($category, $existingCategories)) {
            $stmt = $conn->prepare("INSERT INTO Category (nameCategory) VALUES (?)");
            $stmt->execute([$category]);
            echo "Добавлена новая категория: {$category}\n";
        }
    }

    // Обновляем связи товаров с категориями
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

    foreach ($categoryUpdates as $itemName => $categoryName) {
        $stmt = $conn->prepare("
            UPDATE Item i 
            JOIN Category c ON c.nameCategory = ? 
            SET i.idCategory = c.idCategory 
            WHERE i.ItemName = ?
        ");
        $stmt->execute([$categoryName, $itemName]);
        echo "Обновлена категория для товара: {$itemName} -> {$categoryName}\n";
    }

    // Проверяем финальное состояние
    echo "\nФинальное состояние базы данных:\n";
    $stmt = $conn->query("
        SELECT i.ItemName, c.nameCategory 
        FROM Item i 
        LEFT JOIN Category c ON i.idCategory = c.idCategory 
        ORDER BY c.nameCategory, i.ItemName
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        echo "{$item['ItemName']} - {$item['nameCategory']}\n";
    }

    echo "\nОбновление завершено успешно!";
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 