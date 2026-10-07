<?php

/**
 * Ревизия почтовых индексов в заказах
 *
 * Находит заказы, у которых PSC записан не пятью цифрами: с пробелом,
 * с буквами, короче или длиннее нормы, пустой. Такие заказы DPD отклоняет.
 *
 * По умолчанию НИЧЕГО НЕ МЕНЯЕТ и печатает только сводку и те индексы,
 * которые нужно править руками. Полный список - с флагом --all.
 *
 * С флагом --apply чинится только безопасный случай: цифр ровно пять,
 * но есть лишние символы ("831 02" -> "83102", "956/17" -> "95617").
 * Индексы, где цифр не пять, автоматически не трогаются - их правит менеджер.
 *
 * Пустой индекс адреса доставки (PSC_2) проблемой не считается: второй
 * адрес заполняется редко, и пустой он у большинства заказов.
 *
 * Запуск из консоли:
 *   php local/tools/check_order_zips.php                 # сводка + ручные случаи
 *   php local/tools/check_order_zips.php --all           # полный список
 *   php local/tools/check_order_zips.php --apply         # почистить лишние символы
 *   php local/tools/check_order_zips.php --limit=500
 *
 * Из браузера (только под администратором):
 *   /local/tools/check_order_zips.php
 */

// Коды свойств заказа с индексом: основной и адрес доставки
const ZIP_PROPERTY_CODES = ['PSС', 'PSС_2'];

// Свойство второго адреса: пустое значение здесь нормально
const ZIP_OPTIONAL_CODE = 'PSС_2';

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

\Bitrix\Main\Loader::includeModule('sale');

use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Internals\OrderPropsTable;
use Room\Services\DpdService;
use Room\Services\PscService;

// Разбор аргументов
$apply = false;
$showAll = false;
$limit = 0;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--apply') {
            $apply = true;
        } elseif ($arg === '--all') {
            $showAll = true;
        } elseif (strpos($arg, '--limit=') === 0) {
            $limit = (int)substr($arg, strlen('--limit='));
        }
    }
} else {
    $apply = !empty($_GET['apply']);
    $showAll = !empty($_GET['all']);
    $limit = (int)($_GET['limit'] ?? 0);
}

$out = static function (string $message = ''): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

// Идентификаторы свойств с индексом
$propertyIds = [];

$rsProps = OrderPropsTable::getList([
    'select' => ['ID', 'CODE', 'NAME'],
    'filter' => ['@CODE' => ZIP_PROPERTY_CODES],
]);

while ($prop = $rsProps->fetch()) {
    $propertyIds[(int)$prop['ID']] = $prop['CODE'];
}

if (empty($propertyIds)) {
    die('Не найдены свойства заказа с кодами: ' . implode(', ', ZIP_PROPERTY_CODES) . PHP_EOL);
}

// Сверка с реестром пошты доступна только после import_psc.php
$registry = PscService::isReady();

$out('Свойства с индексом: ' . implode(', ', $propertyIds));
$out($registry
    ? 'Сверка с реестром пошты: включена (' . PscService::count() . ' строк справочника)'
    : 'Сверка с реестром пошты: НЕДОСТУПНА, сначала запустите import_psc.php');
$out($apply ? 'Режим: ЗАПИСЬ (чистятся лишние символы)' : 'Режим: только просмотр');
$out($showAll ? 'Показываю все проблемные значения' : 'Показываю только те, что нужно править руками (--all - все)');
$out(str_repeat('-', 78));

$params = [
    'select' => ['ID', 'ORDER_ID', 'ORDER_PROPS_ID', 'VALUE'],
    'filter' => ['@ORDER_PROPS_ID' => array_keys($propertyIds)],
    'order' => ['ORDER_ID' => 'DESC'],
];

if ($limit > 0) {
    $params['limit'] = $limit;
}

$rsValues = OrderPropsValueTable::getList($params);

$total = 0;
$ok = 0;
$emptyOptional = 0;
$emptyRequired = 0;
$fixable = 0;
$manual = 0;
$unknown = 0;
$fixed = 0;

// Один индекс встречается в сотнях заказов, в базу ходим по разу на индекс
$knownCache = [];

while ($row = $rsValues->fetch()) {
    $total++;

    $value = trim((string)$row['VALUE']);
    $digits = DpdService::normalizeZip($value);
    $code = $propertyIds[(int)$row['ORDER_PROPS_ID']] ?? '?';

    $isValid = DpdService::isValidZip($value);

    // Существует ли такой индекс на самом деле. Формат из пяти цифр
    // ещё ничего не гарантирует: в заказе 2772 стоял 990 03, который
    // в реестре пошты не значится - у Veľký Krtíš индекс 990 01
    $isKnown = true;

    if ($isValid && $registry) {
        if (!isset($knownCache[$digits])) {
            $knownCache[$digits] = PscService::exists($digits);
        }

        $isKnown = $knownCache[$digits];
    }

    // Уже в правильном виде
    if ($value !== '' && $value === $digits && $isValid && $isKnown) {
        $ok++;
        continue;
    }

    // Лишние символы чинятся автоматически даже у неизвестного индекса:
    // это независимые вещи
    $canFix = $isValid && $value !== $digits;

    if ($value === '') {
        // Пустой второй адрес - обычное дело, в отчёт не тащим
        if ($code === ZIP_OPTIONAL_CODE) {
            $emptyOptional++;
            continue;
        }

        $emptyRequired++;
        $verdict = 'ПУСТОЙ основной индекс - заказ в DPD не уйдёт';
        $needsHand = true;
    } elseif (!$isValid) {
        $manual++;
        $verdict = 'цифр ' . mb_strlen($digits) . ' вместо 5 - правит менеджер';
        $needsHand = true;
    } elseif (!$isKnown) {
        $unknown++;
        $verdict = 'индекса "' . $digits . '" нет в реестре пошты - правит менеджер';
        $needsHand = true;

        if ($canFix) {
            $fixable++;
        }
    } else {
        $fixable++;
        $verdict = 'лишние символы -> "' . $digits . '"';
        $needsHand = false;
    }

    if ($showAll || $needsHand) {
        $out(sprintf(
            '  заказ %-8s %-8s "%s"  %s',
            $row['ORDER_ID'],
            $code,
            $value,
            $verdict
        ));
    }

    // Чиним только безопасный случай: цифр ровно пять
    if ($apply && $canFix) {
        $result = OrderPropsValueTable::update($row['ID'], ['VALUE' => $digits]);

        if ($result->isSuccess()) {
            $fixed++;
        } else {
            $out('        ОШИБКА записи: ' . implode('; ', $result->getErrorMessages()));
        }
    }
}

$out(str_repeat('-', 78));
$out('Проверено значений:                 ' . $total);
$out('  уже в правильном виде:            ' . $ok);
$out('  чинится автоматически:            ' . $fixable);
$out('  правит менеджер (не 5 цифр):      ' . $manual);
$out('  нет в реестре пошты:              ' . ($registry ? $unknown : 'не проверялось'));
$out('  пустой основной индекс:           ' . $emptyRequired);
$out('  пустой второй адрес (это норма):  ' . $emptyOptional);

if ($apply) {
    $out('');
    $out('Исправлено: ' . $fixed);
} elseif ($fixable > 0) {
    $out('');
    $out('Для исправления ' . $fixable . ' значений запустите с флагом --apply');
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
