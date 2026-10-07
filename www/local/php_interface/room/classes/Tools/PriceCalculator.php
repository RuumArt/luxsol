<?php

namespace Room\Tools;

use CUser;
use CIBlockElement;
use CIBlockPriceTools;
use CCatalogProduct;
use CPrice;

/**
 * Класс для расчета цен товаров с учетом размеров и цвета
 */
class PriceCalculator
{
    // Коэффициенты для расчета цены в зависимости от площади
    const SQUARE_COEFFICIENT_0_5 = 2.0;    // 0-5 м²
    const SQUARE_COEFFICIENT_5_10 = 1.35;  // 5-10 м²
    const SQUARE_COEFFICIENT_10_20 = 1.2;  // 10-20 м²
    const SQUARE_COEFFICIENT_20_PLUS = 1.0; // 20+ м²

    // ID цвета "Белая" (Biela)
    const COLOR_WHITE_ID = 307;
    const COLOR_WHITE_NAME = 'Biela';

    // IBLOCK_ID для товаров
    const IBLOCK_ID_PRODUCTS = 1;
    // IBLOCK_ID для цветов
    const IBLOCK_ID_COLORS = 5;

    /**
     * Расчет цены для товара с размерами
     *
     * @param array $params Параметры расчета
     * @return array Результат расчета
     */
    public static function calculatePriceWithSizes(array $params): array
    {
        $sizes = $params['sizes'] ?? [];
        $productId = $params['productId'] ?? 0;
        $baseProductId = $params['baseProductId'] ?? $productId;
        $color = $params['color'] ?? self::COLOR_WHITE_NAME;
        $basePrice = $params['basePrice'] ?? 0;
        $productProperties = $params['productProperties'] ?? [];

        // Фильтрация размеров
        $sizesArr = self::filterSizes($sizes);

        // Получение цен для белого и цветного товара
        $prices = self::getColorPrices($baseProductId, $color);
        $priceWhite = $prices['white'];
        $priceColor = $prices['color'];
        $paintPrice = $priceColor - $priceWhite;

        // Расчет общей площади для проверки
        $totalSquare = self::calculateTotalSquare($sizes);

        // Расчет цен для каждого размера
        $result = self::calculatePricesForSizes(
            $sizes,
            $priceWhite,
            $priceColor,
            $paintPrice,
            $totalSquare,
            $productProperties,
            $baseProductId,
            $basePrice
        );

        return [
            'price' => round($result['totalPrice'], 2),
            'priceNovat' => round($result['totalPriceNovat'], 2),
            'totalSquare' => round($result['totalSquare'], 1),
            'sizesStr' => $result['sizesStr'],
            'height' => $result['height'],
            'width' => $result['width'],
            'countSum' => $result['countSum'],
            'sizesArr' => $sizesArr
        ];
    }

    /**
     * Фильтрация размеров
     *
     * @param array $sizes Массив размеров
     * @return array Отфильтрованные размеры
     */
    private static function filterSizes(array $sizes): array
    {
        $sizesArr = [];
        foreach ($sizes as $key => $size) {
            if (!empty($size['width']) && !empty($size['height']) && !empty($size['count'])) {
                $sizesArr[$key] = $size;
            }
        }
        return $sizesArr;
    }

