<?php

namespace Room\Events;

use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;

class CatalogHandlers
{
    // Идентификатор ИБ товаров
    const IB_CATALOG = IBLOCK_ID__CATALOG;

    // Идентификатор ИБ торговых предложений
    const IB_CATALOG_TP = IBLOCK_ID__CATALOG_TP;

    // Отладочный лог в AddMessage2Log
    const DEBUG_LOG = false;

    /** @var array Защита от рекурсии: обновление ТП тоже поднимает события каталога */
    protected static $inProgress = [];

    /** @var array ТП, которым нужно проставить вес в конце хита */
    protected static $deferredOffers = [];

    /** @var bool Отложенная обработка уже поставлена в очередь */
    protected static $deferredRegistered = false;

    /** @var int|null Идентификатор свойства привязки ТП к товару */
    protected static $skuPropertyId = null;

    /**
     * Обработчик обновления элемента ИБ товаров
     *
     * @param array $arFields
     * @return void
     */
    public static function onAfterIBlockElementUpdate(&$arFields): void
    {
        if ((int) $arFields['IBLOCK_ID'] !== self::IB_CATALOG) {
            return;
        }

        if (array_key_exists('RESULT', $arFields) && empty($arFields['RESULT'])) {
            return;
        }

        self::syncWeightToOffers((int) $arFields['ID']);
    }

    /**
     * Обработчик обновления товара (b_catalog_product)
     * Ловит момент, когда вес реально сохранился в основном товаре
     *
     * @param Event $event
     * @return void
     */
    public static function onCatalogProductUpdate(Event $event): void
    {
        $id = self::extractEventId($event);

        if ($id <= 0) {
            return;
        }

        $iblockId = self::getElementIblockId($id);

        if ($iblockId === self::IB_CATALOG_TP) {
            // Карточка товара может перезаписать вес ТП уже после нашей простановки,
            // поэтому проверяем ТП ещё раз в конце хита
            self::deferOffer($id);

            return;
        }

        if ($iblockId !== self::IB_CATALOG) {
            return;
        }

        $fields = $event->getParameter('fields');

        // Вес не менялся - синхронизировать нечего
        if (is_array($fields) && !array_key_exists('WEIGHT', $fields)) {
            return;
        }

        self::syncWeightToOffers($id);
    }

    /**
     * Обработчик создания товара (b_catalog_product)
     * Нужен для новых ТП: на момент добавления элемента ИБ
     * записи в b_catalog_product ещё нет, писать вес некуда
     *
     * @param Event $event
     * @return void
     */
    public static function onCatalogProductAdd(Event $event): void
    {
        $id = self::extractEventId($event);

        if ($id <= 0 || self::getElementIblockId($id) !== self::IB_CATALOG_TP) {
            return;
        }

        self::deferOffer($id);
    }

    /**
     * Обработчик создания элемента ИБ торговых предложений
     *
     * @param array $arFields
     * @return void
     */
    public static function onAfterIBlockElementAddOffer(&$arFields): void
    {
        if ((int) $arFields['IBLOCK_ID'] !== self::IB_CATALOG_TP) {
            return;
        }

        if (empty($arFields['ID'])) {
            return;
        }

        self::deferOffer((int) $arFields['ID']);
    }

    /**
     * Обработчик обновления элемента ИБ торговых предложений
     * Ловит перепривязку ТП к другому товару и импорт
     *
     * @param array $arFields
     * @return void
     */
    public static function onAfterIBlockElementUpdateOffer(&$arFields): void
    {
        if ((int) $arFields['IBLOCK_ID'] !== self::IB_CATALOG_TP) {
            return;
        }

        if (array_key_exists('RESULT', $arFields) && empty($arFields['RESULT'])) {
            return;
        }

        self::deferOffer((int) $arFields['ID']);
    }

    /**
     * Обработка отложенных ТП в самом конце хита
     * Вызывается из фонового задания Bitrix
     *
     * @return void
     */
    public static function processDeferredOffers(): void
    {
        $offerIds = self::$deferredOffers;

        self::$deferredOffers = [];
        self::$deferredRegistered = false;

        foreach ($offerIds as $offerId) {
            self::syncWeightFromParent((int) $offerId);
        }
    }

