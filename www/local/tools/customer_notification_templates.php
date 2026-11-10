<?php
/** Versioned, transactional migration. Default: preview. --apply: no mail or order saves. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
$options = getopt('', ['apply']);
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
use Room\Tools\CustomerNotificationTemplates as Templates;
use Room\Tools\ProductionUpdates;
use Room\Tools\ProductionUpdateLogTable;

$migration = 'luxsol_customer_notifications_20261110';
$connection = Application::getConnection();
$locked = false;
try {
    $locked = $connection->lock($migration, 0);
    if (!$locked) { throw new RuntimeException('Another notification migration is running.'); }
    if (Option::get('main', $migration, '') !== '') {
        echo "Already applied. Later edits in the Bitrix interface are preserved.\n";
        exit(0);
    }
    $source = [];
    $sources = CEventMessage::GetList('id', 'asc', ['TYPE_ID'=>'SALE_NEW_ORDER', 'SITE_ID'=>'s1', 'ACTIVE'=>'Y']);
    while ($row = $sources->Fetch()) {
        if (str_contains((string)$row['EMAIL_TO'], '#EMAIL#') && $row['BODY_TYPE'] === 'html' && !empty($row['SITE_TEMPLATE_ID'])) { $source[] = $row; }
    }
    if (count($source) !== 1) { throw new RuntimeException('Expected one active themed customer SALE_NEW_ORDER template.'); }
    $plans = [];
    foreach (Templates::definitions() as $event => $definition) {
        $rows = CEventMessage::GetList('id', 'asc', ['TYPE_ID'=>$event, 'SITE_ID'=>'s1']);
        $existing = [];
        while ($row = $rows->Fetch()) {
            if ($row['EMAIL_TO'] !== '#EMAIL#') { throw new RuntimeException('Review non-customer template for ' . $event); }
            $sites = [];
            $siteRows = CEventMessage::GetSite($row['ID']);
            while ($site = $siteRows->Fetch()) { $sites[] = $site['LID']; }
            if ($sites !== ['s1']) { throw new RuntimeException('Review shared-site template for ' . $event); }
            $existing[] = $row;
        }
        if (count($existing) > 1) { throw new RuntimeException('Multiple customer templates for ' . $event); }
        $plans[$event] = ['before'=>$existing[0] ?? null, 'after'=>Templates::fields($event, $source[0])];
        echo $event, ': ', $existing ? 'update #' . $existing[0]['ID'] : 'create',
            '; active=Y; theme=', Templates::THEME, '; subject=', $definition['subject'], PHP_EOL;
    }
    // Status notifications already depend on NOTIFY=Y in the Bitrix order engine.
    foreach (['RE','LP'] as $status) {
        $row = $connection->query("SELECT NOTIFY FROM b_sale_status WHERE ID='" . $status . "' AND TYPE='O'")->fetch();
        if (($row['NOTIFY'] ?? '') !== 'Y') { throw new RuntimeException('Enable status notification explicitly before migrating: ' . $status); }
    }
    if (!isset($options['apply'])) { echo "Preview only. --apply installs templates; production automation stays disabled on first install. No mail is sent.\n"; exit(0); }
    // DDL is outside the data transaction (MariaDB implicitly commits CREATE TABLE).
    if (!$connection->isTableExists(ProductionUpdateLogTable::getTableName())) { ProductionUpdateLogTable::getEntity()->createDbTable(); }
    $connection->startTransaction();
    try {
        $ids = [];
        $backups = [];
        foreach ($plans as $event => $plan) {
            $languages = array_unique(['ru', 'en', $source[0]['LANGUAGE_ID'] ?: 'ru']);
            foreach ($languages as $language) {
                if (!CEventType::GetList(['EVENT_NAME'=>$event, 'LID'=>$language])->Fetch()) {
                    if (!CEventType::Add(['LID'=>$language, 'EVENT_NAME'=>$event, 'NAME'=>Templates::definitions()[$event]['title'],
                        'DESCRIPTION'=>"#EMAIL# - E-mail zákazníka\n#ORDER_ID# / #ORDER_NUMBER# - Číslo objednávky\nHTML telo používa bezpečne escapované #HTML_ORDER_ID# / #HTML_ORDER_NUMBER#.", 'SORT'=>170])) {
                        throw new RuntimeException('Cannot create event type: ' . $event);
                    }
                }
            }
            $message = new CEventMessage();
            if ($plan['before']) {
                $id = (int)$plan['before']['ID'];
                $backups[$event] = ['id'=>$id, 'fields'=>array_intersect_key($plan['before'], $plan['after'])];
                $backups[$event]['fields']['LID'] = ['s1'];
                if (!$message->Update($id, $plan['after'])) { throw new RuntimeException($message->LAST_ERROR); }
            } else {
                $id = $message->Add($plan['after']);
                if (!$id) { throw new RuntimeException($message->LAST_ERROR); }
                $backups[$event] = ['id'=>(int)$id, 'created'=>true];
            }
            $ids[$event] = (int)$id;
        }
        $config = ProductionUpdates::config();
        $previousConfig = $config;
        $cod = \Bitrix\Sale\PaySystem\Manager::getList([
            'filter'=>['=ACTIVE'=>'Y', '=ACTION_FILE'=>'cashondelivery'], 'select'=>['ID'],
        ])->fetchAll();
        $config = array_replace(['enabled'=>false, 'pending_statuses'=>['N','OZ'],
            'cod_pay_system_ids'=>array_map('intval', array_column($cod, 'ID')), 'active_since'=>time()], $config);
        $config['template_id'] = $ids[Templates::PRODUCTION_EVENT];
        Option::set('main', ProductionUpdates::OPTION, json_encode($config, JSON_THROW_ON_ERROR));
        Option::set('main', $migration, json_encode(['applied_at'=>date(DATE_ATOM), 'templates'=>$backups,
            'previous_production_config'=>$previousConfig], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $connection->commitTransaction();
    } catch (Throwable $error) { $connection->rollbackTransaction(); throw $error; }
    echo "Applied. Backups retained in Bitrix option ", $migration, ". No mail queued or sent.\n";
} catch (Throwable $error) { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); }
finally { if ($locked) { $connection->unlock($migration); } }
