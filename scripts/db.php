<?php
$config = include('db_config.php');

try {
    $db = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'], 
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // при любой ошибке вызываем exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // говорим PDO по умолчанию возвращать данные из таблицы в виде ассоциативных массивов
            PDO::ATTR_EMULATE_PREPARES => false, // защита от SQL-инъекций отключает эмуляцию подготовленных запросов
        ]
    );
} catch (PDOException $e) {
    error_log("Ошибка подключения к БД: " . $e->getMessage());
    throw $e; 
}
