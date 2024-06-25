<?php

try {
    require_once __DIR__ . '/vendor/autoload.php';

    // Парсим .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // Подключаем декларации
    require_once __DIR__ . '/core/index.php';

    // Запускаем приложение
    require_once 'public/index.php';
} catch (\Throwable $t) {
    echo 'Error: ' . $t->getMessage();
    echo '<br />';
}
