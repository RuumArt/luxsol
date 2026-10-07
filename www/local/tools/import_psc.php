<?php

/**
 * Импорт справочника словацких почтовых индексов
 *
 * Источник - официальные файлы Slovenská pošta:
 *   OBCE.xlsx    населённые пункты (лист "obce" и лист "detail SČ")
 *   ULICE.xlsx   улицы городов, где у одного города несколько индексов
 *   POBoxy.xlsx  индексы абонентских ящиков
 *
 * Все три нужны вместе: в OBCE.xlsx у 163 крупнейших городов колонка PSC
 * пустая, вместо индекса стоит пометка "Pozri zoz.ulíc s PSČ" или
 * "viď detail SČ". Индексы Братиславы, Кошиц, Банской Бистрицы и прочих
 * лежат только в ULICE.xlsx и на листе "detail SČ".
 *
 * Индексы из POBoxy.xlsx помечаются отдельно и в подсказки не попадают:
 * из 3047 таких индексов 1946 не существуют как территориальные, курьер
 * по ним не доставляет. Метка нужна, чтобы объяснить это покупателю.
 *
 * По умолчанию НИЧЕГО НЕ ПИШЕТ: разбирает файлы и показывает, что получится.
 * Запись - с флагом --apply. Таблица наполняется во временной копии и
 * подменяется одним RENAME, так что подсказки на сайте не пропадают.
 *
 * Файлы кладутся в local/tools/psc/ (или укажите --dir=)
 *
 * Запуск из консоли:
 *   php local/tools/import_psc.php
 *   php local/tools/import_psc.php --apply
 *   php local/tools/import_psc.php --dir=/var/www/upload/psc --apply
 *
 * Из браузера (только под администратором):
 *   /local/tools/import_psc.php
 */

// Куда класть файлы почты по умолчанию
const DEFAULT_DATA_DIR = __DIR__ . '/psc';

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
@ini_set('memory_limit', '512M');
ignore_user_abort(true);

use Bitrix\Main\Application;
use Room\Services\PscService;
use Room\Tools\Xlsx;

// Разбор аргументов
$apply = false;
$dataDir = DEFAULT_DATA_DIR;

if ($isCli) {
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--apply') {
            $apply = true;
        } elseif (strpos($arg, '--dir=') === 0) {
            $dataDir = rtrim(substr($arg, strlen('--dir=')), '/');
        }
    }
} else {
    $apply = !empty($_GET['apply']);

    if (!empty($_GET['dir'])) {
        $dataDir = rtrim($_GET['dir'], '/');
    }
}

$out = static function (string $message = ''): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

$out('Папка с файлами: ' . $dataDir);
$out($apply ? 'Режим: ЗАПИСЬ в ' . PscService::TABLE : 'Режим: только разбор файлов');
$out(str_repeat('-', 78));

// ------------------------------------------------------------------- разбор
// Ключ строки, чтобы не задваивать одинаковые записи из разных листов
$rows = [];
$stats = [];

/**
 * Добавляет строку справочника
 */
$add = static function (
    string $src,
    string $psc,
    string $obec,
    string $ulica = '',
    string $okres = '',
    string $kraj = '',
    string $posta = ''
) use (&$rows, &$stats): void {
    $psc = PscService::normalize($psc);
    $obec = trim($obec);

    if (strlen($psc) !== 5 || $obec === '') {
        return;
    }

    $key = $src . '|' . $psc . '|' . $obec . '|' . $ulica;

    if (isset($rows[$key])) {
        return;
    }

    $rows[$key] = [
        'PSC' => $psc,
        'OBEC' => $obec,
        'OBEC_KEY' => PscService::searchKey($obec),
        'ULICA' => trim($ulica),
        'OKRES' => trim($okres),
        'KRAJ' => trim($kraj),
        'POSTA' => trim($posta),
        'SRC' => $src,
    ];

    $stats[$src] = ($stats[$src] ?? 0) + 1;
};

