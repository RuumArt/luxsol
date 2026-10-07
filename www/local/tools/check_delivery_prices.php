<?php

/**
 * Проверка тарифов доставки на сценариях
 *
 * Собирает заказ в памяти с заданными весами, прогоняет через ограничения
 * и расчёт служб DPD, Packeta и Toptrans и сверяет с ожидаемым результатом.
 * НИЧЕГО НЕ СОХРАНЯЕТ: заказ не записывается, корзины на сайте не появляются.
 *
 * Запуск:
 *   php local/tools/check_delivery_prices.php
 */

// Службы доставки на сайте
const SERVICES = [
    18 => 'DPD',
    21 => 'Packeta',
    22 => 'Toptrans',
];

/**
 * Сценарии: позиции корзины и ожидаемая цена по службам.
 * Позиция: [вес за штуку в граммах, количество, строка размеров или '']
 * Ожидание: число - цена, null - служба недоступна
 */
const SCENARIOS = [
    'мелочь, 3 шт по 1 кг' => [
        'items' => [[1000, 3, '']],
        'expect' => [18 => 7.00, 21 => 2.83, 22 => null],
    ],
    'один товар 7 кг' => [
        'items' => [[7000, 1, '']],
        'expect' => [18 => 9.00, 21 => 4.67, 22 => null],
    ],
    'товар без веса' => [
        'items' => [[0, 1, '']],
        'expect' => [18 => 7.00, 21 => 2.83, 22 => null],
    ],
    'один товар 12 кг' => [
        'items' => [[12000, 1, '']],
        'expect' => [18 => 11.00, 21 => null, 22 => null],
    ],
    'заказ 2770: две сетки, 33,36 кг' => [
        'items' => [[33360, 1, '33 × 8,5 m × 1 ks., 32,5 × 8,5 m × 1 ks.']],
        'expect' => [18 => 22.00, 21 => null, 22 => null],
    ],
    'пример клиента 17 + 10 + 6 кг' => [
        'items' => [[17000, 1, ''], [10000, 1, ''], [6000, 1, '']],
        'expect' => [18 => 22.00, 21 => null, 22 => null],
    ],
    '11 + 10 кг, делить не нужно' => [
        'items' => [[11000, 1, ''], [10000, 1, '']],
        'expect' => [18 => 15.00, 21 => null, 22 => null],
    ],
    'кусок 35 кг' => [
        'items' => [[35000, 1, '']],
        'expect' => [18 => null, 21 => null, 22 => 50.00],
    ],
    'кусок 35 кг + 25 кг' => [
        'items' => [[35000, 1, ''], [25000, 1, '']],
        'expect' => [18 => null, 21 => null, 22 => 60.00],
    ],
];

if (PHP_SAPI !== 'cli') {
    die('Только из консоли' . PHP_EOL);
}

$documentRoot = realpath(__DIR__ . '/../../');

$_SERVER['DOCUMENT_ROOT'] = $documentRoot;
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require_once $documentRoot . '/bitrix/modules/main/include/prolog_before.php';

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

use Bitrix\Sale\Basket;
use Bitrix\Sale\Delivery\Restrictions\Manager as RestrictionManager;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Order;

$siteId = \CSite::GetDefSite();
$currency = 'EUR';

$personType = \Bitrix\Sale\Internals\PersonTypeTable::getRow([
    'select' => ['ID'],
    'filter' => ['=ACTIVE' => 'Y'],
    'order' => ['SORT' => 'ASC'],
]);

// Любой товар каталога: нужен только как ссылка для позиции корзины
$product = \Bitrix\Catalog\ProductTable::getRow([
    'select' => ['ID'],
    'filter' => ['=TYPE' => \Bitrix\Catalog\ProductTable::TYPE_PRODUCT],
]);

if (!$personType || !$product) {
    die('Не найден тип плательщика или товар каталога' . PHP_EOL);
}

printf("Сайт %s, тип плательщика %d, товар-заглушка #%d\n\n", $siteId, $personType['ID'], $product['ID']);

