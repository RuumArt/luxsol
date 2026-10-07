<?php

/*
 * Свои службы доставки с тарифом по весу.
 * Появляются в админке в списке обработчиков при создании службы доставки
 */
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'onSaleDeliveryHandlersClassNamesBuildList',
    ['\Room\Events\DeliveryHandlers', 'onBuildHandlersList']
);