    /**
     * Проставляет в ТП вес его основного товара
     *
     * @param int $offerId
     * @return bool Обновлён ли вес
     */
    public static function syncWeightFromParent(int $offerId): bool
    {
        if ($offerId <= 0 || !Loader::includeModule('catalog')) {
            return false;
        }

        // Уже обрабатываем это ТП в текущем хите
        if (!empty(self::$inProgress['offer_' . $offerId])) {
            return false;
        }

        $parentId = self::resolveParentId($offerId);

        if ($parentId <= 0) {
            self::debug('ТП #' . $offerId . ': не найден основной товар');

            return false;
        }

        $weight = self::getProductWeight($parentId);

        // Вес в основном товаре не заполнен - ничего не делаем
        if ($weight <= 0) {
            self::debug('Товар #' . $parentId . ': вес не заполнен');

            return false;
        }

        if (abs(self::getProductWeight($offerId) - $weight) < 0.0001) {
            return false;
        }

        self::$inProgress['offer_' . $offerId] = true;

        try {
            $result = Product::update($offerId, [
                'fields' => ['WEIGHT' => $weight],
            ]);

            if (!$result->isSuccess()) {
                AddMessage2Log(
                    'Не удалось проставить вес в ТП #' . $offerId
                    . ' (товар #' . $parentId . '): '
                    . implode('; ', $result->getErrorMessages())
                );

                return false;
            }
        } finally {
            unset(self::$inProgress['offer_' . $offerId]);
        }

        self::debug('ТП #' . $offerId . ': проставлен вес ' . $weight . ' из товара #' . $parentId);

        return true;
    }

    /**
     * Разовый обход всех товаров каталога с простановкой веса в ТП
     * Запускается вручную (CLI / php_command_line.php), в событиях не участвует
     *
     * @param callable|null $logger Колбэк для вывода прогресса: function (string $message): void
     * @return array ['products' => обработано товаров, 'offers' => обновлено ТП]
     */
    public static function syncAllProducts(?callable $logger = null): array
    {
        $result = ['products' => 0, 'offers' => 0];

        if (!Loader::includeModule('catalog')) {
            return $result;
        }

        // Берём только товары каталога с заполненным весом
        $rsProducts = ProductTable::getList([
            'select' => ['ID'],
            'filter' => [
                '=IBLOCK_ELEMENT.IBLOCK_ID' => self::IB_CATALOG,
                '>WEIGHT' => 0,
            ],
            'order' => ['ID' => 'ASC'],
        ]);

        $productIds = [];

        while ($product = $rsProducts->fetch()) {
            $productIds[] = (int) $product['ID'];
        }

        if (empty($productIds)) {
            return $result;
        }

        if ($logger) {
            $logger('Товаров с заполненным весом: ' . count($productIds));
        }

        foreach ($productIds as $productId) {
            $updated = self::syncWeightToOffers($productId);

            $result['products']++;
            $result['offers'] += $updated;

            if ($logger && $updated > 0) {
                $logger('Товар #' . $productId . ': обновлено ТП - ' . $updated);
            }
        }

        if ($logger) {
            $logger(
                'Готово. Обработано товаров: ' . $result['products']
                . ', обновлено ТП: ' . $result['offers']
            );
        }

        return $result;
    }

    /**
     * Проставляет вес основного товара во все его торговые предложения
     * Метод публичный - можно вызвать руками для массовой простановки
     *
     * @param int $productId
     * @return int Количество обновлённых ТП
     */
    public static function syncWeightToOffers(int $productId): int
    {
        if ($productId <= 0 || !Loader::includeModule('catalog')) {
            return 0;
        }

        // Уже обрабатываем этот товар в текущем хите
        if (!empty(self::$inProgress[$productId])) {
            return 0;
        }

        $weight = self::getProductWeight($productId);

        // Вес в основном товаре не заполнен - ничего не делаем
        if ($weight <= 0) {
            return 0;
        }

        $offerIds = self::getOfferIds($productId);

        if (empty($offerIds)) {
            return 0;
        }

        self::$inProgress[$productId] = true;

        $updated = 0;

        try {
            $rsOffers = ProductTable::getList([
                'select' => ['ID', 'WEIGHT'],
                'filter' => ['@ID' => $offerIds],
            ]);

            while ($offer = $rsOffers->fetch()) {
                // Вес уже совпадает - лишний запрос не делаем
                if (abs((float) $offer['WEIGHT'] - $weight) < 0.0001) {
                    continue;
                }

                $result = Product::update((int) $offer['ID'], [
                    'fields' => ['WEIGHT' => $weight],
                ]);

                if ($result->isSuccess()) {
                    $updated++;
                } else {
                    AddMessage2Log(
                        'Не удалось проставить вес в ТП #' . $offer['ID']
                        . ' (товар #' . $productId . '): '
                        . implode('; ', $result->getErrorMessages())
                    );
                }
            }
        } finally {
            unset(self::$inProgress[$productId]);
        }

        return $updated;
    }

