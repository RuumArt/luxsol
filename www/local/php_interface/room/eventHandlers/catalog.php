<?php

/*
 * Простановка веса основного товара во все его торговые предложения
 * Событие после обновления элемента ИБ товаров
 */
AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['\Room\Events\CatalogHandlers', 'onAfterIBlockElementUpdate']);

/*
 * Обратное направление: забор веса из основного товара в ТП
 * События создания и обновления элемента ИБ торговых предложений
 */
AddEventHandler('iblock', 'OnAfterIBlockElementAdd', ['\Room\Events\CatalogHandlers', 'onAfterIBlockElementAddOffer']);
AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['\Room\Events\CatalogHandlers', 'onAfterIBlockElementUpdateOffer']);

$eventManager = \Bitrix\Main\EventManager::getInstance();

/*
 * То же самое, но после сохранения карточки товара (b_catalog_product),
 * т.к. вес пишется отдельно от элемента инфоблока
 */
$eventManager->addEventHandler(
    'catalog',
    'Bitrix\Catalog\Model\Product::OnAfterUpdate',
    ['\Room\Events\CatalogHandlers', 'onCatalogProductUpdate']
);

/*
 * Создание нового ТП: на момент добавления элемента ИБ
 * записи в b_catalog_product ещё нет, поэтому ловим создание товара
 */
$eventManager->addEventHandler(
    'catalog',
    'Bitrix\Catalog\Model\Product::OnAfterAdd',
    ['\Room\Events\CatalogHandlers', 'onCatalogProductAdd']
);

unset($eventManager);
