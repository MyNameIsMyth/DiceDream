<?php
// Функция для получения изображения из папки Media
function getProductImage($itemName, $img_path = null) {
    // Массив возможных путей к изображениям
    $possiblePaths = [
        "../Media/" . str_replace(['"', "'", ' '], '', $itemName) . ".png", // Относительный путь
        "C:/Users/Und_ofg/Media/" . str_replace(['"', "'", ' '], '', $itemName) . ".png", // Абсолютный путь 1
        "C:/Users/Und_ofg/Desktop/Media/" . str_replace(['"', "'", ' '], '', $itemName) . ".png", // Абсолютный путь 2
    ];

    // Если передан путь из базы данных, добавляем его в начало массива
    if ($img_path) {
        array_unshift($possiblePaths, $img_path);
    }

    // Проверяем каждый возможный путь
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return "../Media/" . basename($path); // Всегда возвращаем относительный путь
        }
    }

    // Если ничего не найдено, возвращаем путь к логотипу
    return "../Media/logo.png";
}
?> 