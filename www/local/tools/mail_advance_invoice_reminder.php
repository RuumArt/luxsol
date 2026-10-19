<?php
/**
 * Create the Bitrix advance-invoice reminder using the existing customer mail theme.
 * Run after deploying files, as the site's PHP user, from DOCUMENT_ROOT:
 *   php -d short_open_tag=1 local/tools/mail_advance_invoice_reminder.php
 *   php -d short_open_tag=1 local/tools/mail_advance_invoice_reminder.php --apply
 * Optional: --source=30 selects a particular SALE_NEW_ORDER customer template.
 * Default is a preview. --apply creates only missing records, preserves later admin
 * edits and never calls a mail sender, creates agents or schedules reminders.
 * Admin: Settings > Product settings > Mail events > Mail templates;
 * filter EVENT_NAME = LUXSOL_ADVANCE_INVOICE_REMINDER.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$options = getopt('', ['apply', 'source:']);
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'luxsol.sk';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Room\Tools\AdvanceInvoiceReminderTemplate as Reminder;

$site = 's1';
$connection = \Bitrix\Main\Application::getConnection();
try {
    $existing = [];
    $rows = \CEventMessage::GetList('id', 'asc', ['TYPE_ID' => Reminder::EVENT_NAME, 'SITE_ID' => $site]);
    while ($row = $rows->Fetch()) {
        $existing[] = $row;
    }
    if (count($existing) > 1) {
        throw new \RuntimeException('Multiple reminder templates already exist; review them before installation.');
    }
    if ($existing) {
        echo 'Reminder template already exists: #', $existing[0]['ID'], '. No changes; admin edits preserved.', PHP_EOL;
        exit(0);
    }

    $filter = ['TYPE_ID' => 'SALE_NEW_ORDER', 'SITE_ID' => $site, 'ACTIVE' => 'Y', 'BODY_TYPE' => 'html'];
    if (isset($options['source'])) {
        if (!ctype_digit((string)$options['source']) || (int)$options['source'] < 1) {
            throw new \RuntimeException('--source must be a positive template ID.');
        }
        $filter['ID'] = (int)$options['source'];
    }
    $sources = [];
    $rows = \CEventMessage::GetList('id', 'asc', $filter);
    while ($row = $rows->Fetch()) {
        if (str_contains((string)$row['EMAIL_TO'], '#EMAIL#') && !empty($row['SITE_TEMPLATE_ID'])) {
            $sources[] = $row;
        }
    }
    if (count($sources) !== 1) {
        throw new \RuntimeException('Select one active themed SALE_NEW_ORDER customer template with --source=ID.');
    }
    $source = $sources[0];
    $fields = Reminder::fields($source, $site);
    echo 'Source: #', $source['ID'], '; theme: ', $source['SITE_TEMPLATE_ID'], PHP_EOL;
    echo 'Will create mail event and customer template: ', Reminder::EVENT_NAME, PHP_EOL;
    if (!isset($options['apply'])) {
        echo 'Preview only. Use --apply to create the template. No mail is sent.', PHP_EOL;
        exit(0);
    }

    $connection->startTransaction();
    try {
        $types = [];
        $rows = \CEventType::GetList(['EVENT_NAME' => Reminder::EVENT_NAME]);
        while ($row = $rows->Fetch()) {
            $types[$row['LID']] = true;
        }
        foreach (array_unique(['ru', 'en', $source['LANGUAGE_ID'] ?: 'ru']) as $language) {
            if (!isset($types[$language])) {
                $id = \CEventType::Add([
                    'LID' => $language,
                    'EVENT_NAME' => Reminder::EVENT_NAME,
                    'NAME' => 'Pripomenutie úhrady zálohovej faktúry – Luxsol',
                    'DESCRIPTION' => Reminder::DESCRIPTION,
                    'SORT' => 160,
                ]);
                if (!$id) {
                    throw new \RuntimeException('Cannot create reminder mail event type.');
                }
            }
        }
        $message = new \CEventMessage();
        $id = $message->Add($fields);
        if (!$id) {
            throw new \RuntimeException('Cannot create reminder template: ' . $message->LAST_ERROR);
        }
        $connection->commitTransaction();
    } catch (\Throwable $error) {
        $connection->rollbackTransaction();
        throw $error;
    }
    echo 'Created reminder template #', $id, '. No mail queued; automatic sending is not configured.', PHP_EOL;
} catch (\Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
