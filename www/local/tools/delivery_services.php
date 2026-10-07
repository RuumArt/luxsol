<?php

/**
 * Службы доставки: список и смена обработчика
 *
 * В админке обработчик выбирается только при создании службы, а Packeta
 * должна сохранить свой ID 21: на него завязано создание посылки
 * в init.php. Поэтому обработчик меняем здесь, прямо у существующей службы.
 *
 * По умолчанию НИЧЕГО НЕ МЕНЯЕТ, только показывает список служб.
 * Перед записью старая строка сохраняется в JSON рядом со скриптом.
 *
 * Запуск из консоли:
 *   php local/tools/delivery_services.php                               # список
 *   php local/tools/delivery_services.php --switch=21:packeta            # что будет
 *   php local/tools/delivery_services.php --switch=21:packeta --apply    # записать
 *   php local/tools/delivery_services.php --restore=backup.json --apply  # откат
 *
 *   php local/tools/delivery_services.php --restrictions=21              # ограничения службы
 *   php local/tools/delivery_services.php --hide-oversize=18 --apply     # скрыть DPD для кусков > 30 кг
 *
 *   php local/tools/delivery_services.php --create-toptrans=18           # что будет
 *   php local/tools/delivery_services.php --create-toptrans=18 --apply   # создать
 *
 * Обработчики: dpd, packeta, toptrans
 *
 * Toptrans создаётся по образцу службы DPD (число после знака равенства):
 * копируются ограничения по сайту, типу плательщика и местоположению и
 * привязки к способам оплаты. Ограничения по весу, размерам и цене не
 * копируются - у поддонов свои пределы. Добавляется ограничение
 * "только для неразложимого заказа"
 */

// Классы ограничений DPD, которые Toptrans наследовать не должен
const SKIP_RESTRICTIONS = [
    'ByWeight',
    'ByDimensions',
    'ByMaxSize',
    'ByPrice',
    'OversizeRestriction',
];

const HANDLERS = [
    'dpd' => '\Room\Delivery\DpdHandler',
    'packeta' => '\Room\Delivery\PacketaHandler',
    'toptrans' => '\Room\Delivery\ToptransHandler',
];

$isCli = (PHP_SAPI === 'cli');

if (!$isCli) {
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

use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table;
use Bitrix\Sale\Internals\DeliveryPaySystemTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\Services\Base\RestrictionManager;

// Разбор аргументов
$apply = false;
$switchId = 0;
$switchTo = '';
$restoreFile = '';
$templateId = 0;
$restrictionsOf = 0;
$hideOversizeFor = 0;

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--hide-oversize=') === 0) {
        $hideOversizeFor = (int)substr($arg, strlen('--hide-oversize='));
    } elseif (strpos($arg, '--restrictions=') === 0) {
        $restrictionsOf = (int)substr($arg, strlen('--restrictions='));
    } elseif (strpos($arg, '--create-toptrans=') === 0) {
        $templateId = (int)substr($arg, strlen('--create-toptrans='));
    } elseif ($arg === '--apply') {
        $apply = true;
    } elseif (strpos($arg, '--switch=') === 0) {
        [$id, $handler] = array_pad(explode(':', substr($arg, strlen('--switch=')), 2), 2, '');
        $switchId = (int)$id;
        $switchTo = strtolower(trim($handler));
    } elseif (strpos($arg, '--restore=') === 0) {
        $restoreFile = substr($arg, strlen('--restore='));
    }
}

// ------------------------------------------------------------------- список
$rows = [];

$rs = Table::getList([
    'select' => ['ID', 'PARENT_ID', 'NAME', 'CLASS_NAME', 'ACTIVE', 'SORT'],
    'order' => ['PARENT_ID' => 'ASC', 'SORT' => 'ASC', 'ID' => 'ASC'],
]);

while ($row = $rs->fetch()) {
    $rows[(int)$row['ID']] = $row;
}

echo 'Службы доставки', PHP_EOL;
echo str_repeat('-', 100), PHP_EOL;

foreach ($rows as $row) {
    printf(
        "  #%-4d %-3s родитель %-4s %-35s %s\n",
        $row['ID'],
        $row['ACTIVE'] === 'Y' ? 'вкл' : 'ВЫК',
        (int)$row['PARENT_ID'] ?: '-',
        mb_strimwidth((string)$row['NAME'], 0, 35, '…'),
        $row['CLASS_NAME']
    );
}

