<?php
require_once 'db_connect.php';

try {
    // Выводим текущее состояние
    echo "Проверка текущего состояния категорий и товаров:\n";
    
    // Проверяем все товары и их категории
    $stmt = $conn->query("
        SELECT i.idItem, i.ItemName, i.idCategory, c.nameCategory 
        FROM Item i 
        LEFT JOIN Category c ON i.idCategory = c.idCategory
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Текущие связи:\n";
    foreach ($items as $item) {
        echo "{$item['ItemName']} - Категория: " . 
             ($item['nameCategory'] ? $item['nameCategory'] : 'Нет категории') . "\n";
    }

    // Определяем правильные категории для товаров
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

    // Обновляем категории
    foreach ($categoryUpdates as $itemName => $categoryName) {
        // Сначала получаем ID категории
        $stmt = $conn->prepare("SELECT idCategory FROM Category WHERE nameCategory = ?");
        $stmt->execute([$categoryName]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            // Обновляем товар
            $stmt = $conn->prepare("UPDATE Item SET idCategory = ? WHERE ItemName = ?");
            $stmt->execute([$category['idCategory'], $itemName]);
            echo "Обновлен товар {$itemName} - установлена категория {$categoryName}\n";
        } else {
            echo "Ошибка: категория {$categoryName} не найдена для товара {$itemName}\n";
        }
    }

    // Проверяем результат обновления
    echo "\nПроверка после обновления:\n";
    $stmt = $conn->query("
        SELECT i.idItem, i.ItemName, i.idCategory, c.nameCategory 
        FROM Item i 
        LEFT JOIN Category c ON i.idCategory = c.idCategory
    ");
    $updatedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($updatedItems as $item) {
        echo "{$item['ItemName']} - Категория: " . 
             ($item['nameCategory'] ? $item['nameCategory'] : 'Нет категории') . "\n";
    }

    echo "\nОбновление завершено успешно!";
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 