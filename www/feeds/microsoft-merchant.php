<?php
/** Microsoft забирает /feeds/microsoft-merchant.xml по расписанию. */
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('BX_SECURITY_SESSION_VIRTUAL', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

// До отправки ответа целиком собираем XML: ошибки не должны давать HTTP 200
// с пустым каталогом или фрагментом HTML/PHP-предупреждения.
$feedBufferLevel = ob_get_level();
ob_start();
try {
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
    $feedXml = \Room\Tools\MicrosoftMerchantFeed::xml();
    $feedStatus = 200;
} catch (\Throwable $error) {
    error_log('Microsoft Merchant feed generation failed: ' . get_class($error));
    $feedXml = '<?xml version="1.0" encoding="UTF-8"?><error>Feed temporarily unavailable</error>';
    $feedStatus = 503;
}

while (ob_get_level() > $feedBufferLevel) {
    ob_end_clean();
}
http_response_code($feedStatus);
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
if ($feedStatus !== 200) {
    header('Cache-Control: no-store');
    header('Retry-After: 3600');
}
echo $feedXml;
die();