/**
 * Заказ в памяти с одной отгрузкой
 */
function room_build_shipment(array $items, string $siteId, string $currency, int $personTypeId, int $productId)
{
    $order = Order::create($siteId, null, $currency);
    $order->setPersonTypeId($personTypeId);

    $basket = Basket::create($siteId);

    foreach ($items as $index => [$weight, $quantity, $sizes]) {
        $item = $basket->createItem('catalog', $productId);

        // Без провайдера: позиция не пересчитывается из каталога,
        // и заданный вес остаётся таким, как в сценарии
        $item->setFields([
            'NAME' => 'Тестовая позиция ' . ($index + 1),
            'QUANTITY' => $quantity,
            'CURRENCY' => $currency,
            'PRICE' => 10,
            'BASE_PRICE' => 10,
            'CUSTOM_PRICE' => 'Y',
            'WEIGHT' => $weight,
            'LID' => $siteId,
            'PRODUCT_PROVIDER_CLASS' => '',
        ]);

        if ($sizes !== '') {
            $item->getPropertyCollection()->setProperty([[
                'NAME' => 'Rozmery',
                'CODE' => 'SIZES_STR',
                'VALUE' => $sizes,
                'SORT' => 100,
            ]]);
        }
    }

    $result = $order->setBasket($basket);

    if (!$result->isSuccess()) {
        throw new \Exception('Корзина: ' . implode('; ', $result->getErrorMessages()));
    }

    // На случай, если пересчёт всё же тронул вес
    $position = 0;

    foreach ($basket as $basketItem) {
        $basketItem->setField('WEIGHT', $items[$position][0]);
        $position++;
    }

    $shipment = $order->getShipmentCollection()->createItem();
    $shipmentItems = $shipment->getShipmentItemCollection();

    foreach ($basket as $basketItem) {
        $shipmentItem = $shipmentItems->createItem($basketItem);
        $shipmentItem->setQuantity($basketItem->getQuantity());
    }

    return $shipment;
}

$passed = 0;
$failed = 0;

foreach (SCENARIOS as $title => $scenario) {
    echo $title, PHP_EOL;

    try {
        $shipment = room_build_shipment(
            $scenario['items'],
            $siteId,
            $currency,
            (int)$personType['ID'],
            (int)$product['ID']
        );
    } catch (\Throwable $e) {
        echo '  ОШИБКА сборки заказа: ', $e->getMessage(), PHP_EOL, PHP_EOL;
        $failed++;
        continue;
    }

    foreach (SERVICES as $serviceId => $serviceName) {
        $expected = $scenario['expect'][$serviceId];
        $actual = null;
        $note = '';

        $service = DeliveryManager::getObjectById($serviceId);

        if (!$service) {
            $note = 'служба не найдена или выключена';
        } elseif (RestrictionManager::checkService($serviceId, $shipment) !== RestrictionManager::SEVERITY_NONE) {
            $note = 'скрыта ограничением';
        } else {
            $calc = $service->calculate($shipment);

            if ($calc->isSuccess()) {
                $actual = round((float)$calc->getPrice(), 2);
                $note = $calc->getPacksCount() > 1 ? $calc->getPacksCount() . ' посылки' : '';
            } else {
                $note = 'недоступна: ' . implode('; ', $calc->getErrorMessages());
            }
        }

        $ok = ($expected === null && $actual === null)
            || ($expected !== null && $actual !== null && abs($expected - $actual) < 0.005);

        $ok ? $passed++ : $failed++;

        printf(
            "  %-4s %-9s %-10s ожидали %-10s %s\n",
            $ok ? 'ОК' : 'СБОЙ',
            $serviceName,
            $actual === null ? '—' : number_format($actual, 2, ',', ' ') . ' €',
            $expected === null ? '—' : number_format($expected, 2, ',', ' ') . ' €',
            $note
        );
    }

    echo PHP_EOL;
}

echo str_repeat('-', 70), PHP_EOL;
printf("Совпало: %d, расхождений: %d\n", $passed, $failed);
