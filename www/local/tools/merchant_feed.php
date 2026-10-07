<?php

/**
 * Проверка фида Google Merchant: что попадёт в фид и что нет
 *
 * Фид отдаётся по адресу https://luxsol.sk/feeds/google-merchant.php и
 * кешируется на час. Здесь он собирается заново, без кеша.
 *
 * Запуск:
 *   php local/tools/merchant_feed.php           сводка и товары, не попавшие в фид
 *   php local/tools/merchant_feed.php --list    все позиции: ID, цена, наличие, доставка
 *   php local/tools/merchant_feed.php --xml     сам XML
 *   php local/tools/merchant_feed.php --clear   сбросить кеш фида
 */

if (PHP_SAPI !== 'cli') {
    die('Только из консоли' . PHP_EOL);
}

$documentRoot = realpath(__DIR__ . '/../../');

$_SERVER['DOCUMENT_ROOT'] = $documentRoot;
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'luxsol.sk';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require_once $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

use Room\Tools\MerchantFeed;

if (in_array('--clear', $argv, true)) {
    \Bitrix\Main\Data\Cache::createInstance()->cleanDir(MerchantFeed::CACHE_DIR);
    echo 'Кеш фида сброшен', PHP_EOL;

    return;
}

$feed = MerchantFeed::collect();

if (in_array('--xml', $argv, true)) {
    echo MerchantFeed::render($feed['items']);

    return;
}

$items = $feed['items'];

echo 'В фиде товаров: ', count($items), PHP_EOL;

$byAvailability = array_count_values(array_column($items, 'availability'));
foreach ($byAvailability as $state => $count) {
    echo '  ', $state, ': ', $count, PHP_EOL;
}

$perM2 = count(array_filter($items, static function ($i) {
    return isset($i['unit_pricing_measure']);
}));
echo '  цена за m²: ', $perM2, PHP_EOL;

$cut = count(array_filter($items, static function ($i) {
    return isset($i['return_policy_label']);
}));
echo '  отрезной товар (возврат только при браке): ', $cut, PHP_EOL;

$noAds = count(array_filter($items, static function ($i) {
    return !empty($i['excluded_destination']);
}));
echo '  без платной рекламы (крепёж и шнуры): ', $noAds, PHP_EOL;

$services = array_count_values(array_map(static function ($i) {
    return $i['shipping']['service'] . ' ' . number_format($i['shipping']['price'], 2, ',', '') . ' €';
}, $items));
ksort($services);
echo 'Доставка:', PHP_EOL;
foreach ($services as $service => $count) {
    echo '  ', $service, ': ', $count, PHP_EOL;
}

$handling = array_count_values(array_map(static function ($i) {
    return 'отгрузка ' . $i['handling'][0] . '-' . $i['handling'][1] . ' + в пути '
        . MerchantFeed::TRANSIT_DAYS[0] . '-' . MerchantFeed::TRANSIT_DAYS[1] . ' раб. дн.';
}, $items));
echo 'Сроки:', PHP_EOL;
foreach ($handling as $text => $count) {
    echo '  ', $text, ': ', $count, PHP_EOL;
}

$ids = array_column($items, 'id');
$dupes = array_keys(array_filter(array_count_values($ids), static function ($c) {
    return $c > 1;
}));
if ($dupes) {
    echo 'ОШИБКА: повторяющиеся ID: ', implode(', ', $dupes), PHP_EOL;
}

$byProductId = count(array_filter($items, static function ($i) {
    return $i['id'] === (string)$i['site_id'];
}));
echo 'ID по артикулу: ', count($items) - $byProductId, ', по ID товара (артикула нет или он повторяется): ', $byProductId, PHP_EOL;

if ($feed['skipped']) {
    echo PHP_EOL, 'Не попали в фид: ', count($feed['skipped']), PHP_EOL;
    foreach ($feed['skipped'] as [$id, $name, $reason]) {
        printf("  #%-6d %s - %s%s", $id, $name, $reason, PHP_EOL);
    }
}

if (in_array('--list', $argv, true)) {
    echo PHP_EOL;
    foreach ($items as $i) {
        printf(
            "%-10s #%-6d %-12s %-13s %s %s%s",
            $i['id'],
            $i['site_id'],
            $i['price'],
            $i['availability'],
            isset($i['unit_pricing_measure']) ? 'm²' : '  ',
            $i['title'],
            PHP_EOL
        );
    }
}