    /**
     * Откладывает обработку ТП на конец хита
     *
     * @param int $offerId
     * @return void
     */
    protected static function deferOffer(int $offerId): void
    {
        if ($offerId <= 0) {
            return;
        }

        self::$deferredOffers[$offerId] = $offerId;

        if (self::$deferredRegistered) {
            return;
        }

        self::$deferredRegistered = true;

        Application::getInstance()->addBackgroundJob([__CLASS__, 'processDeferredOffers']);
    }

    /**
     * Идентификатор основного товара для ТП
     * Читаем привязку напрямую из БД: CCatalogSKU::GetProductInfo() кеширует
     * результат статически, и для только что созданного ТП там может лежать false
     *
     * @param int $offerId
     * @return int
     */
    protected static function resolveParentId(int $offerId): int
    {
        $skuPropertyId = self::getSkuPropertyId();

        if ($skuPropertyId <= 0) {
            return 0;
        }

        $rsValues = \CIBlockElement::GetPropertyValues(
            self::IB_CATALOG_TP,
            ['ID' => $offerId],
            false,
            ['ID' => $skuPropertyId]
        );

        $values = $rsValues->Fetch();

        $parentId = (int) ($values[$skuPropertyId] ?? 0);

        if ($parentId <= 0) {
            return 0;
        }

        // Привязка может вести в другой инфоблок - проверяем
        return self::getElementIblockId($parentId) === self::IB_CATALOG ? $parentId : 0;
    }

    /**
     * Идентификатор свойства привязки ТП к основному товару
     *
     * @return int
     */
    protected static function getSkuPropertyId(): int
    {
        if (self::$skuPropertyId === null) {
            $skuInfo = \CCatalogSKU::GetInfoByOfferIBlock(self::IB_CATALOG_TP);

            self::$skuPropertyId = empty($skuInfo['SKU_PROPERTY_ID'])
                ? 0
                : (int) $skuInfo['SKU_PROPERTY_ID'];
        }

        return self::$skuPropertyId;
    }

    /**
     * Идентификатор товара из события модели каталога
     *
     * @param Event $event
     * @return int
     */
    protected static function extractEventId(Event $event): int
    {
        $id = $event->getParameter('id');

        if (is_array($id)) {
            $id = $id['ID'] ?? reset($id);
        }

        return (int) $id;
    }

    /**
     * Вес основного товара
     *
     * @param int $productId
     * @return float
     */
    protected static function getProductWeight(int $productId): float
    {
        $row = ProductTable::getRow([
            'select' => ['ID', 'WEIGHT'],
            'filter' => ['=ID' => $productId],
        ]);

        return $row ? (float) $row['WEIGHT'] : 0.0;
    }

    /**
     * Идентификаторы торговых предложений товара
     *
     * @param int $productId
     * @return array
     */
    protected static function getOfferIds(int $productId): array
    {
        $offers = \CCatalogSKU::getOffersList(
            [$productId],
            self::IB_CATALOG,
            ['CHECK_PERMISSIONS' => 'N'],
            ['ID']
        );

        if (empty($offers[$productId])) {
            return [];
        }

        return array_map('intval', array_keys($offers[$productId]));
    }

    /**
     * Инфоблок элемента
     *
     * @param int $elementId
     * @return int
     */
    protected static function getElementIblockId(int $elementId): int
    {
        $row = \Bitrix\Iblock\ElementTable::getRow([
            'select' => ['ID', 'IBLOCK_ID'],
            'filter' => ['=ID' => $elementId],
        ]);

        return $row ? (int) $row['IBLOCK_ID'] : 0;
    }

    /**
     * Отладочный лог
     *
     * @param string $message
     * @return void
     */
    protected static function debug(string $message): void
    {
        if (self::DEBUG_LOG) {
            AddMessage2Log('[weight] ' . $message);
        }
    }
}