    /**
     * Получение цен для белого и цветного товара
     *
     * @param int $productId ID товара
     * @param string $colorName Название цвета
     * @return array Массив с ценами
     */
    private static function getColorPrices(int $productId, string $colorName): array
    {
        global $USER;
        
        if (!isset($USER) || !is_object($USER)) {
            $USER = $GLOBALS['USER'] ?? null;
        }

        $priceWhite = 0;
        $priceColor = 0;

        // Получение офферов товара
        $offers = CIBlockPriceTools::GetOffersArray([
            'IBLOCK_ID' => self::IBLOCK_ID_PRODUCTS,
            'HIDE_NOT_AVAILABLE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y'
        ], [$productId], null, ['ID', 'PROPERTY_COLOR'], null, null, null, null, null, null, null);

        // Получение ID цвета
        $colorId = self::getColorId($colorName);

        // Поиск цен для белого и цветного товара
        $userGroups = [];
        if ($USER && method_exists($USER, 'GetUserGroupArray')) {
            $userGroups = $USER->GetUserGroupArray();
        }

        foreach ($offers as $offer) {
            if ($offer['PROPERTY_COLOR_VALUE'] == self::COLOR_WHITE_ID) {
                $priceResult = CCatalogProduct::GetOptimalPrice(
                    $offer['ID'],
                    1,
                    $userGroups,
                    'N'
                );
                $priceWhite = $priceResult['PRICE']['PRICE'] ?? 0;
            } elseif ($offer['PROPERTY_COLOR_VALUE'] == $colorId) {
                $priceResult = CCatalogProduct::GetOptimalPrice(
                    $offer['ID'],
                    1,
                    $userGroups,
                    'N'
                );
                $priceColor = $priceResult['PRICE']['PRICE'] ?? 0;
            }
        }

        return [
            'white' => $priceWhite,
            'color' => $priceColor
        ];
    }

    /**
     * Получение ID цвета по названию
     *
     * @param string $colorName Название цвета
     * @return int ID цвета
     */
    private static function getColorId(string $colorName): int
    {
        $arFilter = [
            "IBLOCK_ID" => self::IBLOCK_ID_COLORS,
            "ACTIVE" => "Y",
            "%NAME" => $colorName
        ];
        $res = CIBlockElement::GetList([], $arFilter, false, false, ["ID"]);
        if ($arFields = $res->GetNext()) {
            return (int)$arFields['ID'];
        }
        return 0;
    }

    /**
     * Расчет общей площади
     *
     * @param array $sizes Массив размеров
     * @return float Общая площадь
     */
    private static function calculateTotalSquare(array $sizes): float
    {
        $totalSquare = 0;
        foreach ($sizes as $size) {
            $height = (float)str_replace(",", ".", $size['height'] ?? 0);
            $width = (float)str_replace(",", ".", $size['width'] ?? 0);
            $count = (int)($size['count'] ?? 0);
            $totalSquare += $width * $height * $count;
        }
        return $totalSquare;
    }

    /**
     * Расчет цен для каждого размера
     *
     * @param array $sizes Массив размеров
     * @param float $priceWhite Цена белого товара
     * @param float $priceColor Цена цветного товара
     * @param float $paintPrice Разница в цене (покраска)
     * @param float $totalSquare Общая площадь
     * @param array $productProperties Свойства товара
     * @param int $baseProductId ID базового товара
     * @param float $basePrice Базовая цена
     * @return array Результат расчета
     */
    private static function calculatePricesForSizes(
        array $sizes,
        float $priceWhite,
        float $priceColor,
        float $paintPrice,
        float $totalSquare,
        array $productProperties,
        int $baseProductId,
        float $basePrice
    ): array {
        $prices = [];
        $pricesNovat = [];
        $sumSquare = 0;
        $height = 0;
        $width = 0;
        $countSum = 0;
        $sizesLines = [];
        $paintGrid = false;
        $paintSquareThreshold = $productProperties['PROPERTY_S_POKRAS_VALUE'] ?? 0;
        $paintGridPrice = $productProperties['PROPERTY_PRICE_POKRAS_VALUE'] ?? 0;

        foreach ($sizes as $size) {
            $count = (int)($size['count'] ?? 0);
            $countSum += $count;

            $fHeight = (float)str_replace(",", ".", $size['height'] ?? 0);
            $fWidth = (float)str_replace(",", ".", $size['width'] ?? 0);

            $height += $fHeight;
            $width += $fWidth;

            $square = $fWidth * $fHeight;
            $sumSquare += $square * $count;
            // Единый формат вывода: "2,5 × 3,5 m × 1 ks."
            $sizesLines[] = sprintf(
                '%s × %s m × %s ks.',
                Format::number($fHeight),
                Format::number($fWidth),
                Format::number($count)
            );

            // Расчет цены для размера
            $price = self::calculateSizePrice(
                $square,
                $priceWhite,
                $priceColor,
                $paintPrice,
                $totalSquare,
                $paintSquareThreshold,
                $paintGridPrice,
                $paintGrid,
                $baseProductId,
                $basePrice
            );

            $priceNovat = self::calculateSizePriceNovat(
                $square,
                $baseProductId,
                $basePrice
            );

            $prices[] = $price * $count;
            $pricesNovat[] = $priceNovat * $count;
        }

        return [
            'totalPrice' => array_sum($prices),
            'totalPriceNovat' => array_sum($pricesNovat),
            'totalSquare' => $sumSquare,
            'sizesStr' => implode(', ', $sizesLines),
            'height' => $height,
            'width' => $width,
            'countSum' => $countSum
        ];
    }

