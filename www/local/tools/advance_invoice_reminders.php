<?php
/**
 * Advance invoice reminders: --install, --enable, --disable, --run, --report.
 * No argument: read-only preview. --install updates only this mail template,
 * backs it up in Bitrix options, creates the dispatch journal, and keeps delivery
 * disabled on first installation. Cron runs --run hourly as the site's PHP user.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
$options = getopt('', ['install','enable','disable','run','report']);
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = 'luxsol.sk';
$_SERVER['REQUEST_URI'] = '/';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
\Bitrix\Main\Loader::includeModule('sale');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Room\Tools\AdvanceInvoiceReminders as Reminders;
use Room\Tools\AdvanceInvoiceReminderTemplate as Template;
use Room\Tools\AdvanceInvoiceReminderLogTable as Journal;

try {
    if (isset($options['install'])) {
        $templates = \Bitrix\Main\Mail\Internal\EventMessageTable::getList([
            'filter'=>['=EVENT_NAME'=>Template::EVENT_NAME],
        ])->fetchAll();
        if (count($templates) !== 1 || $templates[0]['EMAIL_TO'] !== '#EMAIL#') {
            throw new \RuntimeException('Expected one existing customer reminder template. Run mail_advance_invoice_reminder.php --apply first.');
        }
        $systems = \Bitrix\Sale\PaySystem\Manager::getList([
            'filter'=>['=ACTION_FILE'=>'bill', '=ACTIVE'=>'Y'], 'select'=>['ID'],
        ])->fetchAll();
        if (count($systems) !== 1) {
            throw new \RuntimeException('Expected one active bank-transfer payment system.');
        }
        $template = $templates[0];
        $fields = Template::fields($template, 's1');
        $backupKey = 'luxsol_invoice_reminder_template_v1_backup';
        if (Option::get('main', $backupKey, '') === '') {
            Option::set('main', $backupKey, json_encode([
                'id'=>$template['ID'], 'subject'=>$template['SUBJECT'], 'message'=>$template['MESSAGE'],
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }
        $connection = Application::getConnection();
        if (!$connection->isTableExists(Journal::getTableName())) {
            Journal::getEntity()->createDbTable();
        }
        $message = new \CEventMessage();
        if (!$message->Update($template['ID'], ['MESSAGE'=>$fields['MESSAGE'], 'SUBJECT'=>$fields['SUBJECT']])) {
            throw new \RuntimeException('Cannot update reminder template: ' . $message->LAST_ERROR);
        }
        if (!\CEventType::Update(['EVENT_NAME'=>Template::EVENT_NAME], ['DESCRIPTION'=>Template::DESCRIPTION])) {
            throw new \RuntimeException('Cannot update reminder event description.');
        }
        $old = Reminders::config();
        Option::set('main', Reminders::OPTION, json_encode([
            'enabled'=>!empty($old['enabled']), 'template_id'=>(int)$template['ID'],
            'pay_system_id'=>(int)$systems[0]['ID'],
        ], JSON_THROW_ON_ERROR));
        echo 'Installed; template #', $template['ID'], '. Delivery enabled: ', !empty($old['enabled']) ? 'yes' : 'no', PHP_EOL;
    } elseif (isset($options['enable']) || isset($options['disable'])) {
        $config = Reminders::config();
        if (!$config || !Application::getConnection()->isTableExists(Journal::getTableName())) {
            throw new \RuntimeException('Install reminders before changing delivery state.');
        }
        $config['enabled'] = isset($options['enable']) && !isset($options['disable']);
        Option::set('main', Reminders::OPTION, json_encode($config, JSON_THROW_ON_ERROR));
        echo 'Automatic invoice reminders: ', $config['enabled'] ? 'enabled' : 'disabled', PHP_EOL;
    } elseif (isset($options['report'])) {
        $recent = Journal::getList([
            'order'=>['CREATED_AT'=>'DESC'], 'limit'=>20,
        ])->fetchAll();
        foreach ($recent as &$entry) { $entry['CREATED_AT'] = (string)$entry['CREATED_AT']; }
        unset($entry);
        echo json_encode(['config'=>Reminders::config(), 'recent'=>$recent], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
    } else {
        $result = Reminders::run(isset($options['run']));
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), PHP_EOL;
        if (!empty($result['failed'])) { exit(1); }
    }
} catch (\Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
