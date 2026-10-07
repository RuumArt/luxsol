<?php

namespace Room\Services;

use Bitrix\Sale;
use Bitrix\Main\Context;
use Bitrix\Currency\CurrencyManager;
use Room\Tools\Format;
use CCatalogSku;
use CIBlockElement;
use CCatalogProduct;
use CCatalogDiscount;
use Exception;

/**
 * Класс для работы с корзиной
 */
class CartService
{
    const IBLOCK_ID_PRODUCTS = 1;

    /**
     * Получение ID базового товара (для SKU)
     *
     * @param int $productId ID товара или SKU
     * @return int ID базового товара
     */
    public static function getBaseProductId(int $productId): int
    {
        $mxResult = CCatalogSku::GetProductInfo($productId);
        if (is_array($mxResult)) {
            return (int)$mxResult['ID'];
        }
        return $productId;
    }

    /**
     * Получение информации о товаре
     *
     * @param int $productId ID товара
     * @return array Информация о товаре
     */
    public static function getProductInfo(int $productId): array
    {
        $baseProductId = self::getBaseProductId($productId);

        $arFilter = [
            "IBLOCK_ID" => self::IBLOCK_ID_PRODUCTS,
            "ACTIVE" => "Y",
            "ID" => $baseProductId
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                "ID",
                "NAME",
                "IBLOCK_ID",
                "DETAIL_PAGE_URL",
                "PROPERTY_S_POKRAS",
                "PROPERTY_ARTICLE",
                "PROPERTY_PRICE_WHITE",
                "PROPERTY_PRICE_POKRAS",
                "PROPERTY_27", // Вес
                "PROPERTY_2",  // Длина
                "PROPERTY_3",  // Высота
                "PROPERTY_4"   // Ширина
            ]
        );

        $productInfo = [];
        if ($arFields = $res->GetNext()) {
            $productInfo = [
                'id' => (int)$arFields['ID'],
                'name' => $arFields['NAME'],
                'article' => $arFields['PROPERTY_ARTICLE_VALUE'] ?? '',
                'weight' => $arFields['PROPERTY_27_VALUE'] ?? 0,
                'length' => $arFields['PROPERTY_2_VALUE'] ?? 0,
                'height' => $arFields['PROPERTY_3_VALUE'] ?? 0,
                'width' => $arFields['PROPERTY_4_VALUE'] ?? 0,
                'paintSquareThreshold' => $arFields['PROPERTY_S_POKRAS_VALUE'] ?? 0,
                'paintGridPrice' => $arFields['PROPERTY_PRICE_POKRAS_VALUE'] ?? 0,
                'priceWhite' => $arFields['PROPERTY_PRICE_WHITE_VALUE'] ?? 0
            ];
        }

