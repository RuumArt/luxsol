<?php

/**
 * Поиск товаров и торговых предложений без веса
 *
 * Пустой вес в каталоге означает, что заказ уходит в DPD с весом 1 кг
 * по умолчанию: и габариты, и тариф считаются неверно.
 *
 * Ничего не меняет, только показывает.
 *
 * Инфоблок торговых предложений добавляется сам: вес хранится именно
 * у предложений, у родителя его быть не должно.
 *
 * По умолчанию печатаются только те позиции, где вес действительно нужен.
 * Родители торговых предложений скрыты, их видно с флагом --all.
 *
 * Запуск из консоли:
 *   php local/tools/check_product_weights.php
 *   php local/tools/check_product_weights.php --active     # только активные
 *   php local/tools/check_product_weights.php --all        # вместе с родителями ТП
 *   php local/tools/check_product_weights.php --iblock=1,5
 *
 * Из браузера (только под администратором):
 *   /local/tools/check_product_weights.php
 */

// Инфоблок каталога. Инфоблок предложений подставляется автоматически
const DEFAULT_IBLOCK_IDS = [1];

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

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

use Bitrix\Catalog\ProductTable;

// Разбор аргументов
$iblockIds = DEFAULT_IBLOCK_IDS;
$onlyActive = false;
$showAll = false;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--active') {
            $onlyActive = true;
        } elseif ($arg === '--all') {
            $showAll = true;
        } elseif (strpos($arg, '--iblock=') === 0) {
            $iblockIds = explode(',', substr($arg, strlen('--iblock=')));
        }
    }
} else {
    $onlyActive = !empty($_GET['active']);
    $showAll = !empty($_GET['all']);

    if (!empty($_GET['iblock'])) {
        $iblockIds = explode(',', $_GET['iblock']);
    }
}

$iblockIds = array_values(array_filter(array_map('intval', $iblockIds)));

if (empty($iblockIds)) {
    die('Не указан инфоблок' . PHP_EOL);
}

// Инфоблоки торговых предложений добавляем сами: без них проверка
// бессмысленна, потому что вес хранится у предложений
$offerIblockIds = [];

foreach ($iblockIds as $iblockId) {
    $info = \CCatalogSKU::GetInfoByProductIBlock($iblockId);

    if (!empty($info['IBLOCK_ID'])) {
        $offerIblockIds[] = (int)$info['IBLOCK_ID'];
    }
}

$offerIblockIds = array_values(array_diff(array_unique($offerIblockIds), $iblockIds));
$iblockIds = array_merge($iblockIds, $offerIblockIds);

$out = static function (string $message = ''): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

$out('Инфоблоки: ' . implode(', ', $iblockIds)
    . (!empty($offerIblockIds)
        ? ' (торговые предложения: ' . implode(', ', $offerIblockIds) . ')'
        : ' (инфоблок предложений не найден)'));
$out($onlyActive ? 'Только активные товары' : 'Все товары, включая неактивные');
$out($showAll
    ? 'Показываю всё, включая родителей ТП'
    : 'Показываю только те, где вес нужен (--all - вместе с родителями ТП)');
$out(str_repeat('-', 78));

$filter = [
    '@IBLOCK_ELEMENT.IBLOCK_ID' => $iblockIds,
    [
        'LOGIC' => 'OR',
        ['=WEIGHT' => null],
        ['=WEIGHT' => 0],
    ],
];

if ($onlyActive) {
    $filter['=IBLOCK_ELEMENT.ACTIVE'] = 'Y';
}

$rs = ProductTable::getList([
    'select' => [
        'ID',
        'WEIGHT',
        'TYPE',
        'NAME' => 'IBLOCK_ELEMENT.NAME',
        'ACTIVE' => 'IBLOCK_ELEMENT.ACTIVE',
        'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
    ],
    'filter' => $filter,
    'order' => ['ID' => 'ASC'],
]);

// Типы товаров: у родителя товара с предложениями вес не нужен,
// его берут сами предложения
$typeNames = [
    ProductTable::TYPE_PRODUCT => 'товар',
    ProductTable::TYPE_SET => 'комплект',
    ProductTable::TYPE_SKU => 'родитель ТП',
    ProductTable::TYPE_OFFER => 'торговое предложение',
    ProductTable::TYPE_FREE_OFFER => 'предложение без товара',
    ProductTable::TYPE_EMPTY_SKU => 'родитель без ТП',
    ProductTable::TYPE_SERVICE => 'услуга',
];

$total = 0;
$parents = 0;
$offers = 0;
$simple = 0;
$orphans = 0;

while ($row = $rs->fetch()) {
    $total++;

    $type = (int)$row['TYPE'];

    // Родителю торговых предложений вес не нужен: он есть у предложений.
    // Исключение - родитель, у которого предложений нет вовсе
    $isParent = ($type === ProductTable::TYPE_SKU);
    $isOrphan = ($type === ProductTable::TYPE_EMPTY_SKU);
    $isOffer = in_array($type, [ProductTable::TYPE_OFFER, ProductTable::TYPE_FREE_OFFER], true);

    if ($isParent) {
        $parents++;
        $note = '  (вес берётся из ТП)';
    } elseif ($isOrphan) {
        $orphans++;
        $note = '  (у товара нет ТП - вес брать неоткуда)';
    } elseif ($isOffer) {
        $offers++;
        $note = '';
    } else {
        $simple++;
        $note = '';
    }

    if (!$showAll && $isParent) {
        continue;
    }

    $out(sprintf(
        '  #%-8d %-45s %-22s иб %-4s активен %s%s',
        $row['ID'],
        mb_strimwidth((string)$row['NAME'], 0, 45, '…'),
        $typeNames[$type] ?? ('тип ' . $type),
        $row['IBLOCK_ID'],
        $row['ACTIVE'],
        $note
    ));
}

$important = $offers + $simple + $orphans;

$out(str_repeat('-', 78));
$out('Всего позиций без веса:               ' . $total);
$out('  торговых предложений:               ' . $offers);
$out('  простых товаров:                    ' . $simple);
$out('  родителей без ТП:                   ' . $orphans);
$out('  родителей ТП (вес не нужен):        ' . $parents);
$out('ИТОГО требуют заполнения веса:        ' . $important);

if ($important > 0) {
    $out('');
    $out('Заказы с такими товарами уходят в DPD с весом 1 кг по умолчанию.');
    $out('Вес заполняется в карточке товара, поле "Вес" на вкладке "Торговый каталог".');
    $out('После заполнения родителя вес разойдётся по ТП автоматически -');
    $out('за это отвечает обработчик Room\\Events\\CatalogHandlers.');
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
