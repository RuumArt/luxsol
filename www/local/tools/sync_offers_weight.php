<?php

/**
 * Разовый скрипт: проставляет вес основного товара во все его торговые предложения
 *
 * Запуск из консоли:
 *   php local/tools/sync_offers_weight.php
 *
 * Запуск из браузера (только под администратором):
 *   /local/tools/sync_offers_weight.php
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

@set_time_limit(0);
ignore_user_abort(true);

$logger = static function (string $message): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

\Room\Events\CatalogHandlers::syncAllProducts($logger);

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