    /**
     * Расчет цены для одного размера
     *
     * @param float $square Площадь размера
     * @param float $priceWhite Цена белого товара
     * @param float $priceColor Цена цветного товара
     * @param float $paintPrice Разница в цене
     * @param float $totalSquare Общая площадь
     * @param float $paintSquareThreshold Порог площади для покраски
     * @param float $paintGridPrice Цена сетки покраски
     * @param bool &$paintGrid Флаг использования сетки покраски
     * @param int $baseProductId ID товара
     * @param float $basePrice Базовая цена
     * @return float Цена
     */
    private static function calculateSizePrice(
        float $square,
        float $priceWhite,
        float $priceColor,
        float $paintPrice,
        float $totalSquare,
        float $paintSquareThreshold,
        float $paintGridPrice,
        bool &$paintGrid,
        int $baseProductId,
        float $basePrice
    ): float {

        // Если есть цена белого товара и цвет не белый
        if ($priceWhite > 0) {
            $newPriceWhite = self::getAdjustedPrice($square, $priceWhite);

            if ($paintSquareThreshold > 0) {
                if ($totalSquare > $paintSquareThreshold) {
                    $price = ($square < $paintSquareThreshold)
                        ? ($newPriceWhite + $paintPrice) * $square
                        : $priceColor * $square;
                } else {
                    $price = ($square < $paintSquareThreshold)
                        ? $newPriceWhite * $square
                        : $priceColor * $square;
                }

                // Добавление цены сетки покраски
                if ($totalSquare < $paintSquareThreshold && $square < $paintSquareThreshold && !$paintGrid) {
                    $price += $paintGridPrice;
                    $paintGrid = true;
                }
            } else {
                $price = $newPriceWhite * $square;
            }

            return $price;
        }

        // Расчет по базовой цене
        $coefficient = self::getSquareCoefficient($square);
        return $square * $basePrice * $coefficient;
    }

    /**
     * Расчет цены без НДС для размера
     *
     * @param float $square Площадь
     * @param int $productId ID товара
     * @param float $basePrice Базовая цена
     * @return float Цена без НДС
     */
    private static function calculateSizePriceNovat(float $square, int $productId, float $basePrice): float
    {
        $priceResult = CPrice::GetList(
            [],
            [
                "PRODUCT_ID" => $productId,
                "CATALOG_GROUP_ID" => 1
            ]
        );

        $nvp = 0;
        if ($arPrices = $priceResult->Fetch()) {
            $nvp = (float)$arPrices['PRICE'];
        }

        if ($nvp > 0) {
            $coefficient = self::getSquareCoefficient($square);
            return $square * $nvp * $coefficient;
        }

        return 0;
    }

    /**
     * Получение скорректированной цены в зависимости от площади
     *
     * @param float $square Площадь
     * @param float $basePrice Базовая цена
     * @return float Скорректированная цена
     */
    private static function getAdjustedPrice(float $square, float $basePrice): float
    {
        $coefficient = self::getSquareCoefficient($square);
        return $basePrice * $coefficient;
    }

    /**
     * Получение коэффициента в зависимости от площади
     *
     * @param float $square Площадь
     * @return float Коэффициент
     */
    private static function getSquareCoefficient(float $square): float
    {
        if ($square >= 0 && $square < 5) {
            return self::SQUARE_COEFFICIENT_0_5;
        } elseif ($square >= 5 && $square < 10) {
            return self::SQUARE_COEFFICIENT_5_10;
        } elseif ($square >= 10 && $square < 20) {
            return self::SQUARE_COEFFICIENT_10_20;
        } else {
            return self::SQUARE_COEFFICIENT_20_PLUS;
        }
    }
}

