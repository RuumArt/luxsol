<?php

/**
 * Диагностика вызова DPD по конкретному заказу
 *
 * Показывает, что именно ушло бы в DPD: получателя, индекс, вес, посылки,
 * готовый JSON запроса и результат встроенной проверки.
 * По умолчанию НИЧЕГО НЕ ОТПРАВЛЯЕТ - только показывает.
 * С флагом --send работает как повтор вызова: тот же код, что и при
 * переводе заказа в статус "Готово к отправке".
 *
 * Запуск из консоли:
 *   php local/tools/check_dpd_order.php --order=2772
 *   php local/tools/check_dpd_order.php --order=2772 --send    # реально создать отправление
 *
 * Из браузера (только под администратором):
 *   /local/tools/check_dpd_order.php?order=2772
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
\Bitrix\Main\Loader::includeModule('catalog');

use Bitrix\Sale\Order;
use Room\Helpers\PropertyHelper;
use Room\Services\DpdService;
use Room\Tools\Order as OrderTools;

// Разбор аргументов
$orderNumber = '';
$send = false;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if (strpos($arg, '--order=') === 0) {
            $orderNumber = substr($arg, strlen('--order='));
        } elseif ($arg === '--send') {
            $send = true;
        }
    }
} else {
    $orderNumber = (string)($_GET['order'] ?? '');
    $send = !empty($_GET['send']);
}

if ($orderNumber === '') {
    die('Укажите --order=2772' . PHP_EOL);
}

$out = static function (string $message = ''): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

// Ищем заказ по ID, затем по номеру счёта
$order = Order::load((int)$orderNumber);

if (!$order) {
    $row = \Bitrix\Sale\Internals\OrderTable::getRow([
        'select' => ['ID'],
        'filter' => ['=ACCOUNT_NUMBER' => $orderNumber],
    ]);

    if ($row) {
        $order = Order::load((int)$row['ID']);
    }
}

if (!$order) {
    die('Заказ не найден: ' . $orderNumber . PHP_EOL);
}

$props = $order->getPropertyCollection();
$basket = $order->getBasket();

$out(str_repeat('=', 72));
$out('Заказ #' . $order->getId() . '  (номер ' . $order->getField('ACCOUNT_NUMBER') . ')');
$out(str_repeat('=', 72));
$out('  статус:  ' . $order->getField('STATUS_ID') . '   (DPD вызывается на RE)');
$out('  доставка: ' . implode(', ', $order->getDeliveryIdList())
    . (OrderTools::isDpdOrder($order)
        ? '   (DPD)'
        : '   НЕ DPD - при смене статуса в DPD не отправится'));
$out('  сумма:   ' . $order->getPrice() . ' ' . $order->getCurrency());
$out('  доставка: ' . $order->getDeliveryPrice());
$out('');

// ---------------------------------------------------------------- получатель
$fields = [
    'FIO' => 'Имя',
    'LAST_NAME' => 'Фамилия',
    'Adresa' => 'Адрес',
    'Mesto' => 'Город',
    'PSС' => 'Индекс (PSC)',
    'PHONE' => 'Телефон',
    'EMAIL' => 'E-mail',
    'FIO_2' => 'Имя (доставка)',
    'LAST_NAME_2' => 'Фамилия (доставка)',
    'Adresa_2' => 'Адрес (доставка)',
    'Mesto_2' => 'Город (доставка)',
    'PSС_2' => 'Индекс (доставка)',
    'PHONE_2' => 'Телефон (доставка)',
];

$out('Свойства заказа');
$out(str_repeat('-', 72));

foreach ($fields as $code => $title) {
    $value = PropertyHelper::getPropertyByCodeClear($props, $code);
    $out(sprintf('  %-22s %s', $title . ':', $value === '' ? '(пусто)' : $value));
}

$out('');

// ------------------------------------------------------------------- индексы
$zipMain = PropertyHelper::getPropertyByCodeClear($props, 'PSС');
$zipAlt = PropertyHelper::getPropertyByCodeClear($props, 'PSС_2');
$zip = $zipAlt !== '' ? $zipAlt : $zipMain;

$zipDigits = DpdService::normalizeZip($zip);

$out('Проверка индекса');
$out(str_repeat('-', 72));
$out('  как записан:     "' . $zip . '"');
$out('  только цифры:    "' . $zipDigits . '" (' . mb_strlen($zipDigits) . ' знаков)');

if ($zip === '') {
    $out('  ВЕРДИКТ: индекс пустой - DPD откажет');
} elseif (!DpdService::isValidZip($zip)) {
    $out('  ВЕРДИКТ: словацкий PSC это 5 цифр - заказ будет отклонён проверкой');
} elseif ($zip !== $zipDigits) {
    $out('  ВЕРДИКТ: лишние символы (пробел и т.п.) есть, но в DPD уйдёт "'
        . $zipDigits . '" - нормализация работает');
} else {
    $out('  ВЕРДИКТ: формат в порядке');
}

$out('');

// ----------------------------------------------------------------------- вес
$out('Вес');
$out(str_repeat('-', 72));

foreach ($basket as $item) {
    $itemWeight = (float)$item->getWeight();
    $quantity = (float)$item->getQuantity();
    $itemProps = $item->getPropertyCollection()->getPropertyValues();
    $sizes = $itemProps['SIZES_STR']['VALUE'] ?? '';

    $out(sprintf(
        '  #%-7s %-38s вес %8.0f г x %s шт = %8.0f г',
        $item->getProductId(),
        mb_strimwidth($item->getField('NAME'), 0, 38, '…'),
        $itemWeight,
        $quantity,
        $itemWeight * $quantity
    ));

    if ($sizes !== '') {
        $out('           размеры: ' . $sizes);

        // Площадь по строке размеров: высота x ширина x количество кусков
        $area = 0;

        foreach (\Room\Tools\Format::parseSizes($sizes) as $size) {
            $area += $size['height'] * $size['width'] * $size['count'];
        }

        if ($area > 0) {
            $out(sprintf(
                '           площадь %.1f m2, то есть %.0f г на m2',
                $area,
                $itemWeight / $area
            ));
        }
    }

    if ($itemWeight <= 0) {
        $out('           ВНИМАНИЕ: у товара не задан вес в каталоге');
    }
}

$out('');
$out('  вес корзины по Битриксу: ' . $basket->getWeight() . ' г = '
    . round($basket->getWeight() / 1000, 2) . ' кг');

if ((float)$basket->getWeight() <= 0) {
    $out('  ВНИМАНИЕ: вес нулевой - DpdService подставит 1 кг по умолчанию');
}

// Раскладка по мешкам - та же, по которой считается цена доставки
$pieces = \Room\Delivery\Packer::piecesFromBasket($basket);

$out('');
$out('  кусков в заказе: ' . count($pieces)
    . ' (' . implode(', ', array_map(static function ($piece) {
        return number_format($piece, 2, ',', ' ') . ' кг';
    }, $pieces)) . ')');

$packed = \Room\Delivery\Packer::pack(
    $pieces,
    DpdService::maxParcelWeight(),
    ['\Room\Delivery\DpdHandler', 'priceForBag']
);

if ($packed === null) {
    $out('  РАЗЛОЖИТЬ НЕЛЬЗЯ: есть кусок тяжелее '
        . DpdService::maxParcelWeight() . ' кг - только Toptrans');
} else {
    $out('  мешков при лимите ' . DpdService::maxParcelWeight() . ' кг: '
        . count($packed['bags']));

    foreach ($packed['bags'] as $i => $bag) {
        $out(sprintf(
            '    мешок %d: %s = %s кг -> %s EUR',
            $i + 1,
            implode(' + ', array_map(static function ($piece) {
                return number_format($piece, 2, ',', ' ');
            }, $bag)),
            number_format(array_sum($bag), 2, ',', ' '),
            number_format(\Room\Delivery\DpdHandler::priceForBag(array_sum($bag)), 2, ',', ' ')
        ));
    }

    $out('  доставка DPD по тарифу: '
        . number_format($packed['price'], 2, ',', ' ') . ' EUR');
}

$out('');

// -------------------------------------------------------------- что уйдёт в DPD
// Ключи не дублируем: берём тот же сервис, что и боевой вызов
$dpd = OrderTools::getDpdService();

$payload = $dpd->prepareShipmentData($order);
$validationErrors = $dpd->getValidationErrors($payload);

$out('Запрос в DPD');
$out(str_repeat('-', 72));
$out(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
$out('');
if (empty($validationErrors)) {
    $out('  проверка перед отправкой: ПРОШЛА');
} else {
    $out('  проверка перед отправкой: НЕ ПРОШЛА');

    foreach ($validationErrors as $error) {
        $out('    - ' . $error);
    }
}

$out('');

// ------------------------------------------------------------------ отправка
if (!$send) {
    $out('Отправка не выполнялась. Чтобы реально создать отправление: --send');
    require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
    return;
}

if (!OrderTools::isDpdOrder($order)) {
    $out('ВНИМАНИЕ: у заказа не курьерская доставка DPD, отправляем вручную по вашей команде.');
}

$out('Отправляем в DPD тем же путём, что и смена статуса на RE...');

$result = OrderTools::createDpd($order);

if ($result['success']) {
    $out('  успех. Посылки: ' . (implode(', ', $result['parcels']) ?: 'номера распознать не удалось'));
    $out('');
    $out('  сырой ответ DPD:');
    $out(json_encode($result['result'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
} else {
    $out('  ОШИБКА: ' . $result['error']);
}

$out('');
$out('Итог записан в журнал событий и в комментарий к заказу.');

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
