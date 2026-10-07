<?php

/**
 * Диагностика блоков "Alternatívne produkty" (ITEM_UPSELL)
 * и "Súvisiace produkty" (ITEM_WITH) на детальной странице товара
 *
 * Показывает, что реально лежит в свойствах связей и что покажет компонент.
 * Ничего не меняет.
 *
 * Запуск из консоли:
 *   php local/tools/check_product_links.php --article=420105
 *   php local/tools/check_product_links.php --id=1234
 *
 * Из браузера (только под администратором):
 *   /local/tools/check_product_links.php?article=420105
 */

const CATALOG_IBLOCK_ID = 1;

// Код свойства с артикулом
const ARTICLE_PROP = 'ARTICLE';

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

\Bitrix\Main\Loader::includeModule('iblock');

// Разбор аргументов
$article = '';
$elementId = 0;
$checkAll = false;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--all') {
            $checkAll = true;
        } elseif (strpos($arg, '--article=') === 0) {
            $article = substr($arg, strlen('--article='));
        } elseif (strpos($arg, '--id=') === 0) {
            $elementId = (int)substr($arg, strlen('--id='));
        }
    }
} else {
    $checkAll = !empty($_GET['all']);
    $article = (string)($_GET['article'] ?? '');
    $elementId = (int)($_GET['id'] ?? 0);
}

if (!$checkAll && $article === '' && $elementId <= 0) {
    die('Укажите --article=420105, --id=1234 или --all' . PHP_EOL);
}

/**
 * Значения свойств связей одного товара
 *
 * GetProperty принимает CODE только строкой, поэтому обходим коды по одному:
 * со списком в фильтре mysqli падает на forSql
 */
function room_link_props(int $elementId): array
{
    $props = [];

    foreach (['ITEM_UPSELL', 'ITEM_WITH', 'UPSELL_TITLES'] as $propCode) {
        $rs = CIBlockElement::GetProperty(
            CATALOG_IBLOCK_ID,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['CODE' => $propCode]
        );

        while ($prop = $rs->Fetch()) {
            if ($prop['VALUE'] === null || $prop['VALUE'] === '') {
                continue;
            }

            $props[$prop['CODE']][] = $prop['VALUE'];
        }
    }

    return $props;
}

/**
 * Краткая карточка элемента: активность, раздел, название
 */
function room_describe_elements(array $ids): array
{
    if (empty($ids)) {
        return [];
    }

    $result = [];

    $rs = CIBlockElement::GetList(
        [],
        ['ID' => $ids, 'CHECK_PERMISSIONS' => 'N'],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE', 'IBLOCK_SECTION_ID']
    );

    while ($row = $rs->Fetch()) {
        $result[(int)$row['ID']] = $row;
    }

    return $result;
}

// ------------------------------------------------ массовая проверка связей
if ($checkAll) {
    // Кандидаты: товары, у которых связи вообще заполнены
    $candidates = [];

    foreach (['ITEM_UPSELL', 'ITEM_WITH'] as $propCode) {
        $rs = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => CATALOG_IBLOCK_ID,
                'CHECK_PERMISSIONS' => 'N',
                '!PROPERTY_' . $propCode => false,
            ],
            false,
            false,
            ['ID', 'NAME', 'ACTIVE']
        );

        while ($row = $rs->Fetch()) {
            $candidates[(int)$row['ID']] = $row;
        }
    }

    echo 'Товаров со связями: ', count($candidates), PHP_EOL;
    echo str_repeat('-', 78), PHP_EOL;

    $broken = 0;
    $titleMismatch = 0;
    $checkedLinks = 0;

    foreach ($candidates as $id => $row) {
        $props = room_link_props($id);
        $problems = [];

        foreach (['ITEM_UPSELL' => 'ALT', 'ITEM_WITH' => 'SUV'] as $code => $short) {
            $values = $props[$code] ?? [];

            if (empty($values)) {
                continue;
            }

            $found = room_describe_elements(array_map('intval', $values));

            foreach ($values as $value) {
                $checkedLinks++;
                $target = $found[(int)$value] ?? null;

                if (!$target) {
                    $problems[] = $short . ' ' . $value . ': элемента нет';
                } elseif ($target['ACTIVE'] !== 'Y') {
                    $problems[] = $short . ' #' . $value . ': товар отключён ('
                        . mb_strimwidth((string)$target['NAME'], 0, 30, '…') . ')';
                } elseif ((int)$target['IBLOCK_ID'] !== CATALOG_IBLOCK_ID) {
                    $problems[] = $short . ' #' . $value . ': чужой инфоблок ' . $target['IBLOCK_ID'];
                }
            }
        }

        // Подписи раздаются по позиции, поэтому их должно быть столько же
        $upsellCount = count($props['ITEM_UPSELL'] ?? []);
        $titlesCount = count($props['UPSELL_TITLES'] ?? []);

        if ($titlesCount > 0 && $titlesCount !== $upsellCount) {
            $problems[] = 'подписей ' . $titlesCount . ', товаров ' . $upsellCount;
            $titleMismatch++;
        }

        if (empty($problems)) {
            continue;
        }

        $broken++;

        printf(
            "#%-7d %-40s %s\n",
            $id,
            mb_strimwidth((string)$row['NAME'], 0, 40, '…'),
            $row['ACTIVE'] === 'Y' ? '' : '(сам отключён)'
        );

        foreach ($problems as $problem) {
            echo '          ', $problem, PHP_EOL;
        }
    }

    echo str_repeat('-', 78), PHP_EOL;
    echo 'Проверено связей:            ', $checkedLinks, PHP_EOL;
    echo 'Товаров с битыми связями:    ', $broken, PHP_EOL;
    echo 'Из них расходятся подписи:   ', $titleMismatch, PHP_EOL;
    echo PHP_EOL;
    echo 'ALT - Alternatívne produkty (ITEM_UPSELL)', PHP_EOL;
    echo 'SUV - Súvisiace produkty (ITEM_WITH)', PHP_EOL;

    require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
    return;
}