try {
    // ------------------------------------------------------------ OBCE.xlsx
    $obce = new Xlsx($dataDir . '/OBCE.xlsx');

    // Лист "obce": DOBEC, OBEC, OKRES, PSC, DPOSTA, POSTA, KOD_OKR, KRAJ
    $sheet = $obce->findSheet('obce');

    if ($sheet === '') {
        throw new \Exception('В OBCE.xlsx не найден лист "obce"');
    }

    $first = true;

    foreach ($obce->rows($sheet) as $row) {
        if ($first) {
            $first = false;
            continue;
        }

        $row += array_fill(0, 8, '');
        $add(PscService::SRC_OBEC, $row[3], $row[1], '', $row[2], $row[7], $row[5]);
    }

    // Лист "detail SČ": населённые пункты без улиц, но с несколькими индексами
    // OBEC, OKRES, KOD_OKR, KRAJ, PSC, POSTA, miestne pomenovanie, Súpisné čísla
    $sheet = $obce->findSheet('detail');

    if ($sheet !== '') {
        foreach ($obce->rows($sheet) as $row) {
            $row += array_fill(0, 8, '');
            $add(PscService::SRC_DETAIL, $row[4], $row[0], '', $row[1], $row[3], $row[5]);
        }
    } else {
        $out('ВНИМАНИЕ: в OBCE.xlsx не найден лист "detail SČ"');
    }

    unset($obce);

    // ----------------------------------------------------------- ULICE.xlsx
    // DULICA, ULICA, PSC, DPOSTA, POSTA, POZNAMKA, OBCE
    $ulice = new Xlsx($dataDir . '/ULICE.xlsx');
    $sheet = $ulice->findSheet('ulice');

    if ($sheet === '') {
        throw new \Exception('В ULICE.xlsx не найден лист "ulice"');
    }

    $first = true;

    foreach ($ulice->rows($sheet) as $row) {
        if ($first) {
            $first = false;
            continue;
        }

        $row += array_fill(0, 7, '');
        $add(PscService::SRC_STREET, $row[2], $row[6], $row[1], '', '', $row[4]);
    }

    unset($ulice);

    // ---------------------------------------------------------- POBoxy.xlsx
    // (пусто), Názov pošty, Kraj, Okres, Počet PSČ, PSČ pre P.O.BOXy, Typ PSČ
    $poboxPath = $dataDir . '/POBoxy.xlsx';

    if (is_file($poboxPath)) {
        $pobox = new Xlsx($poboxPath);
        $sheet = $pobox->findSheet('pobox');

        if ($sheet !== '') {
            foreach ($pobox->rows($sheet) as $row) {
                $row += array_fill(0, 7, '');

                // Территориальные индексы уже пришли из OBCE и ULICE,
                // отдельно нужны только специфические
                if (mb_strpos(PscService::searchKey($row[6]), 'specificke') === false) {
                    continue;
                }

                $add(PscService::SRC_POBOX, $row[5], $row[1], '', $row[3], $row[2], $row[1]);
            }
        } else {
            $out('ВНИМАНИЕ: в POBoxy.xlsx не найден лист с индексами');
        }

        unset($pobox);
    } else {
        $out('POBoxy.xlsx не найден, проверка на абонентские ящики работать не будет');
    }
} catch (\Throwable $e) {
    $out('ОШИБКА разбора: ' . $e->getMessage());
    require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
    return;
}

// -------------------------------------------------------------------- итоги
$titles = [
    PscService::SRC_OBEC => 'OBCE, лист obce',
    PscService::SRC_DETAIL => 'OBCE, лист detail SČ',
    PscService::SRC_STREET => 'ULICE',
    PscService::SRC_POBOX => 'POBoxy (абонентские ящики)',
];

$out('');
$out('Разобрано строк');
$out(str_repeat('-', 78));

foreach ($titles as $src => $title) {
    $out(sprintf('  %-30s %6d', $title, $stats[$src] ?? 0));
}

$deliverable = [];
$cities = [];

foreach ($rows as $row) {
    if (in_array($row['SRC'], PscService::DELIVERABLE, true)) {
        $deliverable[$row['PSC']] = true;
        $cities[$row['OBEC']] = true;
    }
}

