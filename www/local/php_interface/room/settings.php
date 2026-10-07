<?php

// Local credentials override environment variables; this file is ignored by Git.
if (is_file(__DIR__ . '/settings.local.php')) {
    require_once __DIR__ . '/settings.local.php';
}


define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/local/AddMessage2Log.txt");

if (!defined('B24_LEAD_HOOK')) { define('B24_LEAD_HOOK', getenv('B24_LEAD_HOOK') ?: ''); }

define("PERSON_TYPE_ID", 1);

define('IBLOCK_ID__SLIDER', 3);
define('IBLOCK_ID__CATALOG', 1);
define('IBLOCK_ID__CATALOG_TP', 2);
define('IBLOCK_ID__MAIN_PAGE', 4);

define('IBLOCK_ID__SHOPS', 6);
define('IBLOCK_ID__COLLECTION', 7);
define('IBLOCK_ID__MUSIC', 8);

define('CATALOG__MAIN_PRICE_CODE', "BASE_PRICE");

define('SALE_PRICE_XML_ID', "MAIN_PRICE");

define('MAIN_PRICE_ID', 1);
define('SALE_PRICE_ID', 1);

define('CATALOG_ARTICUL_PROP_ID', 42);

define('DEFAULT_LOCATION_ID', '0000073738');
define('DEFAULT_DELIVERY_ID', 2);
define('DEFAULT_PAYMENT_ID', 2);

define('SDEK_DELIVERY_ID', 6);
define('SDEK_DELIVERY_PVZ_FIELD_ID', 4);

define('IBLOCK_TYPE__CATALOG', "CRM_PRODUCT_CATALOG");

define('CART_PATH', '/cart/');
define('CATALOG_PATH', '/catalog/');

define('HALF_SCREEEN_IMAGES', [
    "WIDTH" => 958,
    "HEIGHT" => 1080,
]);

define('PRODUCT_CARD_IMAGE', [
    "WIDTH" => 600,
    "HEIGHT" => 800,
]);

define('GALLERY_DATAIL_IMAGES', [
    "WIDTH" => 800,
    "HEIGHT" => 1200,
]);

// На каких страницах скрывать контейнер
const HIDE_CONTAINER = [
    '/'
];

// Перечень сайтов для сжатия html
const ROOM_MINIFY = ['s1'];

const GLOBAL_PREFILTER = [
    "!ID" => '48',
//    ">CATALOG_PRICE_".MAIN_PRICE_ID => '0',
//    ">PREVIEW_PICTURE" => '0',
];

// Доступ к smstools.sk: значения читаются из локальных настроек или окружения
if (!defined('SMS_API_KEY')) { define('SMS_API_KEY', getenv('SMS_API_KEY') ?: ''); }
define('SMS_SENDER_NAME', 'Luxsol');

// Секретный ключ reCAPTCHA: проверяет формы обратной связи на спам
if (!defined('RECAPTCHA_SECRET')) { define('RECAPTCHA_SECRET', getenv('RECAPTCHA_SECRET') ?: ''); }

// Пароль API Packeta: создание отправлений
if (!defined('PACKETA_API_PASSWORD')) { define('PACKETA_API_PASSWORD', getenv('PACKETA_API_PASSWORD') ?: ''); }

// Бот, который пишет о новых заказах в канал «Luxsol notify»
if (!defined('TELEGRAM_BOT_TOKEN')) { define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: ''); }

if (!defined('TELEGRAM_CHAT_ID')) { define('TELEGRAM_CHAT_ID', getenv('TELEGRAM_CHAT_ID') ?: ''); }

// Legacy CAPTCHA and DPD settings.
if (!defined('RE_SITE_KEY')) { define('RE_SITE_KEY', getenv('RE_SITE_KEY') ?: ''); }
if (!defined('RE_SEC_KEY')) { define('RE_SEC_KEY', getenv('RE_SEC_KEY') ?: ''); }
if (!defined('DPD_CLIENT_KEY')) { define('DPD_CLIENT_KEY', getenv('DPD_CLIENT_KEY') ?: ''); }
if (!defined('DPD_EMAIL')) { define('DPD_EMAIL', getenv('DPD_EMAIL') ?: ''); }
if (!defined('DPD_DELIS_ID')) { define('DPD_DELIS_ID', getenv('DPD_DELIS_ID') ?: ''); }
if (!defined('DPD_PICKUP_ADDRESS_ID')) { define('DPD_PICKUP_ADDRESS_ID', (int)(getenv('DPD_PICKUP_ADDRESS_ID') ?: 0)); }