// Находим товар
$filter = ['IBLOCK_ID' => CATALOG_IBLOCK_ID, 'CHECK_PERMISSIONS' => 'N'];

if ($elementId > 0) {
    $filter['ID'] = $elementId;
} else {
    $filter['PROPERTY_' . ARTICLE_PROP] = $article;
}

$rs = CIBlockElement::GetList([], $filter, false, false, [
    'ID', 'IBLOCK_ID', 'NAME', 'ACTIVE', 'IBLOCK_SECTION_ID', 'PROPERTY_' . ARTICLE_PROP,
]);

$product = $rs->Fetch();

if (!$product) {
    die('Товар не найден' . PHP_EOL);
}

printf(
    "Товар: #%d  %s\n  артикул: %s | активен: %s | раздел: %s\n\n",
    $product['ID'],
    $product['NAME'],
    (string)$product['PROPERTY_' . ARTICLE_PROP . '_VALUE'],
    $product['ACTIVE'],
    (string)$product['IBLOCK_SECTION_ID']
);

// Свойства связей
$props = room_link_props((int)$product['ID']);

foreach (['ITEM_UPSELL' => 'Alternatívne produkty', 'ITEM_WITH' => 'Súvisiace produkty'] as $code => $title) {
    $values = $props[$code] ?? [];

    echo str_repeat('=', 70), PHP_EOL;
    printf("%s  (свойство %s)\n", $title, $code);
    echo str_repeat('=', 70), PHP_EOL;

    if (empty($values)) {
        echo "  свойство ПУСТОЕ\n";

        if ($code === 'ITEM_WITH') {
            printf(
                "  -> шаблон покажет 3 СЛУЧАЙНЫХ товара из раздела %s\n",
                (string)$product['IBLOCK_SECTION_ID']
            );
        } else {
            echo "  -> блок не выводится вообще\n";
        }

        echo PHP_EOL;
        continue;
    }

    printf("  значений в свойстве: %d\n", count($values));
    printf("  как записано: %s\n\n", implode(', ', $values));

    $numeric = array_values(array_filter(array_map('intval', $values)));
    $found = room_describe_elements($numeric);

    foreach ($values as $i => $value) {
        $id = (int)$value;

        if (!isset($found[$id])) {
            printf(
                "  %d. %-10s НЕ НАЙДЕН элемент с таким ID%s\n",
                $i + 1,
                $value,
                is_numeric($value) ? '' : ' (значение не число - свойство не привязка к элементам?)'
            );

            continue;
        }

        $item = $found[$id];

        printf(
            "  %d. #%-7d %-45s активен: %s | иб: %s | раздел: %s\n",
            $i + 1,
            $item['ID'],
            mb_strimwidth($item['NAME'], 0, 45, '…'),
            $item['ACTIVE'],
            $item['IBLOCK_ID'],
            (string)$item['IBLOCK_SECTION_ID']
        );
    }

    echo PHP_EOL;
}

// Подписи блока альтернатив
$titles = $props['UPSELL_TITLES'] ?? [];
$upsell = $props['ITEM_UPSELL'] ?? [];

if (!empty($titles) || !empty($upsell)) {
    echo str_repeat('=', 70), PHP_EOL;
    echo "Подписи UPSELL_TITLES", PHP_EOL;
    echo str_repeat('=', 70), PHP_EOL;

    printf("  товаров: %d, подписей: %d%s\n\n", count($upsell), count($titles),
        count($upsell) === count($titles) ? '' : '   <-- НЕ СОВПАДАЕТ');

    foreach ($titles as $i => $title) {
        printf("  %d. %s\n", $i + 1, $title);
    }

    echo PHP_EOL;
    echo "  Внимание: шаблон upsell выдаёт подписи по порядковому номеру\n";
    echo "  вывода, а компонент сортирует товары по своим правилам, а не\n";
    echo "  по порядку значений свойства. Порядок может не совпадать.\n\n";
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
