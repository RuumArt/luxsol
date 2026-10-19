<?php
/** CLI: сводка, --list, --xml, --clear. Без реальных заказов и изменений товаров. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Только из консоли');
}

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../');
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'luxsol.sk';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Room\Tools\MicrosoftMerchantFeed;

if (in_array('--clear', $argv, true)) {
    \Bitrix\Main\Data\Cache::createInstance()->cleanDir(MicrosoftMerchantFeed::CACHE_DIR);
    echo 'Кеш Microsoft-фида сброшен', PHP_EOL;
    return;
}

try {
    $feed = MicrosoftMerchantFeed::collect();
    $xml = MicrosoftMerchantFeed::render($feed['items']);
    if (in_array('--xml', $argv, true)) {
        echo $xml;
        return;
    }

    echo 'Microsoft Merchant: ', count($feed['items']), ' товаров', PHP_EOL;
    foreach (array_count_values(array_column($feed['items'], 'availability')) as $state => $count) {
        echo '  ', $state, ': ', $count, PHP_EOL;
    }
    echo 'Не выгружаются: ', count($feed['skipped']), PHP_EOL;
    foreach ($feed['skipped'] as [$id, $name, $reason]) {
        printf("  #%s %s — %s\n", $id, $name, $reason);
    }
    if (in_array('--list', $argv, true)) {
        foreach ($feed['items'] as $item) {
            printf("%-10s %-12s %-13s %s\n", $item['id'], $item['price'], $item['availability'], $item['title']);
        }
    }
} catch (\Throwable $error) {
    fwrite(STDERR, 'Ошибка проверки Microsoft-фида: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
