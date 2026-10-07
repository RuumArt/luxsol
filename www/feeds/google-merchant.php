<?php
/**
 * Фид товаров для Google Merchant Center
 *
 * Merchant забирает его по расписанию раз в сутки. Содержимое собирает
 * Room\Tools\MerchantFeed, проверка из консоли - local/tools/merchant_feed.php
 */

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('BX_SECURITY_SESSION_VIRTUAL', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/xml; charset=utf-8');
// Фид нужен Merchant, а не поисковой выдаче
header('X-Robots-Tag: noindex, nofollow');

echo \Room\Tools\MerchantFeed::xml();

die();
