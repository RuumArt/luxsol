<?php

/**
 * Знак умножения в названиях товаров: кириллическая «Х» → «×»
 *
 * В части названий размеры записаны кириллической буквой: «3Х2Х0,8Х1,2 m».
 * На вид не отличить, но для поиска и Google Merchant это чужой алфавит
 * посреди словацкого текста. Меняем только «Х»/«х» между цифрами - там это
 * знак умножения. Пробелы вокруг сохраняются как были.
 *
 * Пишем через CIBlockElement::Update, а не SQL: так изменение попадает
 * в журнал изменений каталога и обновляется поиск по сайту.
 *
 * Запуск:
 *   php local/tools/fix_names_x.php            показать, что изменится
 *   php local/tools/fix_names_x.php --apply    заменить
 */

// Каталог и торговые предложения
const IBLOCKS = [1, 6];

// «Х» между цифрами, пробелы вокруг допускаются
const PATTERN = '/(\d)(\s*)[Хх](\s*)(?=\d)/u';

// Латинская x между цифрами. Меняется только в тех названиях, где уже
// исправлена кириллица: встречается вперемешку («5X2Х1Х1,5»), и без этого
// осталось бы «5X2×1×1,5». Остальной каталог с латинской x не трогаем
const LATIN_PATTERN = '/(\d)(\s*)[Xx](\s*)(?=\d)/u';

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

$apply = in_array('--apply', $argv, true);

$changes = [];
$leftovers = [];

foreach (IBLOCKS as $iblockId) {
    $rs = \CIBlockElement::GetList(
        ['ID' => 'ASC'],
        ['IBLOCK_ID' => $iblockId, 'CHECK_PERMISSIONS' => 'N'],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE']
    );

    while ($row = $rs->Fetch()) {
        $name = $row['NAME'];

        if (!preg_match('/[Хх]/u', $name)) {
            continue;
        }

        $fixed = preg_replace(PATTERN, '$1$2×$3', $name);

        if ($fixed !== $name) {
            $fixed = preg_replace(LATIN_PATTERN, '$1$2×$3', $fixed);
            $changes[] = $row + ['FIXED' => $fixed];
        }

        // Кириллическая «Х» не между цифрами - не трогаем, только показываем
        if (preg_match('/[Хх]/u', $fixed)) {
            $leftovers[] = $row + ['FIXED' => $fixed];
        }
    }
}

echo 'Будет исправлено названий: ', count($changes), PHP_EOL;
foreach ($changes as $c) {
    printf("  #%-6d ib%d %s  %s%s    → %s%s", $c['ID'], $c['IBLOCK_ID'], $c['ACTIVE'] === 'Y' ? 'вкл ' : 'выкл', $c['NAME'], PHP_EOL, $c['FIXED'], PHP_EOL);
}

if ($leftovers) {
    echo PHP_EOL, 'Кириллическая «Х» не между цифрами - оставлено как есть: ', count($leftovers), PHP_EOL;
    foreach ($leftovers as $c) {
        printf("  #%-6d ib%d %s%s", $c['ID'], $c['IBLOCK_ID'], $c['FIXED'], PHP_EOL);
    }
}

echo str_repeat('-', 70), PHP_EOL;

if (!$apply) {
    echo 'Ничего не изменено. Для замены добавьте --apply', PHP_EOL;

    return;
}

$el = new \CIBlockElement();
$done = 0;

foreach ($changes as $c) {
    // Только NAME: остальные поля и свойства не передаём - они не меняются
    if ($el->Update($c['ID'], ['NAME' => $c['FIXED']])) {
        $done++;
    } else {
        echo '  ОШИБКА #', $c['ID'], ': ', $el->LAST_ERROR, PHP_EOL;
    }
}

// <title> и meta description строятся по SEO-шаблонам из названия, и готовые
// значения Битрикс хранит в b_iblock_element_iprop. Update их не пересчитывает:
// после замены в заголовке вкладки оставалась старая «Х». Сбрасываем - при
// следующем показе значения соберутся из нового названия
$seo = 0;
$rs = $GLOBALS['DB']->Query(
    "SELECT DISTINCT IBLOCK_ID, ELEMENT_ID FROM b_iblock_element_iprop
     WHERE IBLOCK_ID IN (" . implode(',', IBLOCKS) . ") AND VALUE REGEXP '[0-9][[:space:]]*(Х|х)[[:space:]]*[0-9]'"
);
while ($row = $rs->Fetch()) {
    (new \Bitrix\Iblock\InheritedProperty\ElementValues((int)$row['IBLOCK_ID'], (int)$row['ELEMENT_ID']))->clearValues();
    $seo++;
}

foreach (IBLOCKS as $iblockId) {
    \CIBlock::CleanCache($iblockId);
}

echo 'Готово: исправлено ', $done, ' из ', count($changes), ', SEO-заголовки сброшены у ', $seo, PHP_EOL;
