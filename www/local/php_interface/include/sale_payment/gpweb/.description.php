<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
    'NAME' => Loc::getMessage('SALE_HPS_GPWEB_NAME'),
    'SORT' => 500,
    'IS_AVAILABLE' => \Bitrix\Main\Loader::includeModule('sale'),
    'CODES' => array(
        'GPWEB_MERCHANT_NUMBER' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_MERCHANT_NUMBER'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_MERCHANT_NUMBER_DESC'),
            'VALUE' => '',
            'TYPE' => ''
        ),
        'GPWEB_PRIVATE_KEY_PATH' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_PRIVATE_KEY_PATH'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_PRIVATE_KEY_PATH_DESC'),
            'VALUE' => '',
            'TYPE' => ''
        ),
        'GPWEB_PRIVATE_KEY_PASSWORD' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_PRIVATE_KEY_PASSWORD'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_PRIVATE_KEY_PASSWORD_DESC'),
            'VALUE' => '',
            'TYPE' => 'PASSWORD'
        ),
        'GPWEB_PUBLIC_KEY_PATH' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_PUBLIC_KEY_PATH'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_PUBLIC_KEY_PATH_DESC'),
            'VALUE' => '',
            'TYPE' => ''
        ),
        'GPWEB_GATEWAY_URL' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_GATEWAY_URL'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_GATEWAY_URL_DESC'),
            'VALUE' => 'https://3dsecure.gpwebpay.com/pgw/order.do',
            'TYPE' => ''
        ),
        'GPWEB_CURRENCY' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_CURRENCY'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_CURRENCY_DESC'),
            'VALUE' => '978',
            'TYPE' => ''
        ),
        'PS_CHANGE_STATUS_PAY' => array(
            'NAME' => Loc::getMessage('SALE_HPS_GPWEB_CHANGE_STATUS_PAY'),
            'DESCR' => Loc::getMessage('SALE_HPS_GPWEB_CHANGE_STATUS_PAY_DESC'),
            'VALUE' => 'Y',
            'TYPE' => 'CHECKBOX'
        ),
    )
);