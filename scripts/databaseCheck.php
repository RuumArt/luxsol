<?php
$dbName = getenv('DBName');

// создать соединение с MySQL
try {
    $link = mysqli_connect(getenv('DBHost'), getenv('DBLogin'), getenv('DBPassword'));
} catch (Throwable $exception) {
    die('Ошибка соединения с базой данных: ' . $exception->getMessage() . PHP_EOL);
}
if (!$link) {
    die('Ошибка соединения с базой данных: ' . mysqli_connect_error() . PHP_EOL);
}

// проверить наличие базы данных
try {
    $sql = sprintf('SHOW DATABASES LIKE \'%s\';', $dbName);
    if (!$rs = mysqli_query($link, $sql)) {
        die('Ошибка при проверке наличия базы данных: ' . mysqli_error($link) . PHP_EOL);
    }
    $result = $rs->fetch_row();
    if ($result === false) {
        die('Ошибка при проверке наличия базы данных: ' . mysqli_error($link) . PHP_EOL);
    }
    if ($result !== null) {
        // БД существует - дальше ничего делать не нужно
        echo sprintf('База данных существует: %s', $dbName) . PHP_EOL;
        die(0);
    }
} catch (Throwable $exception) {
    die('Ошибка при проверке наличия базы данных: ' . $exception->getMessage() . PHP_EOL);
}

// выполнить запрос на создание базы данных
try {
    $sql = sprintf('CREATE DATABASE IF NOT EXISTS %s;', $dbName);
    if (!mysqli_query($link, $sql)) {
        die('Ошибка при создании базы данных: ' . mysqli_error($link) . PHP_EOL);
    }
    echo sprintf('Создана база данных: %s', $dbName) . PHP_EOL;
} catch (Throwable $exception) {
    die('Ошибка при создании базы данных: ' . $exception->getMessage() . PHP_EOL);
}
