<?php

/**
 * Показывает, какие свойства позиций реально встречаются в заказах:
 * код, название и сколько раз использовано. Ничего не меняет.
 *
 * Нужен, чтобы не гадать с кодами при фильтрации свойств в шаблонах.
 *
 * Запуск из консоли:
 *   php local/tools/list_basket_prop_codes.php
 *
 * Из браузера (только под администратором):
 *   /local/tools/list_basket_prop_codes.php
 */

$isCli = (PHP_SAPI === 'cli');

$documentRoot = realpath(__DIR__ . '/../../');

if ($isCli) {
    $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
    $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('BX_NO_ACCELERATOR_RESET', true);
}

require_once $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

if (!$isCli) {
    global $USER;

    if (!$USER || !$USER->IsAdmin()) {
        http_response_code(403);
        die('Access denied');
    }

    header('Content-Type: text/plain; charset=UTF-8');
}

\Bitrix\Main\Loader::includeModule('sale');

$rows = \Bitrix\Sale\Internals\BasketPropertyTable::getList([
    'select' => [
        'CODE',
        'NAME',
        'CNT',
    ],
    'runtime' => [
        new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'),
    ],
    'group' => ['CODE', 'NAME'],
    'order' => ['CNT' => 'DESC'],
]);

printf("%-22s %-34s %s\n", 'CODE', 'NAME', 'использований');
echo str_repeat('-', 75), PHP_EOL;

while ($row = $rows->fetch()) {
    printf(
        "%-22s %-34s %d\n",
        (string)$row['CODE'],
        mb_strimwidth((string)$row['NAME'], 0, 34, '…'),
        (int)$row['CNT']
    );
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
