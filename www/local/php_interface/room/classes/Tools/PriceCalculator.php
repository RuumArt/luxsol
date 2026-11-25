<?php

namespace Room\Tools;

use CUser;
use CIBlockElement;
use CIBlockPriceTools;
use CCatalogProduct;

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
        // RESULT_PRICE is in the same VAT basis as the product card/provider.
        // PRICE.PRICE can be net; client-submitted prices are not authoritative.
        $selectedPrice = self::getCatalogPrice((int)$productId);
        $basePrice = $selectedPrice['gross'];
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

        $grossTotal = round($result['totalPrice'], 2);
        return [
            'price' => $grossTotal,
            'priceNovat' => round($grossTotal / (1 + $selectedPrice['vatRate']), 2),
            'totalSquare' => round($result['totalSquare'], 1),
            'sizesStr' => $result['sizesStr'],
            'height' => $result['height'],
            'width' => $result['width'],
            'countSum' => $result['countSum'],
            'sizesArr' => $sizesArr
        ];
    }

    /** Base price including VAT; sale discounts remain the basket provider's responsibility. */
    private static function getCatalogPrice(int $productId): array
    {
        global $USER;
        $groups = is_object($USER) && method_exists($USER, 'GetUserGroupArray')
            ? $USER->GetUserGroupArray() : [2];
        $optimal = CCatalogProduct::GetOptimalPrice($productId, 1, $groups, 'N', [], defined('SITE_ID') ? SITE_ID : 's1');
        $result = $optimal['RESULT_PRICE'] ?? [];
        $gross = (float)($result['BASE_PRICE'] ?? 0);
        if ($gross <= 0) {
            throw new \RuntimeException('Cenu tovaru sa nepodarilo overiť. Obnovte stránku.');
        }
        if (($result['VAT_INCLUDED'] ?? 'Y') !== 'Y') {
            throw new \RuntimeException('Cenu tovaru s DPH sa nepodarilo overiť.');
        }
        return ['gross'=>$gross, 'vatRate'=>!empty($result['NO_VAT']) && $result['NO_VAT'] === 'Y'
            ? 0.0 : max(0.0, (float)($result['VAT_RATE'] ?? 0))];
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

        foreach ($offers as $offer) {
            if ($offer['PROPERTY_COLOR_VALUE'] == self::COLOR_WHITE_ID) {
                $priceWhite = self::getCatalogPrice((int)$offer['ID'])['gross'];
            } elseif ($offer['PROPERTY_COLOR_VALUE'] == $colorId) {
                $priceColor = self::getCatalogPrice((int)$offer['ID'])['gross'];
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

            $prices[] = $price * $count;
        }

        return [
            'totalPrice' => array_sum($prices),
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

