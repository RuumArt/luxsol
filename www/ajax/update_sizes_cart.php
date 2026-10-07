<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Room\Services\CartService;
use Room\Tools\PriceCalculator;

CModule::IncludeModule('catalog');
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'error' => '',
    'count' => 0,
    'data' => []
];

try {
    $prods = isset($_POST['prods']) ? $_POST['prods'] : null;

    if (empty($prods) || !is_array($prods)) {
        throw new Exception('Не переданы товары');
    }

    foreach ($prods as $product) {
        if (!is_array($product)) {
            continue;
        }

        // prodId - товар в корзине, id - позиция корзины либо базовый товар
        $productId = (int)($product['prodId'] ?? $product['productId'] ?? 0);
        $basketItemId = (int)($product['basketItemId'] ?? 0);

        if ($productId <= 0) {
            continue;
        }

        $sizes = (!empty($product['sizes']) && is_array($product['sizes'])) ? $product['sizes'] : [];

        if (empty($sizes)) {
            continue;
        }

        // Получение информации о товаре
        $baseProductId = CartService::getBaseProductId($productId);
        $productInfo = CartService::getProductInfo($baseProductId);

        if (empty($productInfo)) {
            throw new Exception('Товар не найден: ' . $productId);
        }

        $articul = !empty($product['articul']) ? $product['articul'] : $productInfo['article'];
        $color = (!empty($product['color']) && $product['color'] !== 'undefined')
            ? $product['color']
            : PriceCalculator::COLOR_WHITE_NAME;
        $basePrice = isset($product['price']) ? (float)str_replace(",", ".", $product['price']) : 0;

        // Пересчет цены по новым размерам
        $priceData = PriceCalculator::calculatePriceWithSizes([
            'sizes' => $sizes,
            'productId' => $productId,
            'baseProductId' => $baseProductId,
            'color' => $color,
            'basePrice' => $basePrice,
            'productProperties' => [
                'PROPERTY_S_POKRAS_VALUE' => $productInfo['paintSquareThreshold'],
                'PROPERTY_PRICE_POKRAS_VALUE' => $productInfo['paintGridPrice']
            ]
        ]);

        // Формирование свойств товара
        $properties = [
            ["NAME" => "Číslo výrobku", "VALUE" => $articul, "CODE" => "ARTICUL"],
            ["NAME" => "Farba", "VALUE" => $color, "CODE" => "COLOR"],
            ["NAME" => "Výška", "VALUE" => $priceData['height'], "CODE" => "HEIGHT"],
            ["NAME" => "Šírka", "VALUE" => $priceData['width'], "CODE" => "WIDTH"],
            ["NAME" => "Plocha", "VALUE" => $priceData['totalSquare'], "CODE" => "SQUARE"],
            ["NAME" => "Dimenze (VxŠxP)", "VALUE" => $priceData['sizesStr'], "CODE" => "SIZES_STR"],
            ["NAME" => "Цена", "VALUE" => $basePrice, "CODE" => "PRICE"],
            ["NAME" => "Цена без ндс", "VALUE" => $priceData['priceNovat'], "CODE" => "PRICE_NOVAT"]
        ];

        // Обновление позиции корзины
        $updated = CartService::updateProductSizes(
            $productId,
            $priceData,
            $properties,
            $basketItemId ?: null
        );

        if (!$updated) {
            throw new Exception('Товар не найден в корзине: ' . $productId);
        }

        $response['data'][] = [
            'id' => $baseProductId,
            'prodId' => $productId,
            'price' => $priceData['price'],
            'name' => $productInfo['name'],
            'quantity' => 1
        ];
    }

    // Обновление корзины
    $basketData = CartService::refreshBasket();
    $response['count'] = $basketData['count'];
    $response['success'] = true;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
