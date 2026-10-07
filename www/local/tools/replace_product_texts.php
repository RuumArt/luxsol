<?php

/**
 * Разовый скрипт: замена текста в "Детальном описании" (DETAIL_TEXT)
 * и "Описании для анонса" (PREVIEW_TEXT) элементов каталога
 *
 * Правила замены перечислены в $rules ниже.
 *
 * По умолчанию скрипт НИЧЕГО НЕ МЕНЯЕТ, а только показывает, что будет заменено.
 * Для реальной записи нужен флаг --apply.
 *
 * Запуск из консоли:
 *   php local/tools/replace_product_texts.php                  # предпросмотр
 *   php local/tools/replace_product_texts.php --apply          # запись
 *   php local/tools/replace_product_texts.php --apply --iblock=1,2
 *
 * Запуск из браузера (только под администратором):
 *   /local/tools/replace_product_texts.php
 *   /local/tools/replace_product_texts.php?apply=1&iblock=1
 *
 * Перед записью старые значения сохраняются в
 *   /local/tools/backup/replace_product_texts_<дата>.json
 * Оттуда их можно вернуть вручную, если замена окажется неудачной.
 */

// Инфоблоки по умолчанию, если не передан --iblock
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

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * Правила замены
 *
 * pattern - регулярное выражение, replace - на что менять
 * \b по краям не даёт зацепить часть слова: "OKOLO" и "kgstore" не тронутся
 */
$rules = [
    // "5 kgs" -> "5 kg". Регистр не важен, но пишем всегда строчными
    [
        'pattern' => '/\bkgs\b/iu',
        'replace' => 'kg',
        'title' => 'kgs -> kg',
    ],
    // "OKO 12 CM" -> "Veľkosť oka 12 CM". Любой регистр: OKO, Oko, oko
    //
    // Внимание: "oko" - обычное словацкое слово, и в связном тексте
    // оно тоже будет заменено. Смотрите предпросмотр перед --apply.
    // Если нужно только там, где дальше идёт число, замените шаблон на:
    //   '/\boko\b(?=\s*\d)/iu'
    [
        'pattern' => '/\boko\b/iu',
        'replace' => 'Veľkosť oka',
        'title' => 'OKO/Oko/oko -> Veľkosť oka',
    ],
    // "Pevnosť šnúry" -> "Minimálna pevnosť pri pretrhnutí". Любой регистр.
    // Между словами допускаем и обычные пробелы, и &nbsp; - в описаниях
    // из визуального редактора часто стоит именно неразрывный пробел
    [
        // \x{165}=ť \x{164}=Ť, \x{161}=š \x{160}=Š, \x{fa}=ú \x{da}=Ú:
        // диакритику перечисляем классами, чтобы регистр не зависел
        // от поддержки Unicode-фолдинга в PCRE на конкретном сервере
        'pattern' => '/\bpevnos[\x{165}\x{164}](?:\s|&nbsp;)+[\x{161}\x{160}]n[\x{fa}\x{da}]ry\b/iu',
        'replace' => 'Minimálna pevnosť pri pretrhnutí',
        'title' => 'Pevnosť šnúry -> Minimálna pevnosť pri pretrhnutí',
    ],
];

// Разбор аргументов: --apply, --iblock=1,2
$apply = false;
$iblockIds = DEFAULT_IBLOCK_IDS;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--apply') {
            $apply = true;
        } elseif (strpos($arg, '--iblock=') === 0) {
            $iblockIds = explode(',', substr($arg, strlen('--iblock=')));
        }
    }
} else {
    $apply = !empty($_GET['apply']);

    if (!empty($_GET['iblock'])) {
        $iblockIds = explode(',', $_GET['iblock']);
    }
}

$iblockIds = array_values(array_filter(array_map('intval', $iblockIds)));

if (empty($iblockIds)) {
    die('Не указан инфоблок' . PHP_EOL);
}

$out = static function (string $message): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

/**
 * Применяет правила к тексту
 *
 * @param string $text
 * @param array $rules
 * @return string
 */
$applyRules = static function (string $text, array $rules): string {
    foreach ($rules as $rule) {
        $text = preg_replace($rule['pattern'], $rule['replace'], $text);
    }

    return $text;
};

$out('Инфоблоки: ' . implode(', ', $iblockIds));
$out('Правила: ' . implode('; ', array_column($rules, 'title')));
$out($apply ? 'Режим: ЗАПИСЬ' : 'Режим: предпросмотр (для записи добавьте --apply)');
$out(str_repeat('-', 60));

$element = new CIBlockElement();

$total = 0;
$changed = 0;
$errors = 0;
$backup = [];

foreach ($iblockIds as $iblockId) {
    $rsElements = CIBlockElement::GetList(
        ['ID' => 'ASC'],
        [
            'IBLOCK_ID' => $iblockId,
            'CHECK_PERMISSIONS' => 'N',
        ],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
    );

    // Fetch, а не GetNext: GetNext прогоняет поля через htmlspecialchars
    // и сохранённый обратно текст оказался бы поломанным
    while ($item = $rsElements->Fetch()) {
        $total++;

        $fields = [];

        foreach (['PREVIEW_TEXT', 'DETAIL_TEXT'] as $field) {
            $old = (string)$item[$field];

            if ($old === '') {
                continue;
            }

            $new = $applyRules($old, $rules);

            if ($new !== $old) {
                $fields[$field] = $new;
            }
        }

        if (empty($fields)) {
            continue;
        }

        $changed++;

        $out('#' . $item['ID'] . ' ' . $item['NAME'] . ' - ' . implode(', ', array_keys($fields)));

        if (!$apply) {
            continue;
        }

        $backup[] = [
            'ID' => (int)$item['ID'],
            'IBLOCK_ID' => (int)$item['IBLOCK_ID'],
            'PREVIEW_TEXT' => $item['PREVIEW_TEXT'],
            'DETAIL_TEXT' => $item['DETAIL_TEXT'],
        ];

        if (!$element->Update($item['ID'], $fields)) {
            $errors++;
            $out('   ОШИБКА: ' . $element->LAST_ERROR);
        }
    }
}

if ($apply && !empty($backup)) {
    $backupDir = __DIR__ . '/backup';

    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0775, true);
    }

    $backupFile = $backupDir . '/replace_product_texts_' . date('Y-m-d_H-i-s') . '.json';

    file_put_contents($backupFile, json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $out('Бэкап старых значений: ' . $backupFile);
}

$out(str_repeat('-', 60));
$out('Просмотрено элементов: ' . $total);
$out(($apply ? 'Обновлено: ' : 'Будет обновлено: ') . $changed);

if ($errors) {
    $out('Ошибок при сохранении: ' . $errors);
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
