<?php

/**
 * Журнал изменений каталога: кто, когда и что менял в товарах
 *
 * Битрикс умеет писать такие события, но по умолчанию это выключено,
 * а сам журнал хранится всего 7 дней. Из-за этого вопрос «кто отключил
 * товары два месяца назад» в принципе не имел ответа.
 *
 * Инструмент включает запись событий по каталогу и торговым предложениям
 * и продлевает срок хранения журнала.
 *
 * Запуск:
 *   php local/tools/catalog_log.php            показать текущее состояние
 *   php local/tools/catalog_log.php --apply    включить
 *   php local/tools/catalog_log.php --off      выключить обратно
 */

// Инфоблоки, изменения в которых пишем: каталог и торговые предложения
const IBLOCKS = [1, 6];

// Какие события отслеживаем
const LOG_FIELDS = [
    'LOG_ELEMENT_ADD' => 'добавление товара',
    'LOG_ELEMENT_EDIT' => 'изменение товара',
    'LOG_ELEMENT_DELETE' => 'удаление товара',
    'LOG_SECTION_ADD' => 'добавление раздела',
    'LOG_SECTION_EDIT' => 'изменение раздела',
    'LOG_SECTION_DELETE' => 'удаление раздела',
];

// Сколько дней хранить журнал. По умолчанию в Битриксе 7 - слишком мало,
// чтобы разбираться в том, что случилось месяц назад
const KEEP_DAYS = 365;

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

\Bitrix\Main\Loader::includeModule('iblock');

use Bitrix\Main\Config\Option;

$apply = in_array('--apply', $argv, true);
$off = in_array('--off', $argv, true);
$value = $off ? 'N' : 'Y';

echo 'Текущее состояние:', PHP_EOL;

foreach (IBLOCKS as $iblockId) {
    $iblock = \CIBlock::GetArrayByID($iblockId);

    if (!$iblock) {
        echo '  инфоблок #', $iblockId, ' не найден', PHP_EOL;
        continue;
    }

    echo '  #', $iblockId, ' ', $iblock['NAME'], PHP_EOL;

    foreach (LOG_FIELDS as $field => $title) {
        $state = ($iblock['FIELDS'][$field]['IS_REQUIRED'] ?? 'N') === 'Y' ? 'пишется' : 'не пишется';
        echo '      ', $title, ': ', $state, PHP_EOL;
    }
}

echo '  срок хранения журнала: ', Option::get('main', 'event_log_cleanup_days', '7'), ' дн.', PHP_EOL;
echo '  изменения структуры инфоблоков: ',
    Option::get('iblock', 'event_log_iblock', 'N') === 'Y' ? 'пишутся' : 'не пишутся', PHP_EOL;

echo str_repeat('-', 70), PHP_EOL;

if (!$apply && !$off) {
    echo 'Ничего не изменено. Для включения добавьте --apply, для выключения --off', PHP_EOL;

    return;
}

foreach (IBLOCKS as $iblockId) {
    $iblock = \CIBlock::GetArrayByID($iblockId);

    if (!$iblock) {
        continue;
    }

    // SetFields ждёт полный набор полей инфоблока: берём текущий и правим нужные
    $fields = $iblock['FIELDS'];

    foreach (array_keys(LOG_FIELDS) as $field) {
        $fields[$field]['IS_REQUIRED'] = $value;
    }

    \CIBlock::SetFields($iblockId, $fields);

    echo '  #', $iblockId, ' ', $iblock['NAME'], ': ', $off ? 'выключено' : 'включено', PHP_EOL;
}

Option::set('iblock', 'event_log_iblock', $value);

if (!$off) {
    Option::set('main', 'event_log_cleanup_days', (string)KEEP_DAYS);
    echo '  срок хранения журнала: ', KEEP_DAYS, ' дн.', PHP_EOL;
}

// Настройки инфоблоков лежат в управляемом кеше
foreach (IBLOCKS as $iblockId) {
    \CIBlock::CleanCache($iblockId);
}

\Bitrix\Main\Application::getInstance()->getManagedCache()->clean('b_iblock');

echo str_repeat('-', 70), PHP_EOL;
echo 'Готово. Журнал: Админка -> Настройки -> Инструменты -> Журнал событий', PHP_EOL;