        return $productInfo;
    }

    /**
     * Удаление товара из корзины, если он уже там есть
     *
     * @param int $productId ID товара
     * @return bool Успешность операции
     */
    public static function removeFromBasketIfExists(int $productId): bool
    {
        $basket = Sale\Basket::loadItemsForFUser(
            Sale\Fuser::getId(),
            Context::getCurrent()->getSite()
        );

        $items = $basket->getBasketItems();
        foreach ($items as $item) {
            // У BasketItem нет getOrderId(), привязка к заказу читается из поля
            if ($item->getProductId() == $productId && !$item->getField('ORDER_ID')) {
                $item->delete();
                $basket->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Добавление товара с размерами в корзину
     *
     * @param int $productId ID товара
     * @param array $priceData Данные расчета цены
     * @param array $properties Свойства товара
     * @return bool Успешность операции
     * @throws Exception
     */
    public static function addProductWithSizes(int $productId, array $priceData, array $properties): bool
    {
        $productInfo = self::getProductInfo($productId);

        if (empty($productInfo)) {
            throw new Exception("Товар не найден");
        }

        $basket = Sale\Basket::loadItemsForFUser(
            Sale\Fuser::getId(),
            Context::getCurrent()->getSite()
        );

        $item = $basket->getExistsItem('catalog', $productId);
        if ($item) {
            $item->setField('QUANTITY', 1);
        } else {
            $item = $basket->createItem('catalog', $productId);
            $item->setFields([
                'QUANTITY' => 1,
                'CURRENCY' => CurrencyManager::getBaseCurrency(),
                'LID' => Context::getCurrent()->getSite(),
                'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProviderCustom',
                'NAME' => $productInfo['name']
            ]);

            $propertyCollection = $item->getPropertyCollection();
            $propertyCollection->setProperty(self::buildSizeProperties($priceData, $properties));
        }

        $item->save();
        $basket->save();

        self::storeSizesInSession($productId, $priceData);

        return true;
    }

    /**
     * Обновление размеров и цены у товара, который уже лежит в корзине
     *
     * @param int $productId ID товара
     * @param array $priceData Данные расчета цены
     * @param array $properties Свойства товара
     * @param int|null $basketItemId ID позиции корзины, если известен
     * @return bool Найдена ли позиция и обновлена ли она
     */
    public static function updateProductSizes(
        int $productId,
        array $priceData,
        array $properties,
        ?int $basketItemId = null
    ): bool {
        $basket = Sale\Basket::loadItemsForFUser(
            Sale\Fuser::getId(),
            Context::getCurrent()->getSite()
        );

        $item = $basketItemId ? $basket->getItemById($basketItemId) : null;

        if (!$item) {
            $item = $basket->getExistsItem('catalog', $productId);
        }

        if (!$item) {
            return false;
        }

        $item->setFields([
            'QUANTITY' => 1,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProviderCustom',
        ]);

        // setProperty перезаписывает весь набор: свойства, которых нет
        // в переданном списке, удаляются. Поэтому передаём его целиком
        $propertyCollection = $item->getPropertyCollection();
        $propertyCollection->setProperty(self::buildSizeProperties($priceData, $properties));

        $item->save();
        $basket->save();

        self::storeSizesInSession($productId, $priceData);

        return true;
    }

    /**
     * Собирает итоговый набор свойств позиции корзины
     *
     * @param array $priceData Данные расчета цены
     * @param array $properties Свойства товара
     * @return array
     */
    private static function buildSizeProperties(array $priceData, array $properties): array
    {
        // Размеры и площадь показываются покупателю - приводим к локальному формату.
        // Делаем это здесь, а не в вызывающем коде, чтобы формат был единым
        // независимо от того, кто пишет товар в корзину
        $properties = self::formatDisplayProperties($properties);

        // Цена участвует в расчётах - остаётся числом с точкой
        $properties[] = [
            "NAME" => "Рассчитанная цена",
            "VALUE" => $priceData['price'],
            "CODE" => "CUSTOM_PRICE_PROP"
        ];

        return $properties;
    }

    /**
     * Сохранение размеров в сессию
     *
     * @param int $productId ID товара
     * @param array $priceData Данные расчета цены
     * @return void
     */
    private static function storeSizesInSession(int $productId, array $priceData): void
    {
        if (empty($priceData['sizesArr'])) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['cart_sizes'][$productId] = $priceData['sizesArr'];
    }

    /**
     * Приводит свойства корзины, которые видит покупатель, к локальному формату:
     * размеры - "2,5 × 3,5 m × 1 ks., 1,8 × 2,6 m × 2 ks.", числа - через запятую
     *
     * Свойства с ценами не трогаем: их значения читаются как числа
     *
     * @param array $properties Свойства для корзины
     * @return array
     */
    private static function formatDisplayProperties(array $properties): array
    {
        // Свойства с числовыми значениями: высота, ширина, площадь
        $numericCodes = ['HEIGHT', 'WIDTH', 'SQUARE'];

        foreach ($properties as &$property) {
            $code = $property['CODE'] ?? '';

            if ($code === 'SIZES_STR') {
                $property['VALUE'] = Format::sizes((string) $property['VALUE']);
            } elseif (in_array($code, $numericCodes, true) && $property['VALUE'] !== '') {
                $property['VALUE'] = Format::number($property['VALUE']);
            }
        }

        unset($property);

        return $properties;
    }

    /**
     * Добавление простого товара в корзину
     *
     * @param int $productId ID товара
     * @param int $quantity Количество
     * @param array $properties Свойства товара
     * @return array Данные о цене
     */
    public static function addSimpleProduct(int $productId, int $quantity, array $properties): array
    {
        global $USER;

        $priceResult = CCatalogProduct::GetOptimalPrice(
            $productId,
            $quantity,
            $USER->GetUserGroupArray(),
            'N'
        );

        $nvp = $priceResult["PRICE"]["PRICE"] ?? 0;
        $price = $priceResult["RESULT_PRICE"]["DISCOUNT_PRICE"] ?? 0;

        $props = [];
        foreach ($properties as $prop) {
            if (!empty($prop['VALUE'])) {
                $props[] = $prop;
            }
        }

        Add2BasketByProductID($productId, $quantity, [], $props);

        return [
            'price' => $price,
            'priceNovat' => $nvp
        ];
    }

    /**
     * Применение купона
     *
     * @param string $coupon Код купона
     * @return bool Успешность операции
     */
    public static function applyCoupon(string $coupon): bool
    {
        if (empty($coupon)) {
            return false;
        }

        CCatalogDiscount::SetCoupon($coupon);
        return true;
    }

    /**
     * Обновление корзины и получение количества товаров
     *
     * @return array Данные корзины
     */
    public static function refreshBasket(): array
    {
        $basket = Sale\Basket::loadItemsForFUser(
            Sale\Fuser::getId(),
            Context::getCurrent()->getSite()
        );

        $basket->refresh();
        $basket->save();

        $count = array_sum($basket->getQuantityList());

        return [
            'count' => $count,
            'basket' => $basket
        ];
    }
}

