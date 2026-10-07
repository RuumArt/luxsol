<?php

/**
 * Настройка исключений карты сайта и её пересоздание без админки
 *
 * В карту попадали служебные файлы шаблонов (detail.php в разделах блога,
 * проектов и акций): это заготовки для вывода отдельной записи, отдельных
 * страниц у них нет. Сами записи попадают в карту через свои инфоблоки.
 *
 * Запуск из консоли:
 *   php local/tools/sitemap_regen.php                 показать настройки
 *   php local/tools/sitemap_regen.php --apply         добавить исключения
 *   php local/tools/sitemap_regen.php --generate      пересоздать карту
 *   php local/tools/sitemap_regen.php --apply --generate
 *   php local/tools/sitemap_regen.php --exclude=/blog/detail.php --apply
 */

// Служебные файлы, которых в карте быть не должно
const DEFAULT_EXCLUDES = [
    '/akciova-zlavy/detail.php',
    '/blog/detail.php',
    '/projects/detail.php',
];

// Сколько шагов генерации максимум, чтобы не уйти в вечный цикл
const MAX_STEPS = 400;

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

@set_time_limit(0);
ignore_user_abort(true);

\Bitrix\Main\Loader::includeModule('seo');

use Bitrix\Main\Application;
use Bitrix\Seo\Sitemap\Internals\SitemapTable;
use Bitrix\Seo\Sitemap\Job;

$apply = false;
$generate = false;
$excludes = [];

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--generate') {
        $generate = true;
    } elseif (strpos($arg, '--exclude=') === 0) {
        $excludes[] = substr($arg, strlen('--exclude='));
    }
}

if (empty($excludes)) {
    $excludes = DEFAULT_EXCLUDES;
}

$out = static function (string $message = ''): void {
    echo $message, PHP_EOL;

    if (ob_get_level() > 0) {
        ob_flush();
    }

    flush();
};

// ---------------------------------------------------------------- настройки
$sitemap = SitemapTable::getList(['select' => ['ID', 'NAME', 'SITE_ID', 'SETTINGS', 'ACTIVE']])->fetch();

if (!$sitemap) {
    die('Настройка карты сайта не найдена' . PHP_EOL);
}

$settings = is_array($sitemap['SETTINGS']) ? $sitemap['SETTINGS'] : unserialize($sitemap['SETTINGS']);

$out('Настройка: #' . $sitemap['ID'] . ' "' . $sitemap['NAME'] . '", сайт ' . $sitemap['SITE_ID']);
$out('Активные инфоблоки: ' . implode(', ', array_keys(array_filter(
    (array)($settings['IBLOCK_ACTIVE'] ?? []),
    static function ($v) {
        return $v === 'Y';
    }
))));
$out('Исключённые файлы: ' . implode(', ', array_keys(array_filter(
    (array)($settings['FILE'] ?? []),
    static function ($v) {
        return $v === 'N';
    }
))));
$out(str_repeat('-', 70));

$added = [];

foreach ($excludes as $path) {
    if (($settings['FILE'][$path] ?? null) === 'N') {
        $out('  уже исключён: ' . $path);
        continue;
    }

    if (!is_file($documentRoot . $path)) {
        $out('  ВНИМАНИЕ: файла нет на диске, пропускаю: ' . $path);
        continue;
    }

    $added[] = $path;
    $out('  будет исключён: ' . $path);
}

if (!empty($added) && $apply) {
    foreach ($added as $path) {
        $settings['FILE'][$path] = 'N';
    }

    // В таблице поле хранится сериализованной строкой, ORM само не упакует
    $result = SitemapTable::update($sitemap['ID'], ['SETTINGS' => serialize($settings)]);

    $out($result->isSuccess()
        ? 'Настройки сохранены, исключено файлов: ' . count($added)
        : 'ОШИБКА записи настроек: ' . implode('; ', $result->getErrorMessages()));
} elseif (!empty($added)) {
    $out('');
    $out('Настройки не менялись. Для записи добавьте --apply');
}

if (!$generate) {
    $out('');
    $out('Для пересоздания карты добавьте --generate');
    return;
}

// -------------------------------------------------------------- генерация
$out('');
$out('Пересоздание карты сайта...');

$job = Job::addJob((int)$sitemap['ID']);

if (!$job) {
    die('Не удалось поставить задание генерации' . PHP_EOL);
}

$steps = 0;
$start = microtime(true);

$status = '';

do {
    $job->doStep();
    $data = $job->getData();
    $steps++;

    // getData() отдаёт ключи в нижнем регистре: status, statusMessage, step
    $status = (string)($data['status'] ?? '');

    if ($steps % 10 === 0) {
        $out(sprintf(
            '  шаг %d (этап %s): %s',
            $steps,
            (string)($data['step'] ?? '?'),
            (string)($data['statusMessage'] ?? '')
        ));
    }
} while ($status !== Job::STATUS_FINISH && $status !== Job::STATUS_ERROR && $steps < MAX_STEPS);

$out(sprintf('  шагов: %d, время: %.1f с, статус: %s', $steps, microtime(true) - $start, $status));

if ($status === Job::STATUS_ERROR) {
    $out('  ОШИБКА: ' . (string)($data['statusMessage'] ?? ''));
    return;
}

if ($steps >= MAX_STEPS) {
    $out('  ВНИМАНИЕ: достигнут предел шагов, карта может быть неполной');
}

// ------------------------------------------------------------- что вышло
$out('');
$out('Файлы карты:');

// Какие файлы карты реально подключены к индексу
$index = (string)@file_get_contents($documentRoot . '/' . ($settings['FILENAME_INDEX'] ?? 'sitemap.xml'));
$stale = [];

foreach (glob($documentRoot . '/sitemap*.xml') as $file) {
    $name = basename($file);
    $count = substr_count((string)file_get_contents($file), '<loc>');
    $linked = $name === ($settings['FILENAME_INDEX'] ?? 'sitemap.xml') || strpos($index, $name) !== false;

    if (!$linked) {
        $stale[] = $name;
    }

    $out(sprintf(
        '  %-28s %7s адресов, %6.1f КБ, изменён %s%s',
        $name,
        $count,
        filesize($file) / 1024,
        date('d.m.Y H:i', filemtime($file)),
        $linked ? '' : '   <-- НЕ в индексе'
    ));
}

if (!empty($stale)) {
    $out('');
    $out('Файлы карты, на которые индекс не ссылается, остались от прошлых настроек.');
    $out('Поисковики могут читать их по прямой ссылке. Удалить вручную:');
    $out('  cd ' . $documentRoot . ' && rm ' . implode(' ', $stale));
}

require_once $documentRoot . '/bitrix/modules/main/include/epilog_after.php';