$out('');
$out('  всего строк справочника:       ' . count($rows));
$out('  индексов с доставкой:          ' . count($deliverable));
$out('  населённых пунктов:            ' . count($cities));

// Контроль: без ULICE в справочнике не будет крупных городов
foreach (['Bratislava', 'Košice', 'Banská Bystrica'] as $check) {
    $found = 0;

    foreach ($rows as $row) {
        if ($row['OBEC'] === $check && in_array($row['SRC'], PscService::DELIVERABLE, true)) {
            $found++;
        }
    }

    $out(sprintf('  %-30s %s', $check . ':', $found > 0 ? $found . ' строк' : 'НЕ НАЙДЕН'));
}

if (count($deliverable) < 1000) {
    $out('');
    $out('ВНИМАНИЕ: индексов подозрительно мало, ожидается около 1400.');
    $out('Похоже, один из файлов не прочитался - записывать такое не стоит.');
}

if (!$apply) {
    $out('');
    $out('Записи не было. Для загрузки в базу запустите с флагом --apply');
    require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
    return;
}

// --------------------------------------------------------------- запись
$connection = Application::getConnection();
$helper = $connection->getSqlHelper();

$table = PscService::TABLE;
$temp = $table . '_import';

$out('');
$out('Заполняем ' . $temp . '...');

$connection->queryExecute('DROP TABLE IF EXISTS ' . $temp);
$connection->queryExecute(
    'CREATE TABLE ' . $temp . ' ('
    . ' ID int unsigned NOT NULL AUTO_INCREMENT,'
    . ' PSC char(5) NOT NULL,'
    . ' OBEC varchar(150) NOT NULL,'
    . ' OBEC_KEY varchar(150) NOT NULL,'
    . ' ULICA varchar(150) NOT NULL,'
    . ' OKRES varchar(100) NOT NULL,'
    . ' KRAJ varchar(10) NOT NULL,'
    . ' POSTA varchar(150) NOT NULL,'
    . ' SRC char(1) NOT NULL,'
    . ' PRIMARY KEY (ID),'
    . ' KEY IX_PSC (PSC),'
    . ' KEY IX_OBEC_KEY (OBEC_KEY),'
    . ' KEY IX_SRC (SRC)'
    . ')'
);

$batch = [];
$written = 0;

$flush = static function () use (&$batch, &$written, $connection, $temp): void {
    if (empty($batch)) {
        return;
    }

    $connection->queryExecute(
        'INSERT INTO ' . $temp
        . ' (PSC, OBEC, OBEC_KEY, ULICA, OKRES, KRAJ, POSTA, SRC) VALUES '
        . implode(',', $batch)
    );

    $written += count($batch);
    $batch = [];
};

foreach ($rows as $row) {
    $batch[] = "('"
        . $helper->forSql($row['PSC']) . "','"
        . $helper->forSql($row['OBEC'], 150) . "','"
        . $helper->forSql($row['OBEC_KEY'], 150) . "','"
        . $helper->forSql($row['ULICA'], 150) . "','"
        . $helper->forSql($row['OKRES'], 100) . "','"
        . $helper->forSql($row['KRAJ'], 10) . "','"
        . $helper->forSql($row['POSTA'], 150) . "','"
        . $helper->forSql($row['SRC']) . "')";

    if (count($batch) >= 500) {
        $flush();
    }
}

$flush();

$out('  записано строк: ' . $written);

// Подмена одним движением: на сайте справочник не пропадает
if ($connection->isTableExists($table)) {
    $connection->queryExecute(
        'RENAME TABLE ' . $table . ' TO ' . $table . '_old, '
        . $temp . ' TO ' . $table
    );
    $connection->queryExecute('DROP TABLE ' . $table . '_old');
} else {
    $connection->queryExecute('RENAME TABLE ' . $temp . ' TO ' . $table);
}

$out('  таблица ' . $table . ' обновлена');
$out('');
$out('Готово. Проверить: /ajax/get_region.php?psc=98513');

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
