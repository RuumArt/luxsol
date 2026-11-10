<?php
/** Default is a read-only preview. --run sends real mail only when explicitly enabled. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
$options = getopt('', ['enable','disable','run','report']);
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
use Room\Tools\ProductionUpdates as Updates;
use Room\Tools\ProductionUpdatePolicy as Policy;
use Room\Tools\ProductionUpdateLogTable as Journal;

try {
    $config = Updates::config();
    if (empty($config['template_id']) || !Application::getConnection()->isTableExists(Journal::getTableName())) {
        throw new RuntimeException('Apply customer_notification_templates.php first.');
    }
    if (isset($options['enable']) || isset($options['disable'])) {
        $enable = isset($options['enable']) && !isset($options['disable']);
        if ($enable && empty($config['pending_statuses'])) {
            throw new RuntimeException('Pending order statuses are not configured.');
        }
        foreach ($config['pending_statuses'] ?? [] as $status) {
            if (in_array($status, Policy::FINISHED_STATUSES, true) || !\CSaleStatus::GetByID($status)) {
                throw new RuntimeException('Invalid pending status: ' . $status);
            }
        }
        $config['enabled'] = $enable;
        Option::set('main', Updates::OPTION, json_encode($config, JSON_THROW_ON_ERROR));
        echo 'Production updates: ', $enable ? 'enabled' : 'disabled', PHP_EOL;
    } elseif (isset($options['report'])) {
        $recent = Journal::getList(['order'=>['CREATED_AT'=>'DESC'], 'limit'=>20])->fetchAll();
        foreach ($recent as &$row) { $row['CREATED_AT'] = (string)$row['CREATED_AT']; } unset($row);
        echo json_encode(['config'=>$config,'recent'=>$recent], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
    } else {
        $result = Updates::run(isset($options['run']));
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), PHP_EOL;
        if (!empty($result['failed'])) { exit(1); }
    }
} catch (Throwable $error) { fwrite(STDERR, $error->getMessage() . PHP_EOL); exit(1); }
