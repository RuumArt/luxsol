<?php

/**
 * Какие способы оплаты доступны при каждой доставке, и возврат оплаты картой
 *
 * 17.09 при заведении Toptrans вызывался DeliveryPaySystemTable::setLinks.
 * Он не только пишет связи одной службы, но и пересчитывает ограничения
 * у всех служб доставки и оплаты. После этого давно лежавшие в таблице
 * связи DPD и самовывоза начали действовать, и оплата картой - самый
 * популярный способ - осталась только у Packeta.
 *
 * Доступность проверяется не по таблице связей, а честно: собирается
 * заказ в памяти и прогоняется через ограничения платёжных систем, как
 * это делает оформление заказа. НИЧЕГО НЕ СОХРАНЯЕТ, кроме связей
 * при --apply.
 *
 * Запуск:
 *   php local/tools/pay_links.php          показать, что доступно
 *   php local/tools/pay_links.php --apply  вернуть карту DPD и самовывозу
 */

// Доставки, у которых оплата картой была до 17.09
const RESTORE = [
    18 => 'Kuriérska služba DPD',
    20 => 'Osobný odber v Bratislave',
];

// Оплата картой (GP WebPay)
const CARD_PAY_SYSTEM_ID = 7;

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

use Bitrix\Sale;
use Bitrix\Sale\Internals\DeliveryPaySystemTable;

$apply = in_array('--apply', $argv, true);

/**
 * Любой активный товар с ценой - нужен только для корзины в памяти
 */
$productId = (int)\Bitrix\Main\Application::getConnection()->queryScalar(
    "SELECT e.ID FROM b_iblock_element e
     JOIN b_catalog_product p ON p.ID = e.ID
     JOIN b_catalog_price pr ON pr.PRODUCT_ID = e.ID
     WHERE e.IBLOCK_ID = 1 AND e.ACTIVE = 'Y' AND p.TYPE = 1 AND pr.PRICE > 0"
);

/**
 * Способы оплаты, которые пройдут ограничения при данной доставке
 *
 * @param int $deliveryId
 * @param int $personTypeId
 * @return array ID => название
 */
$availableFor = static function (int $deliveryId, int $personTypeId) use ($productId): array {
    $basket = Sale\Basket::create(SITE_ID);
    $item = $basket->createItem('catalog', $productId);
    $item->setFields([
        'QUANTITY' => 1,
        'CURRENCY' => 'EUR',
        'LID' => SITE_ID,
        'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName(),
    ]);

    $order = Sale\Order::create(SITE_ID, null, 'EUR');
    $order->setPersonTypeId($personTypeId);
    $order->setBasket($basket);

    $service = Sale\Delivery\Services\Manager::getObjectById($deliveryId);

    if (!$service) {
        return [];
    }

    $shipment = $order->getShipmentCollection()->createItem($service);

    foreach ($basket as $basketItem) {
        $shipment->getShipmentItemCollection()->createItem($basketItem)->setQuantity($basketItem->getQuantity());
    }

    $payment = $order->getPaymentCollection()->createItem();
    $payment->setField('SUM', $order->getPrice());

    $result = [];

    foreach (Sale\PaySystem\Manager::getListWithRestrictions($payment) as $ps) {
        if (($ps['ACTIVE'] ?? 'Y') === 'Y' && (int)$ps['ID'] > 0 && ($ps['ACTION_FILE'] ?? '') !== 'inner') {
            $result[(int)$ps['ID']] = $ps['NAME'];
        }
    }

    ksort($result);

    return $result;
};

$deliveries = [];
$res = Sale\Delivery\Services\Table::getList([
    'filter' => ['=ACTIVE' => 'Y', '!=CLASS_NAME' => '\Bitrix\Sale\Delivery\Services\Group'],
    'select' => ['ID', 'NAME', 'PARENT_ID'],
    'order' => ['ID' => 'ASC'],
]);

while ($row = $res->fetch()) {
    if ((int)$row['ID'] === 1) {
        continue; // служебная "без доставки"
    }

    $deliveries[(int)$row['ID']] = $row['NAME'];
}

$show = static function (string $title) use ($deliveries, $availableFor): array {
    echo $title, PHP_EOL;

    $snapshot = [];

    foreach ($deliveries as $id => $name) {
        foreach ([1 => 'физлицо', 2 => 'юрлицо'] as $pt => $ptName) {
            $list = $availableFor($id, $pt);
            $snapshot[$id][$pt] = array_keys($list);

            printf("  #%-3d %-28s %-8s %s\n", $id, mb_substr($name, 0, 28), $ptName,
                $list ? implode(', ', array_map(static function ($k, $v) {
                    return $k . ' ' . $v;
                }, array_keys($list), $list)) : '— нет доступных оплат');
        }
    }

    return $snapshot;
};

$before = $show('Доступные способы оплаты сейчас:');

echo str_repeat('-', 70), PHP_EOL;

$plan = [];

foreach (RESTORE as $deliveryId => $name) {
    if (in_array(CARD_PAY_SYSTEM_ID, $before[$deliveryId][1] ?? [], true)) {
        echo '  #', $deliveryId, ' ', $name, ': карта уже доступна', PHP_EOL;
        continue;
    }

    $links = array_map('intval', DeliveryPaySystemTable::getLinks($deliveryId, DeliveryPaySystemTable::ENTITY_TYPE_DELIVERY));
    $plan[$deliveryId] = array_values(array_unique(array_merge($links, [CARD_PAY_SYSTEM_ID])));

    echo '  #', $deliveryId, ' ', $name, ': оплаты ', implode(',', $links), ' -> ', implode(',', $plan[$deliveryId]), PHP_EOL;
}

if (empty($plan)) {
    echo PHP_EOL, 'Менять нечего', PHP_EOL;
    return;
}

if (!$apply) {
    echo PHP_EOL, 'Ничего не записано. Для записи добавьте --apply', PHP_EOL;
    return;
}

foreach ($plan as $deliveryId => $ids) {
    $r = DeliveryPaySystemTable::setLinks($deliveryId, DeliveryPaySystemTable::ENTITY_TYPE_DELIVERY, $ids);
    echo '  запись #', $deliveryId, ': ', $r->isSuccess() ? 'ок' : implode('; ', $r->getErrorMessages()), PHP_EOL;
}

echo str_repeat('-', 70), PHP_EOL;

$after = $show('После изменения:');

// setLinks трогает ограничения всех служб - проверяем, что изменилось только задуманное
echo str_repeat('-', 70), PHP_EOL, 'Сверка с исходным состоянием:', PHP_EOL;

$unexpected = 0;

foreach ($after as $id => $byPt) {
    foreach ($byPt as $pt => $list) {
        $added = array_diff($list, $before[$id][$pt] ?? []);
        $removed = array_diff($before[$id][$pt] ?? [], $list);
        $expected = isset($plan[$id]) ? [CARD_PAY_SYSTEM_ID] : [];

        if (array_values($added) != array_values(array_intersect($added, $expected)) || !empty($removed)) {
            $unexpected++;
            echo '  НЕОЖИДАННО #', $id, ' тип ', $pt, ': добавлено [', implode(',', $added), '] убрано [', implode(',', $removed), ']', PHP_EOL;
        }
    }
}

echo $unexpected ? '  Есть неожиданные изменения, проверьте!' : '  изменилось только задуманное', PHP_EOL;