echo str_repeat('-', 100), PHP_EOL;

// ------------------------------------- скрыть курьера для неразложимых заказов
if ($hideOversizeFor > 0) {
    if (!isset($rows[$hideOversizeFor])) {
        die('Служба #' . $hideOversizeFor . ' не найдена' . PHP_EOL);
    }

    printf(
        "\nСлужба #%d \"%s\": ограничение \"только если все куски не тяжелее 30 кг\"\n",
        $hideOversizeFor,
        $rows[$hideOversizeFor]['NAME']
    );

    $exists = ServiceRestrictionTable::getRow([
        'select' => ['ID', 'PARAMS'],
        'filter' => [
            '=SERVICE_ID' => $hideOversizeFor,
            '=SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
            '=CLASS_NAME' => '\\Room\\Delivery\\OversizeRestriction',
        ],
    ]);

    if ($exists) {
        die('  у службы уже есть это ограничение (#' . $exists['ID'] . '), параметры: '
            . json_encode($exists['PARAMS'], JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    if (!$apply) {
        die('  Записи не было. Для добавления добавьте --apply' . PHP_EOL);
    }

    $res = ServiceRestrictionTable::add([
        'SERVICE_ID' => $hideOversizeFor,
        'SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
        'CLASS_NAME' => '\\Room\\Delivery\\OversizeRestriction',
        'PARAMS' => ['MODE' => 'NO_OVERSIZE'],
        'SORT' => 200,
    ]);

    echo '  ', $res->isSuccess() ? 'добавлено, #' . $res->getId() : 'ОШИБКА: ' . implode('; ', $res->getErrorMessages()), PHP_EOL;

    return;
}

// ------------------------------------------------- ограничения одной службы
if ($restrictionsOf > 0) {
    if (!isset($rows[$restrictionsOf])) {
        die('Служба #' . $restrictionsOf . ' не найдена' . PHP_EOL);
    }

    printf("\nОграничения службы #%d \"%s\"\n", $restrictionsOf, $rows[$restrictionsOf]['NAME']);

    $rsRestr = ServiceRestrictionTable::getList([
        'filter' => [
            '=SERVICE_ID' => $restrictionsOf,
            '=SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
        ],
        'order' => ['SORT' => 'ASC'],
    ]);

    $found = false;

    while ($restr = $rsRestr->fetch()) {
        $found = true;

        printf(
            "  #%-5d %-55s %s\n",
            $restr['ID'],
            $restr['CLASS_NAME'],
            json_encode($restr['PARAMS'], JSON_UNESCAPED_UNICODE)
        );
    }

    if (!$found) {
        echo '  ограничений нет', PHP_EOL;
    }

    $payLinks = DeliveryPaySystemTable::getLinks($restrictionsOf, DeliveryPaySystemTable::ENTITY_TYPE_DELIVERY);
    echo '  оплаты: ', $payLinks ? implode(', ', $payLinks) : 'все', PHP_EOL;

    return;
}

// ------------------------------------------------------- создание Toptrans
if ($templateId > 0) {
    $newClass = HANDLERS['toptrans'];

    if (!isset($rows[$templateId])) {
        die('Служба-образец #' . $templateId . ' не найдена' . PHP_EOL);
    }

    if (!class_exists($newClass)) {
        die('ОШИБКА: класс ' . $newClass . ' не найден - файлы Delivery залиты?' . PHP_EOL);
    }

    foreach ($rows as $row) {
        if (ltrim((string)$row['CLASS_NAME'], '\\') === ltrim($newClass, '\\')) {
            die('Служба Toptrans уже есть: #' . $row['ID'] . ' - второй раз не создаю' . PHP_EOL);
        }
    }

    $template = Table::getRowById($templateId);

    printf("\nОбразец: #%d \"%s\"\n", $template['ID'], $template['NAME']);

    // Ограничения образца
    $copy = [];
    $skipped = [];

    $rsRestr = ServiceRestrictionTable::getList([
        'filter' => [
            '=SERVICE_ID' => $templateId,
            '=SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
        ],
        'order' => ['SORT' => 'ASC'],
    ]);

    while ($restr = $rsRestr->fetch()) {
        $short = substr(strrchr('\\' . ltrim($restr['CLASS_NAME'], '\\'), '\\'), 1);

        // Регистр в сохранённых именах классов бывает разный
        if (in_array(strtolower($short), array_map('strtolower', SKIP_RESTRICTIONS), true)) {
            $skipped[] = $short;
            continue;
        }

        // Привязка к оплатам переносится отдельно, через таблицу связей
        if (strtolower($short) === 'bypaysystem') {
            continue;
        }

        $copy[] = $restr;
    }

    echo '  ограничения копируются: ',
        $copy ? implode(', ', array_map(static function ($r) {
            return substr(strrchr('\\' . ltrim($r['CLASS_NAME'], '\\'), '\\'), 1);
        }, $copy)) : 'нет', PHP_EOL;
    echo '  ограничения пропущены:  ', $skipped ? implode(', ', $skipped) : 'нет', PHP_EOL;

    // Способы оплаты, разрешённые у образца. Пусто - значит разрешены все
    $payLinks = DeliveryPaySystemTable::getLinks($templateId, DeliveryPaySystemTable::ENTITY_TYPE_DELIVERY);
    echo '  оплаты у доставки:      ', $payLinks ? implode(', ', $payLinks) : 'все', PHP_EOL;

    // Способы оплаты, которые сами ограничены списком доставок и знают образец
    $paySystemsToExtend = [];

    $rsPay = \Bitrix\Sale\PaySystem\Manager::getList(['select' => ['ID', 'NAME']]);

    while ($pay = $rsPay->fetch()) {
        $deliveries = DeliveryPaySystemTable::getLinks((int)$pay['ID'], DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM);

        if (!empty($deliveries) && in_array($templateId, array_map('intval', $deliveries), true)) {
            $paySystemsToExtend[(int)$pay['ID']] = ['name' => $pay['NAME'], 'deliveries' => $deliveries];
        }
    }

    echo '  оплаты со списком доставок, куда добавится Toptrans: ',
        $paySystemsToExtend
            ? implode(', ', array_map(static function ($id, $p) {
                return '#' . $id . ' ' . $p['name'];
            }, array_keys($paySystemsToExtend), $paySystemsToExtend))
            : 'нет',
        PHP_EOL;

    echo '  + ограничение "только для неразложимого заказа"', PHP_EOL;

    if (!$apply) {
        echo PHP_EOL, 'Записи не было. Для создания добавьте --apply', PHP_EOL;
        return;
    }

    $add = Manager::add([
        'NAME' => 'Toptrans',
        'DESCRIPTION' => 'Doprava na paletách pre zásielky, ktoré sa nedajú rozdeliť na balíky.',
        'ACTIVE' => 'Y',
        'PARENT_ID' => 0,
        'SORT' => (int)$template['SORT'] + 10,
        'CLASS_NAME' => $newClass,
        'CURRENCY' => $template['CURRENCY'],
        'VAT_ID' => $template['VAT_ID'],
        'ALLOW_EDIT_SHIPMENT' => 'Y',
        'CONFIG' => [],
        'XML_ID' => Manager::generateXmlId(),
    ]);

    if (!$add->isSuccess()) {
        die('ОШИБКА создания: ' . implode('; ', $add->getErrorMessages()) . PHP_EOL);
    }

    $newId = (int)$add->getId();
    echo PHP_EOL, 'Создана служба #', $newId, PHP_EOL;

    foreach ($copy as $restr) {
        $res = ServiceRestrictionTable::add([
            'SERVICE_ID' => $newId,
            'SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
            'CLASS_NAME' => $restr['CLASS_NAME'],
            'PARAMS' => $restr['PARAMS'],
            'SORT' => $restr['SORT'],
        ]);

        echo '  ограничение ', $restr['CLASS_NAME'], ': ',
            $res->isSuccess() ? 'ок' : implode('; ', $res->getErrorMessages()), PHP_EOL;
    }

    $res = ServiceRestrictionTable::add([
        'SERVICE_ID' => $newId,
        'SERVICE_TYPE' => RestrictionManager::SERVICE_TYPE_SHIPMENT,
        'CLASS_NAME' => '\\Room\\Delivery\\OversizeRestriction',
        'PARAMS' => ['MODE' => 'ONLY_OVERSIZE'],
        'SORT' => 200,
    ]);

    echo '  ограничение OversizeRestriction: ',
        $res->isSuccess() ? 'ок' : implode('; ', $res->getErrorMessages()), PHP_EOL;

    if (!empty($payLinks)) {
        $res = DeliveryPaySystemTable::setLinks($newId, DeliveryPaySystemTable::ENTITY_TYPE_DELIVERY, $payLinks);
        echo '  оплаты у доставки: ', $res->isSuccess() ? 'ок' : implode('; ', $res->getErrorMessages()), PHP_EOL;
    }

    foreach ($paySystemsToExtend as $payId => $pay) {
        $list = array_values(array_unique(array_merge(array_map('intval', $pay['deliveries']), [$newId])));
        $res = DeliveryPaySystemTable::setLinks($payId, DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM, $list);
        echo '  оплата #', $payId, ': ', $res->isSuccess() ? 'Toptrans добавлен' : implode('; ', $res->getErrorMessages()), PHP_EOL;
    }

    echo PHP_EOL, 'Готово. Проверьте службу в админке: Магазин -> Службы доставки -> #', $newId, PHP_EOL;
    echo 'Удалить при необходимости можно там же.', PHP_EOL;

    return;
}

// ------------------------------------------------------------------ откат
if ($restoreFile !== '') {
    $path = strpos($restoreFile, '/') === 0 ? $restoreFile : __DIR__ . '/' . $restoreFile;
    $backup = json_decode((string)@file_get_contents($path), true);

    if (empty($backup['ID']) || empty($backup['CLASS_NAME'])) {
        die('Не удалось прочитать резервную копию: ' . $path . PHP_EOL);
    }

    printf("Откат службы #%d на %s\n", $backup['ID'], $backup['CLASS_NAME']);

    if (!$apply) {
        die('Для записи добавьте --apply' . PHP_EOL);
    }

    $result = Manager::update((int)$backup['ID'], ['CLASS_NAME' => $backup['CLASS_NAME']]);

    echo $result->isSuccess()
        ? 'Готово' . PHP_EOL
        : 'ОШИБКА: ' . implode('; ', $result->getErrorMessages()) . PHP_EOL;

    return;
}

if ($switchId <= 0) {
    echo PHP_EOL, 'Смена обработчика: --switch=<ID>:<dpd|packeta|toptrans>', PHP_EOL;
    return;
}

// ----------------------------------------------------------- смена обработчика
if (!isset(HANDLERS[$switchTo])) {
    die('Неизвестный обработчик "' . $switchTo . '". Доступны: '
        . implode(', ', array_keys(HANDLERS)) . PHP_EOL);
}

if (!isset($rows[$switchId])) {
    die('Служба #' . $switchId . ' не найдена' . PHP_EOL);
}

$service = $rows[$switchId];
$newClass = HANDLERS[$switchTo];

echo PHP_EOL;
printf("Служба #%d \"%s\"\n", $service['ID'], $service['NAME']);
printf("  сейчас:  %s\n", $service['CLASS_NAME']);
printf("  станет:  %s\n", $newClass);

if (!class_exists($newClass)) {
    die('ОШИБКА: класс ' . $newClass . ' не найден - файлы Delivery залиты?' . PHP_EOL);
}

// Профиль внутри модуля перевозчика менять опасно: родитель ждёт своих детей
if ((int)$service['PARENT_ID'] > 0) {
    echo '  ВНИМАНИЕ: служба вложена в #' . $service['PARENT_ID']
        . ' - это профиль другого обработчика. Проверьте, что так и задумано.', PHP_EOL;
}

foreach ($rows as $row) {
    if ((int)$row['PARENT_ID'] === $switchId) {
        die('ОШИБКА: у службы есть вложенные профили, менять обработчик группы нельзя' . PHP_EOL);
    }
}

if (ltrim($service['CLASS_NAME'], '\\') === ltrim($newClass, '\\')) {
    echo '  уже стоит этот обработчик, менять нечего', PHP_EOL;
    return;
}

if (!$apply) {
    echo PHP_EOL, 'Записи не было. Для смены добавьте --apply', PHP_EOL;
    return;
}

// Резервная копия строки целиком, включая настройки
$full = Table::getRowById($switchId);
$backupFile = __DIR__ . '/delivery_' . $switchId . '_' . date('Ymd_His') . '.json';
file_put_contents($backupFile, json_encode($full, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$result = Manager::update($switchId, ['CLASS_NAME' => $newClass]);

if ($result->isSuccess()) {
    echo '  Готово. Резервная копия: ', basename($backupFile), PHP_EOL;
    echo '  Откат: php local/tools/delivery_services.php --restore=',
        basename($backupFile), ' --apply', PHP_EOL;
} else {
    echo '  ОШИБКА: ', implode('; ', $result->getErrorMessages()), PHP_EOL;
}
